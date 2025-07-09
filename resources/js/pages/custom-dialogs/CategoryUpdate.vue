<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button type="button" size="sm" class="mr-2">
                <Icon name="SquarePen" />
                Update
            </Button>
        </DialogTrigger>
        <DialogContent>
            
            <DialogHeader>
                <DialogTitle>
                    Update Kategori
                </DialogTitle>
                <DialogDescription>
                    Form dengan kode asterisk (*) warna merah wajib diisi.
                </DialogDescription>
            </DialogHeader>

            
            <div class="input-group mb-2">
                <label for="categoryName" class="font-bold mb-2 block">
                    Nama Kategori
                    <span class="text-red-400">*</span>
                </label>
                <Input v-model="form.name" id="categoryName" name="categoryName" placeholder="Transportasi" maxlength="100" required />
                <div class="text-gray-400 pt-2 text-sm">Maks. 100 karakter</div>
            </div>

            <div class="input-group mb-2">
                <label for="categoryName" class="font-bold mb-2 block">
                    Kode
                    <span class="text-red-400">*</span>
                </label>
                <Input v-model="form.code" id="categoryCode" name="categoryCode" placeholder="TRANS" minlength="4" maxlength="4" required />
                <div class="text-gray-400 pt-2 text-sm">Harus 4 karakter</div>
            </div>

            <div class="input-group mb-4">
                <label for="categoryName" class="font-bold mb-2 block">
                    Batas Reimbursement Per Bulan
                    <span class="text-red-400">*</span>
                </label>
                <Input v-model="form.limit_per_month" id="categoryLimit" name="categoryLimit" placeholder="50.000" required />
                <div class="text-gray-400 pt-2 text-sm">Boleh menggunakan titik, koma, spasi dan sebagainya untuk memudahkan input. Contoh: 125.000</div>
            </div>

            <DialogFooter>
                <DialogClose>
                    <Button ref="dialogCloseButton" type="button" variant="link">
                        Batal
                    </Button>
                </DialogClose>
                <DialogClose>
                    <Button @click.prevent="saveForm" type="submit">
                        Simpan Data
                    </Button>
                </DialogClose>
            </DialogFooter>

        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">

import Button from '@/components/ui/button/Button.vue'
import Icon from '@/components/Icon.vue'
import { Dialog, DialogTrigger, DialogContent, DialogTitle, DialogHeader, DialogDescription, DialogFooter, DialogClose } from '@/components/ui/dialog'
import Input from '@/components/ui/input/Input.vue'
import { inject, Ref, ref } from 'vue'
import axios, { AxiosResponse } from 'axios'
import swal from 'sweetalert'

// inject
const csrfHash: string|undefined = inject('csrfHash')
const emit = defineEmits(['update'])

// props
const props = defineProps<{
    id: number
}>()

// data
type Form = {
    name: string
    code: string
    limit_per_month: string
}

const form: Ref<Form> = ref({
    name: '',
    code: '',
    limit_per_month: ''
})

// refs
const dialogCloseButton: Ref<HTMLButtonElement|null> = ref(null)

// methods
const updateForm = () => {
    const uri = route('reimbursement.category.find') + `?id=${props.id}`
    axios.get(uri)
        .then((response: AxiosResponse) => {
            const res = response.data
            if (res.status === 'success') {
                form.value.name = res.data.name
                form.value.code = res.data.code
                form.value.limit_per_month = res.data.limit_per_month
            }
        })
        .catch((err) => {
            console.error(err)
            alert(err)
        })
}

const saveForm = () => {

    // build form data
    const input: Form = JSON.parse(JSON.stringify(form.value))
    const limit = String(input.limit_per_month)
    const formData = {
        id: props.id,
        name: input.name,
        code: input.code,
        limit_per_month: limit.replace(/\D/g, '')
    }

    // save data
    axios.patch(route('reimbursement.category.update'), formData, {
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

// update form
updateForm();

</script>