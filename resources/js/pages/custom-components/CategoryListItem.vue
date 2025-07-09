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
                <Button type="button" variant="default" size="sm" class="mr-2">
                    <Icon name="SquarePen" />
                    Update
                </Button>
                <CategoryDelete :id="props.id" :name="props.name" v-on:delete="props.updateList()" />
            </div>
        </CardFooter>
    </Card>

</template>

<script setup lang="ts">

import Card from '@/components/ui/card/Card.vue'
import CardHeader from '@/components/ui/card/CardHeader.vue'
import Button from '@/components/ui/button/Button.vue'
import Icon from '@/components/Icon.vue'
import CardFooter from '@/components/ui/card/CardFooter.vue'
import CardTitle from '@/components/ui/card/CardTitle.vue'
import CardDescription from '@/components/ui/card/CardDescription.vue'
import CardAction from '@/components/ui/card/CardAction.vue'
import { computed } from 'vue'
import { formatCurrency } from '@/library/common'
import CategoryDelete from '../custom-dialogs/CategoryDelete.vue'

const props = defineProps<{
    id: number,
    name: string,
    code: string,
    limit: number,
    updateList: () => void
}>()

const limitStr = computed(() => formatCurrency(props.limit))

</script>