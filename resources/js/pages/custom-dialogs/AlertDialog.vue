<template>
    <AlertDialog>
        <AlertDialogTrigger>
            <Button type="button" :variant="props.triggerButtonVariant" :size="props.triggerButtonSize">
                <Icon :name="triggerButtonIcon" />
                {{ props.triggerButtonText }}
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
                    <Button @click.prevent="saveForm" type="button" :variant="props.triggerButtonVariant">
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
import swal from 'sweetalert'

// inject
const csrfHash: string|undefined = inject('csrfHash')
const emit = defineEmits(['update'])

// get props
const props = defineProps<{
    title: string,
    description: string,
    uri: string,
    buttonText: string,
    triggerButtonText: string,
    triggerButtonVariant: "link" | "default" | "destructive" | "outline" | "secondary" | "ghost",
    triggerButtonIcon: string,
    triggerButtonSize: "sm" | "default" | "icon" | "lg",
    method?: "get" | "delete"
}>()

// methods
const saveForm = () => {

    if (typeof props.method !== 'undefined') {

        if (props.method === 'get') {

            axios.get(props.uri)
                .then((response: AxiosResponse) => {
                    const res = response.data
                    if (res.status === 'success') {
                        emit('update')
                    }
                })
                .catch((err) => {
                    console.error(err)
                    if (typeof err.response.data !== 'undefined') {
                        swal({
                            title: 'Whoopsss',
                            text: err.response.data.message,
                            icon: "error",
                        });
                    }
                })

            return
        }
    }

    // save data
    axios.delete(props.uri, {
        headers: {
            'X-CSRF-TOKEN': csrfHash
        }
    })
        .then((response: AxiosResponse) => {
            const res = response.data
            if (res.status === 'success') {
                emit('update')
            }
        })
        .catch((err) => {
            console.error(err)
            if (typeof err.response.data !== 'undefined') {
                swal({
                    title: 'Whoopsss',
                    text: err.response.data.message,
                    icon: "error",
                });
            }
        })

}

</script>