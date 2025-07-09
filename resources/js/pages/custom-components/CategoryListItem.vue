<template>

    <Card class="bg-linear-to-t from-gray-50 to-white">
        <CardHeader>
            <CardTitle>{{ props.name }}</CardTitle>
            <CardDescription>
                {{ props.code }}
            </CardDescription>
            <CardAction>
                Limit per bulan:
                <p class="text-cyan-600 font-bold">
                    {{ limitStr }}
                </p>
            </CardAction>
        </CardHeader>
        <CardFooter>
            <div class="flex">
                <CategoryUpdate :id="props.id" v-on:update="props.updateList()" />
                <AlertDialog 
                    :title="`Apakah anda yakin akan ketagori menghapus ${props.name}?`" 
                    :uri="deleteUri"
                    description="Aksi ini tidak bisa diputar balik dan kategori akan terhapus secara permanen." 
                    button-text="Ya, Hapus"
                    v-on:update="props.updateList()"
                    trigger-button-icon="Trash2"
                    trigger-button-text="Hapus"
                    trigger-button-variant="destructive"
                    trigger-button-size="sm"
                />
                <!--<CategoryDelete :id="props.id" :name="props.name" v-on:delete="props.updateList()" />-->
            </div>
        </CardFooter>
    </Card>

</template>

<script setup lang="ts">

import Card from '@/components/ui/card/Card.vue'
import CardHeader from '@/components/ui/card/CardHeader.vue'
import CardFooter from '@/components/ui/card/CardFooter.vue'
import CardTitle from '@/components/ui/card/CardTitle.vue'
import CardDescription from '@/components/ui/card/CardDescription.vue'
import CardAction from '@/components/ui/card/CardAction.vue'
import { computed } from 'vue'
import { formatCurrency } from '@/library/common'
// import CategoryDelete from '../custom-dialogs/CategoryDelete.vue'
import CategoryUpdate from '../custom-dialogs/CategoryUpdate.vue'
import AlertDialog from '../custom-dialogs/AlertDialog.vue'

const props = defineProps<{
    id: number,
    name: string,
    code: string,
    limit: number,
    updateList: () => void
}>()

const limitStr = computed(() => formatCurrency(props.limit))
const deleteUri = route('reimbursement.category.delete') + `?id=${props.id}`

</script>