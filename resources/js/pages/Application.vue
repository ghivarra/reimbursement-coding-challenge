<template>
    <Head title="Application"></Head>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <Heading title="Pengajuan" description="Pantau perkembangan reimbursement yang sudah diajukan." class="mb-0" />
            <div v-if="hasCreateAccess" class="mb-4">
                <Button 
                    @click.prevent="router.visit(route('view.application.create'))" 
                    type="button" 
                    class="cursor-pointer"
                >
                    <Icon name="SquarePen" />
                    Buat Pengajuan
                </Button>
            </div>
            <div class="relative max-w-[260px] mb-2">
                <Input placeholder="Cari Pengajuan..." />
                <Icon class="absolute right-[0.75rem] top-[0.5rem]" name="Search" />
            </div>
            
            <div v-for="(item, key) in reimbursementList" :key="key" class="mb-2">
                <ReimbursementsListItem :item="item" :allow-delete="hasDeleteAccess" :update-list="getReimbursementList" />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">

import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { AccessProp, BreadcrumbItem, ReimbursementListItem } from '@/types'
import { provide, Ref, ref } from 'vue'
import Icon from '@/components/Icon.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import Input from '@/components/ui/input/Input.vue'
import { hasAccess } from '@/library/common'
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
    endpoint: string,
    csrfHash: string,
    access: AccessProp
}>()

// provide access
provide('access', props.access)
provide('csrfHash', props.csrfHash)

// const if has access to create
const hasCreateAccess = hasAccess('view.application.create', props.access.modules)
const hasDeleteAccess = hasAccess('reimbursement.main.delete', props.access.modules)
const reimbursementList: Ref<ReimbursementListItem[]> = ref([])

// methods
const getReimbursementList = () => {

    const formData = new FormData()
    formData.append('limit', '10')
    formData.append('offset', '0')
    formData.append('order[column]', 'updated_at')
    formData.append('order[dir]', 'desc')
    formData.append('_token', props.csrfHash)

    axios.post(props.endpoint, formData)
        .then((response: AxiosResponse) => {
            const res = response.data
            if (res.status === 'success') {
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

getReimbursementList()

</script>