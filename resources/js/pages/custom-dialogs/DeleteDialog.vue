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
                <AlertDialogTitle>{{ props.title }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ props.description }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>
                    Batal
                </AlertDialogCancel>
                <AlertDialogAction>
                    <Button @click.prevent="saveForm" type="button" variant="destructive">
                        {{ props.buttonText }}
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
    title: string,
    description: string,
    uri: string,
    buttonText: string
}>()

// methods
const saveForm = () => {

    // save data
    axios.delete(props.uri, {
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