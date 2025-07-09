<template>
    <Head title="Buat Reimbursement"></Head>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <Heading title="Buat Pengajuan Reimbursement" description="Form dengan simbol asterisk (*) berwarna merah artinya wajib diisi." class="mb-0" />
            
            <div class="input-group mb-2">
                <label for="mainName" class="font-bold mb-2 block">
                    Nama Reimbursement
                    <span class="text-red-400">*</span>
                </label>
                <Input v-model="form.name" id="mainName" name="mainName" placeholder="Biaya Transportasi ke Bali" maxlength="200" required />
                <div class="text-gray-400 pt-2 text-sm">Maks. 200 karakter</div>
            </div>

            <div class="input-group mb-2">
                <label class="font-bold mb-2 block">
                    Tanggal
                    <span class="text-red-400">*</span>
                </label>
                <Popover>
                    <PopoverTrigger as-child>
                        <Button
                            variant="outline"
                            :class="cn(
                                'w-[280px] justify-start text-left font-normal',
                                !form.date && 'text-muted-foreground',
                            )"
                        >
                            <CalendarIcon class="mr-2 h-4 w-4" />
                            {{ form.date ? df.format(form.date.toDate(getLocalTimeZone())) : "Pilih Tanggal" }}
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-auto p-0">
                        <Calendar id="mainDate" v-model="form.date" initial-focus />
                    </PopoverContent>
                </Popover>
            </div>

            <div class="input-group mb-2">
                <label class="font-bold mb-2 block">
                    Kategori
                    <span class="text-red-400">*</span>
                </label>
                <Select v-model="form.category_id" id="mainCategory" name="mainCategory" class="w-100">
                    <SelectTrigger>
                        <SelectValue placeholder="Pilih Kategori Reimbursement"></SelectValue>
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="(category, i) in categories" :key="i" :value="category.id">
                            {{ category.name }} - {{ category.code }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="input-group mb-4">
                <label for="mainAmount" class="font-bold mb-2 block">
                    Nilai Reimbursement (dalam Rupiah)
                    <span class="text-red-400">*</span>
                </label>
                <Input v-model="form.amount" id="mainAmount" name="mainAmount" placeholder="50.000" required />
                <div class="text-gray-400 pt-2 text-sm">Boleh menggunakan titik, koma, spasi dan sebagainya untuk memudahkan input. Contoh: 125.000</div>
            </div>

            <div class="input-group mb-4">
                <label for="mainFile" class="font-bold mb-2 block">
                    Berkas (PDF/JPG)
                    <span class="text-red-400">*</span>
                </label>
                <form ref="multipartForm">
                    <Input @change="checkFileUpload" id="mainFile" name="file" type="file" accept="image/jpeg,application/pdf" required />
                </form>
                <div class="text-gray-400 pt-2 text-sm">Hanya boleh menggunakan ekstensi jpg/jpeg atau pdf. Maks. 2 MB</div>
            </div>

            <div v-show="showImagePreview" class="mb-4">
                <img ref="imagePreviewHTML" src="/" class="max-w-[320px] max-h-[320px]" alt="preview">
            </div>

            <div v-show="showPDFPreview" class="mb-4">
                <iframe ref="pdfPreviewHTML" src="#" height="400px" width="100%" class="border-0" title="PDF Viewer" frameborder="0"></iframe>
            </div>

            <div class="input-group mb-4">
                <label for="mainDescription" class="font-bold mb-2 block">
                    Deskripsi
                </label>
                <Textarea v-model="form.description" id="mainDescription" name="mainDescription" placeholder="Deskripsi/penjelasan detail reimbursement" rows="4"></Textarea>
                <div class="text-gray-400 pt-2 text-sm">Opsional</div>
            </div>

            <Button @click.prevent="saveForm" type="submit" class="max-w-[140px]">
                <Icon name="Save" />
                Simpan Data
            </Button>
        
        </div>
    </AppLayout>
</template>

<script setup lang="ts">

import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import Button from '@/components/ui/button/Button.vue'
import Icon from '@/components/Icon.vue'
import Input from '@/components/ui/input/Input.vue'
import { AccessProp, BreadcrumbItem } from '@/types'
import { provide, ref, Ref } from 'vue'
import { Select, SelectContent, SelectItem, SelectValue, SelectTrigger } from '@/components/ui/select'
import axios, { AxiosResponse } from 'axios'
import { Popover, PopoverTrigger, PopoverContent } from '@/components/ui/popover'
import { CalendarIcon } from 'lucide-vue-next'
import Calendar from '@/components/ui/calendar/Calendar.vue'
import { DateFormatter, type DateValue, getLocalTimeZone } from '@internationalized/date'
import { cn } from '@/lib/utils'
import Textarea from '@/components/ui/textarea/Textarea.vue'
import { padNumber } from '@/library/common'
import swal from 'sweetalert'


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor',
        href: route('dashboard'),
    },
    {
        title: 'Pengajuan',
        href: route('view.application'),
    },
    {
        title: 'Buat Baru',
        href: route('view.application.create'),
    },
]

