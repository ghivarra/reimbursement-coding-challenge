<template>
    <Head title="Dasbor" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="bg-green-50 text-green-700 p-4 rounded-md">
                Selamat datang <b>{{ props.userData.name }}</b>, anda login sebagai <b>{{ props.userData.role_name }}</b>.
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">

import AppLayout from '@/layouts/AppLayout.vue';
import { AccessProp, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { defineProps, provide } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor',
        href: route('dashboard'),
    },
];

type UserData = {
    id: number,
    name: string,
    email: string,
    role_id: number,
    role_name: string,
}

const props = defineProps<{
    userData: UserData,
    access: AccessProp
}>()

// provide access
provide('access', props.access)

</script>
