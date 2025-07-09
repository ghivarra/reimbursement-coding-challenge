<template>
    <Badge class="mr-2 mb-2" :class="setColor(props.status)">
        {{ props.status }}
    </Badge>
</template>

<script setup lang="ts">

import Badge from '@/components/ui/badge/Badge.vue'
import { defineProps } from 'vue'

const props = defineProps<{
    status: string
}>()

const colors = [
    {status: 'Diajukan', colorClass: 'bg-blue-400'},
    {status: 'Ditolak', colorClass: 'bg-red-400'},
    {status: 'Dikembalikan', colorClass: 'bg-amber-400 text-dark'},
    {status: 'Disetujui', colorClass: 'bg-green-600'},
    {status: 'Revisi', colorClass: 'bg-blue-400'},
]

const setColor = (status: string): string => {
    const find = colors.filter(function(color) {
        if (color.status === status) return color
    })

    return find[0].colorClass
}

// get data


</script>