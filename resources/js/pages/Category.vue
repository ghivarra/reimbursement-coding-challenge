<template>
    <Head title="Kategori Reimbursement"></Head>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <Heading title="Kategori Reimbursement" description="Mencakup pembuatan, modifikasi, dan penghapusan kategori reimbursement" class="mb-0" />

            <CategoryCreate v-on:insert="updateCategories" />

            <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative mb-2">
                    <Input v-model="query" name="categoryQuery" placeholder="Cari..." />
                    <Icon class="absolute right-[0.75rem] top-[0.5rem]" name="Search" />
                </div>
            </div>
            <CategoryListItem 
                v-for="(category, index) in filteredCategories" 
                :id="category.id"
                :name="category.name" 
                :code="category.code" 
                :limit="category.limit_per_month" 
                :update-list="updateCategories"
                :key="index" />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">

import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import Icon from '@/components/Icon.vue'
import Input from '@/components/ui/input/Input.vue'
import { AccessProp, BreadcrumbItem } from '@/types'
import { onMounted, provide, Ref, ref, watch } from 'vue'
import CategoryListItem from './custom-components/CategoryListItem.vue'
import axios, { AxiosResponse } from 'axios'
import CategoryCreate from './custom-dialogs/CategoryCreate.vue'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor',
        href: route('dashboard'),
    },
    {
        title: 'Kategori',
        href: route('view.category'),
    },
]

const props = defineProps<{
    access: AccessProp,
    csrfHash: string,
}>()

// provide access
provide('access', props.access)
provide('csrfHash', props.csrfHash)

// categories
type Category = {
    id: number,
    name: string,
    code: string,
    limit_per_month: number
}

// data
const categories: Ref<Category[]> = ref([])
const filteredCategories: Ref<Category[]> = ref([])
const query = ref('')

// fetch data
const updateCategories = (): void => {

    // build form
    const formData = new FormData()
    formData.append('limit', '10')
    formData.append('offset', '0')
    formData.append('order[column]', 'name')
    formData.append('order[dir]', 'asc')
    formData.append('_token', props.csrfHash)

    // post
    axios.post(route('reimbursement.category.index'), formData)
        .then((response: AxiosResponse) => {
            const res = response.data
            if (res.status === 'success') {
                categories.value = res.data
                filteredCategories.value = res.data
            }
        })
        .catch((err) => {
            console.error(err)
        })
}

const searchList = (query: string): void => {

    // reset if empty
    if (query.length < 1) {
        filteredCategories.value = categories.value
    }

    filteredCategories.value = categories.value.filter((category) => {
        if (category.name.includes(query) || category.code === query) {
            return category
        }
    })
}

watch(query, (newValue) => {
    searchList(newValue)
})

onMounted(() => {
    updateCategories()
})

</script>