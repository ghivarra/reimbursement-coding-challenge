<template>
    <AlertDialog>
        <AlertDialogTrigger>
            <Button type="button" variant="destructive">
                <Icon name="Trash2" />
                Hapus
            </Button>
        </AlertDialogTrigger>
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Hapus Kategori {{ name }}?</AlertDialogTitle>
                <AlertDialogDescription>
                    Aksi ini tidak bisa diputar balik dan kategori akan terhapus secara permanen.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>
                    Batal
                </AlertDialogCancel>
                <AlertDialogAction>
                    <Button @click.prevent="saveForm" type="button" variant="destructive">
                        Ya, Hapus
                    </Button>
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>

<script setup lang="ts">

import AlertDialog from '@/components/ui/alert-dialog/AlertDialog.vue'
import AlertDialogTrigger from '@/components/ui/alert-dialog/AlertDialogTrigger.vue'
import AlertDialogContent from '@/components/ui/alert-dialog/AlertDialogContent.vue'
import AlertDialogHeader from '@/components/ui/alert-dialog/AlertDialogHeader.vue'
import AlertDialogDescription from '@/components/ui/alert-dialog/AlertDialogDescription.vue'
import AlertDialogFooter from '@/components/ui/alert-dialog/AlertDialogFooter.vue'
import AlertDialogCancel from '@/components/ui/alert-dialog/AlertDialogCancel.vue'
import AlertDialogTitle from '@/components/ui/alert-dialog/AlertDialogTitle.vue'
import Icon from '@/components/Icon.vue'
import Button from '@/components/ui/button/Button.vue'

import { AlertDialogAction } from 'reka-ui'
import { inject } from 'vue'
import axios, { AxiosResponse } from 'axios'

// inject
const csrfHash: string|undefined = inject('csrfHash')
const emit = defineEmits(['delete'])

// get props
const props = defineProps<{
    id: number,
    name: string
}>()

// methods
const saveForm = () => {

    // save data
    const uri = route('reimbursement.category.delete') + `?id=${props.id}`
    axios.delete(uri, {
        headers: {
            'X-CSRF-TOKEN': csrfHash
        }
    })
        .then((response: AxiosResponse) => {
            const res = response.data
            if (res.status === 'success') {
                emit('delete')
            }
        })
        .catch((err) => {
            console.error(err)
            alert(err)
        })
}

</script>