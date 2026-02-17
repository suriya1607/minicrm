<template>
  <div class="mb-4">
    <nav class="text-sm text-gray-500">
      <ol class="flex items-center space-x-2">
        <li v-for="(item, index) in items" :key="index" class="flex items-center">
          
          <RouterLink
            v-if="item.link"
            :to="item.link"
            class="hover:text-blue-600"
          >
            {{ item.label }}
          </RouterLink>

          <span v-else class="text-gray-800 font-semibold">
            {{ item.label }}
          </span>

          <span v-if="index < items.length - 1" class="mx-1">/</span>
        </li>
      </ol>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'

defineProps<{
  items: { label: string; link?: string }[]
}>()
</script>