const props = defineProps<{
    csrfHash: string,
    access: AccessProp
}>()

// provide access
provide('access', props.access)
provide('csrfHash', props.csrfHash)

// data
const df = new DateFormatter('id-ID', {
  dateStyle: 'long',
})

type Form = {
    name: string,
    date: DateValue | undefined,
    amount: string,
    category_id: string,
    description: string
}
const form: Ref<Form> = ref({
    name: '',
    date: undefined,
    amount: '',
    category_id: '',
    description: '',
})

type Category = {
    id: number,
    code: string,
    name: string,
    limit: number,
}
const categories: Ref<Category[]> = ref([])
const showImagePreview = ref(false)
const imagePreviewHTML: Ref<HTMLImageElement|null> = ref(null)
const showPDFPreview = ref(false)
const pdfPreviewHTML: Ref<HTMLIFrameElement|null> = ref(null)
const multipartForm: Ref<HTMLFormElement|null> = ref(null)
const error = ref({
    title: '',
    description: ''
})

// update kategori
const updateCategoryList = () => {
    const uri = route('reimbursement.category.find.all')
    axios.get(uri)
        .then((response: AxiosResponse) => {
            const res = response.data
            if (res.status === 'success') {
                categories.value = res.data
            }
        })
        .catch((err) => {
            console.error(err)
            alert(err)
        })
}

// store update file
const checkFileUpload = (e: Event) => {
    const input = e.target as HTMLInputElement
    const files = input.files

    const allowedMime = ['image/jpeg', 'application/pdf']

    // reset
    imagePreviewHTML.value?.setAttribute('src', '')
    showImagePreview.value = false
    pdfPreviewHTML.value?.setAttribute('src', '')
    showPDFPreview.value = false

    if (typeof files?.length !== 'undefined') {
        if (files?.length > 0) {
            // validate type
            const file = files[0]
            if (!allowedMime.includes(file.type)) {
                console.warn('File type is not valid')
                alert('Berkas tidak valid, hanya boleh menggunakan gambar .JPG atau .PDF')
                return
            }

            if (file.type === 'image/jpeg') {
                imagePreviewHTML.value?.setAttribute('src', URL.createObjectURL(file))
                showImagePreview.value = true
            }

            if (file.type === 'application/pdf') {
                pdfPreviewHTML.value?.setAttribute('src', URL.createObjectURL(file))
                showPDFPreview.value = true
            }
        }
    }
}

const saveForm = () => {
    if (multipartForm.value === null) {
        return
    }

    const date = form.value.date as DateValue
    const amountStr = String(form.value.amount)

    const month = padNumber(date.month, 2);
    const days = padNumber(date.day, 2);

    const selectedDate = `${date.year}-${month}-${days}`

    const formData = new FormData(multipartForm?.value)
    formData.append('_token', props.csrfHash)
    formData.append('name', form.value.name)
    formData.append('date', selectedDate)
    formData.append('amount', amountStr.replace(/\D/g, ''))
    formData.append('category_id', form.value.category_id)
    formData.append('description', form.value.description)

    // send file
    axios.post(route('reimbursement.main.create'), formData)
        .then((response: AxiosResponse) => {
            const res = response.data
            if (res.status === 'success') {
                router.visit(route('view.application'))
            }
        })
        .catch((err) => {
            console.error(err)

            if (typeof err.response.data !== 'undefined') {
                error.value.title = 'Gagal Menambahkan Reimbursement'
                error.value.description = err.response.data.message

                swal({
                    title: error.value.title,
                    text: error.value.description,
                    icon: "error",
                });
            }
        })
}
// update data
updateCategoryList()

</script>