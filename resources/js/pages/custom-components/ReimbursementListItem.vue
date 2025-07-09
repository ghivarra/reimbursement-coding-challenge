<template>
    <Card class="bg-linear-to-t from-gray-50 to-white">
        <CardHeader>
            <div class="mb-2">
                <StatusBadge :status="props.status" />
            </div>
            <CardTitle>{{ props.name }}</CardTitle>
            <CardDescription>
                {{ props.number }}
            </CardDescription>
            <CardAction>
                <div class="flex items-center text-sm mb-4">
                    <Icon name="CalendarRange" class="inline-block mr-1" />
                    {{ props.date }}
                </div>
            </CardAction>
        </CardHeader>
        <CardContent>
            <div class="p-4 bg-neutral-100 rounded-lg">
                <p class="text-lg text-green-600 font-bold">
                    {{ props.amount }}
                </p>
            </div>
        </CardContent>
        <CardFooter>
            <div class="flex">
                <Button v-if="props.status === 'Dikembalikan'" type="button" variant="default" size="sm" class="mr-2">
                    <Icon name="SquarePen" />
                    Revisi
                </Button>
                <Button v-if="props.allowDelete" type="button" variant="destructive" size="sm">
                    <Icon name="Trash2" />
                    Hapus
                </Button>
            </div>
        </CardFooter>
    </Card>
</template>

<script setup lang="ts">

import { Card, CardHeader, CardTitle, CardDescription, CardAction, CardContent, CardFooter } from '@/components/ui/card'
import StatusBadge from './StatusBadge.vue'
import Button from '@/components/ui/button/Button.vue'
import Icon from '@/components/Icon.vue'
import { defineProps } from 'vue'

const props = defineProps<{
    status: string,
    name: string,
    number: string,
    date: string,
    amount: number,
    allowDelete: {
        type: boolean,
        default: false
    },
}>()

</script>