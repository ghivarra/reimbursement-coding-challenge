<template>
    <Head title="Application"></Head>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <Heading title="Buat Pengajuan Reimbursement" description="Isi form sesuai dengan ketentuannya." class="mb-0" />
            <div class="relative mb-2">
                <Input placeholder="Cari Pengajuan..." />
            </div>
            <!--<ListItem />-->
        </div>
    </AppLayout>
</template>

<script setup lang="ts">

import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import Button from '@/components/ui/button/Button.vue'
import Icon from '@/components/Icon.vue'
import Input from '@/components/ui/input/Input.vue'
import { AccessProp, BreadcrumbItem } from '@/types'
import { provide } from 'vue'
import { hasAccess } from '@/library/common'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor',
        href: route('dashboard'),
    },
    {
        title: 'Pengajuan',
        href: route('view.application'),
    },
    {
        title: 'Buat Baru',
        href: route('view.application.create'),
    },
]

const props = defineProps<{
    access: AccessProp
}>()

// provide access
provide('access', props.access)

</script>