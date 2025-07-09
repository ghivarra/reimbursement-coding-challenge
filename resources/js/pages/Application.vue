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
            <div class="relative mb-2">
                <Input placeholder="Cari Pengajuan..." />
                <Icon class="absolute right-[0.75rem] top-[0.5rem]" name="Search" />
            </div>
            <!--<ListItem />-->
        </div>
    </AppLayout>
</template>

<script setup lang="ts">

import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { AccessProp, BreadcrumbItem } from '@/types'
import { provide } from 'vue'
import Icon from '@/components/Icon.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import Input from '@/components/ui/input/Input.vue'
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
]

const props = defineProps<{
    access: AccessProp
}>()

// provide access
provide('access', props.access)

// const if has access to create
const hasCreateAccess = hasAccess('view.application.create', props.access.modules)

</script>