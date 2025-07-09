<template>
    <Head title="Dasbor" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto"></div>
    </AppLayout>
</template>

<script setup lang="ts">

import AppLayout from '@/layouts/AppLayout.vue';
import { AccessProp, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { defineProps, provide } from 'vue';
// import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor',
        href: route('dashboard'),
    },
];

const props = defineProps<{
    access: AccessProp
}>()

// provide access
provide('access', props.access)

</script>
