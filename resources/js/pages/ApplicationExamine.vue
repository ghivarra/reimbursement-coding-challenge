<template>
    <Head :title="title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">

            <div>
                <Badge class="bg-cyan-700 mb-3">{{ reimbursement?.category_name }}</Badge>
                <h1 class="text-2xl font-bold">{{ title }}</h1>
                <p class="text-gray-400 text-md">{{ subtitle }}</p>
            </div>

            <section>
                <div class="flex items-center">
                    <div class="flex items-center mr-6">
                        <Icon name="CircleUserRound" class="mr-2 text-gray-400" />
                        {{ reimbursement?.owner_name }}
                    </div>
                    <div class="flex items-center">
                        <Icon name="Calendar" class="mr-2 text-gray-400" />
                        {{ reimbursement?.date }}
                    </div>
                </div>
            </section>

            <section class="p-4 bg-gray-100 rounded-lg">
                <p class="text-lg font-bold text-green-600">{{ amount }}</p>
            </section>

            <section>
                <div>
                    {{ reimbursement?.description }}
                </div>
            </section>

            <section v-if="extension === 'pdf'" class="mb-4">
                <iframe :src="reimbursement?.file" width="100%" height="600px" frameborder="0"></iframe>
            </section>

            <section v-if="extension === 'jpg'" class="mb-4">
                <img :src="reimbursement?.file" class="max-w-full h-auto max-h-[300px]" :alt="title">
            </section>

            <section>
                <p class="text-xl font-bold">Catatan:</p>
            </section>

            <section>
                <div v-for="(log, key) in logCollections" :key="key" class="w-full flex border-2 mb-4">
                    <div class="flex items-center w-1/2 p-4">
                        <StatusBadge :status="log.status_name" />
                        <div class="pl-4">{{ formatDateTime(log.time) }}</div>
                    </div>
                    <div class="w-1/2 p-4">
                        <div class="mb-2">{{ log.content }}</div>
                        <div class="text-gray-400">{{ log.note }}</div>
                    </div>
                </div>
            </section>

            <Dialog v-if="hasRespondAccess && reimbursement?.status_id === 1 || reimbursement?.status_id === 3">
                <DialogTrigger as-child>
                    <Button type="button" size="sm" class="mr-2">
                        <Icon name="Signature" />
                        Beri Keputusan
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    
                    <DialogHeader>
                        <DialogTitle>
                            Beri Keputusan
                        </DialogTitle>
                        <DialogDescription>
                            Pilihan keputusan antara lain Disetujui, Ditolak, atau Dikembalikan untuk direvisi.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="input-group mb-2">
                        <label class="font-bold mb-2 block">
                            Keputusan
                        </label>
                        <Select v-model="form.respond" id="respond" name="respond" class="w-100" required>
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih Keputusan"></SelectValue>
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="4">Disetujui</SelectItem>
                                <SelectItem value="5">Ditolak</SelectItem>
                                <SelectItem value="2">Dikembalikan</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="input-group mb-4">
                        <label for="note" class="font-bold mb-2 block">
                            Catatan
                        </label>
                        <Textarea v-model="form.note" id="note" name="note" placeholder="Catatan (opsional)" rows="4"></Textarea>
                        <div class="text-gray-400 pt-2 text-sm">Opsional</div>
                    </div>

                    <DialogFooter>
                        <DialogClose>
                            <Button ref="dialogCloseButton" type="button" variant="link">
                                Batal
                            </Button>
                        </DialogClose>
                        <DialogClose>
                            <Button @click.prevent="saveRespond" type="submit">
                                Simpan Data
                            </Button>
                        </DialogClose>
                    </DialogFooter>

                </DialogContent>
            </Dialog>

        </div>
    </AppLayout>
</template>

<script setup lang="ts">

import { Dialog, DialogTrigger, DialogContent, DialogTitle, DialogHeader, DialogDescription, DialogFooter, DialogClose } from '@/components/ui/dialog'
import { Select, SelectContent, SelectItem, SelectValue, SelectTrigger } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import Button from '@/components/ui/button/Button.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { AccessProp, Reimbursement, type BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { computed, defineProps, provide, Ref, ref } from 'vue'
import axios, { AxiosResponse } from 'axios'
import Icon from '@/components/Icon.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import { formatCurrency, formatDateTime, hasAccess } from '@/library/common'
import StatusBadge from './custom-components/StatusBadge.vue'
import swal from 'sweetalert'

const props = defineProps<{
    id: string,
    endpoint: string,
    logEndpoint: string,
    csrfHash: string,
    access: AccessProp
}>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor',
        href: route('dashboard'),
    },
    {
        title: 'Lihat Pengajuan',
        href: route('view.application.examine', props.id),
    },
];

// provide access
provide('access', props.access)

type Log = {
    time: string,
    note: string | null,
    status_name: string,
    content: string,
}

// data
const title = ref('')
const subtitle = ref('')
const reimbursement: Ref<Reimbursement | null> = ref(null)
const logCollections: Ref<Log[]> = ref([])
const hasRespondAccess = hasAccess('reimbursement.main.respond', props.access.modules)

const form = ref({
    respond: '4',
    note: ''
})

// computed
const extension = computed(() => {
    if (reimbursement.value === null) {
        return ''
    }

    const ext = reimbursement.value.file.slice(-3)

    return (ext === 'pdf') ? 'pdf' : 'jpg'
})

const amount = computed(() => {
    if (reimbursement.value !== null) {
        return formatCurrency(reimbursement.value?.amount)
    }
    return ''
})

// methods
const findReimbursement = () => {
    const uri = props.endpoint + `?id=${props.id}`
    axios.get(uri)
        .then((response: AxiosResponse) => {
            const res = response.data
            if (res.status === 'success') {
                reimbursement.value = res.data
                title.value = res.data.name
                subtitle.value = res.data.number
            }
        })
        .catch((err) => {
            console.error(err)
            if (typeof err.response.data !== 'undefined') {
                swal({
                    title: 'Whoopsss',
                    text: err.response.data.message,
                    icon: "error",
                });
            }
        })
}

const findReimbursementLog = () => {
    const uri = props.logEndpoint + `?id=${props.id}`
    axios.get(uri)
        .then((response: AxiosResponse) => {
            const res = response.data
            if (res.status === 'success') {
                logCollections.value = res.data
            }
        })
        .catch((err) => {
            console.error(err)
            if (typeof err.response.data !== 'undefined') {
                swal({
                    title: 'Whoopsss',
                    text: err.response.data.message,
                    icon: "error",
                });
            }
        })
}

const saveRespond = () => {

    const formData = new FormData()
    formData.append("_token", props.csrfHash)
    formData.append("id", props.id)
    formData.append("status_id", form.value.respond)
    formData.append("note", form.value.note)

    // send
    axios.post(route('reimbursement.main.respond'), formData)
        .then((response: AxiosResponse) => {
            const res = response.data
            if (res.status === 'success') {
                router.visit(route('view.approval'))
            }
        })
        .catch((err) => {
            console.error(err)
            if (typeof err.response.data !== 'undefined') {
                swal({
                    title: 'Whoopsss',
                    text: err.response.data.message,
                    icon: "error",
                });
            }
        })
}

// find
findReimbursement()
findReimbursementLog()

</script>
