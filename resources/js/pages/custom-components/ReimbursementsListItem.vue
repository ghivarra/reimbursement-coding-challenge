<template>
    <Card class="bg-linear-to-t from-gray-50 to-white">
        <CardHeader>
            <div class="mb-2">
                <StatusBadge :status="props.item.status_name" />
            </div>
            <CardTitle>{{ props.item.name }}</CardTitle>
            <CardDescription>
                {{ props.item.number }}
            </CardDescription>
            <CardAction>
                <div class="flex items-center text-sm mb-2">
                    <Icon name="CalendarRange" class="mr-1" />
                    {{ props.item.date }}
                </div>
                <div class="flex items-center text-sm mb-4">
                    <Icon name="CircleUserRound" class="mr-1" />
                    {{ props.item.owner_name }}
                </div>
            </CardAction>
        </CardHeader>
        <CardContent>
            <div class="p-4 bg-neutral-100 rounded-lg">
                <p class="text-lg text-green-600 font-bold">
                    {{ value }}
                </p>
            </div>
        </CardContent>
        <CardFooter>
            <div class="flex">
                <Button v-if="props.item.status_name === 'Dikembalikan'" type="button" variant="default" size="sm" class="mr-2">
                    <Icon name="SquarePen" />
                    Revisi
                </Button>
                <DeleteDialog
                    v-if="props.allowDelete"
                    :title="`Hapus Pengajuan ${props.item.name}?`"
                    :uri="deleteUri"
                    v-on:delete="props.updateList()"
                    description="Pengajuan reimbursement yang dihapus hanya bisa dikembalikan oleh Admin"
                    button-text="Ya, hapus"
                />
            </div>
        </CardFooter>
    </Card>
</template>

<script setup lang="ts">

import { Card, CardHeader, CardTitle, CardDescription, CardAction, CardContent, CardFooter } from '@/components/ui/card'
import StatusBadge from './StatusBadge.vue'
import Button from '@/components/ui/button/Button.vue'
import Icon from '@/components/Icon.vue'
import { computed, defineProps } from 'vue'
import { ReimbursementListItem } from '@/types'
import { formatCurrency } from '@/library/common'
import DeleteDialog from '../custom-dialogs/DeleteDialog.vue'

const props = defineProps<{
    item: ReimbursementListItem,
    allowDelete: boolean,
    updateList: () => void
}>()

// compute
const value = computed(() => formatCurrency(props.item.amount))
const deleteUri = route('reimbursement.main.delete') + `?id=${props.item.id}`

</script>