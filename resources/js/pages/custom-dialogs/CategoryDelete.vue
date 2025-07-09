<template>
    <AlertDialog>
        <AlertDialogTrigger>
            <Button type="button" variant="destructive" size="sm">
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

import { AlertDialog, AlertDialogTrigger, AlertDialogContent, AlertDialogHeader, AlertDialogDescription, AlertDialogCancel, AlertDialogTitle, AlertDialogFooter } from '@/components/ui/alert-dialog'
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