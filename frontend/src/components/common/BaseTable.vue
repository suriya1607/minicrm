<template>
  <div class="bg-white rounded-lg shadow">
    <!-- Header -->
    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
      <h2 class="text-xl font-semibold text-gray-800">{{ title }}</h2>
      <slot name="header-action"></slot>
    </div>

    <!-- Filters -->
  <div class="p-4 bg-white flex items-center justify-between gap-4">

    <div class="flex items-center gap-3 w-full max-w-md">
        <input
          v-model="search"
          type="text"
          placeholder="Search..."
          class="border border-gray-200 rounded px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
    </div>
  <slot name="filters"></slot>

  </div>


    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th
              v-for="col in columns"
              :key="col.key"
              class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600"
            >
              {{ col.label }}
            </th>
            <th
              v-if="$slots.actions"
              class="px-6 py-3 text-center text-xs font-semibold uppercase text-gray-600"
            >
              Actions
            </th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200">
          <tr
            v-for="row in filteredRows"
            :key="row.id"
            class="hover:bg-gray-50"
          >
            <td
              v-for="col in columns"
              :key="col.key"
              class="px-6 py-4 text-sm text-gray-700"
            >
              {{ row[col.key] }}
            </td>

            <td v-if="$slots.actions" class="px-3 py-2 text-center relative">
              <slot name="actions" :row="row"></slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="p-4 flex justify-between items-center border-t border-gray-200">

      <span class="text-sm">
        <select
          v-model="perPage"
          @change="$emit('per-page-change', perPage)"
          class="border border-gray-200 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option v-for="value in PageNum" :value="value">{{ value }}</option>
        </select>
        <span class="mx-2">Page {{ currentPage }} of {{ totalPages }}</span>
      </span>

      <div class="flex gap-2 items-center">

        <!-- First page -->
        <button
          @click="$emit('page-change', 1)"
          :disabled="currentPage === 1"
          class="px-3 py-1 border rounded border-gray-200"
        >
          <<
        </button>

        <!-- Page numbers -->
        <template v-for="p in pages" :key="p">
          <span v-if="p === '...'" class="px-2">...</span>

          <button
            v-else
            @click="$emit('page-change', p)"
            :class="[
              'px-3 py-1 border  border-gray-200 rounded',
              p === currentPage ? 'bg-blue-500 text-white' : ''
            ]"
          >
            {{ p }}
          </button>
        </template>

        <!-- Last page -->
        <button
          @click="$emit('page-change', totalPages)"
          :disabled="currentPage === totalPages"
          class="px-3 py-1 border  border-gray-200 rounded"
        >
          >>
        </button>

      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue"
import BaseFilter from '@/components/common/BaseFilter.vue'

const perPage = ref(10)
const PageNum = ref([5,10,25,50,100])


interface Column {
  key: string
  label: string
}

const props = defineProps<{
  title: string
  columns: Column[]
  rows: any[]
  currentPage: number
  totalPages: number
}>()

defineEmits(["page-change", "per-page-change"])

const search = ref("")

const filteredRows = computed(() => {
  return props.rows.filter(row =>
    Object.values(row).some(value =>
      String(value).toLowerCase().includes(search.value.toLowerCase())
    )
  )
})

// Pagination numbers with ...
const pages = computed(() => {
  const pages: (number | string)[] = []
  const total = props.totalPages
  const current = props.currentPage

  if (total <= 6) {
    for (let i = 1; i <= total; i++) pages.push(i)
  } else {
    if (current <= 3) {
      pages.push(1, 2, 3, "...", total - 1, total)
    } else if (current >= total - 2) {
      pages.push(1, 2, "...", total - 2, total - 1, total)
    } else {
      pages.push(1, "...", current - 1, current, current + 1, "...", total)
    }
  }

  return pages
})
</script>
