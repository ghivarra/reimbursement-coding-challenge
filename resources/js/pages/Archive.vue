<template>
    <Head title="Arsip"></Head>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <Heading title="Arsip" description="Halaman pengajuan reimbursement yang dihapus oleh pembuat." class="mb-0" />
            <div class="relative max-w-[260px] mb-2">
                <Input v-model="query" placeholder="Cari Pengajuan..." />
                <Icon class="absolute right-[0.75rem] top-[0.5rem]" name="Search" />
            </div>
            
            <div v-for="(item, key) in filteredReimbursementList" :key="key" class="mb-2">
                <ReimbursementsListItem :item="item" :allow-delete="false" :allow-restore="true" :update-list="getReimbursementList" />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">

import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { AccessProp, BreadcrumbItem, Reimbursement } from '@/types'
import { provide, Ref, ref, watch } from 'vue'
import Icon from '@/components/Icon.vue'
import Heading from '@/components/Heading.vue'
import Input from '@/components/ui/input/Input.vue'
import axios, { AxiosResponse } from 'axios'
import ReimbursementsListItem from './custom-components/ReimbursementsListItem.vue'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor',
        href: route('dashboard'),
    },
    {
        title: 'Pengajuan',
        href: route('view.application'),
    },
]

const props = defineProps<{
    csrfHash: string,
    access: AccessProp
}>()

// provide access
provide('access', props.access)
provide('csrfHash', props.csrfHash)

// const if has access to create
const reimbursementList: Ref<Reimbursement[]> = ref([])
const filteredReimbursementList: Ref<Reimbursement[]> = ref([])
const query = ref('')

// methods
const getReimbursementList = () => {

    const formData = new FormData()
    formData.append('limit', '10')
    formData.append('offset', '0')
    formData.append('order[column]', 'updated_at')
    formData.append('order[dir]', 'desc')
    formData.append('_token', props.csrfHash)

    axios.post(route('reimbursement.main.index.archive'), formData)
        .then((response: AxiosResponse) => {
            const res = response.data
            if (res.status === 'success') {
                filteredReimbursementList.value = res.data
                reimbursementList.value = res.data
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

const searchList = (query: string): void => {

    // reset if empty
    if (query.length < 1) {
        filteredReimbursementList.value = JSON.parse(JSON.stringify(reimbursementList.value))
    }

    filteredReimbursementList.value = reimbursementList.value.filter((item) => {
        if (item.name.includes(query) || item.status_name == query || item.number == query) {
            return item
        }
    })
}

watch(query, (newValue) => {
    searchList(newValue)
})

getReimbursementList()

</script>