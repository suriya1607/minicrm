<template>
  <div class="relative">

    <!-- Filter Button -->
    <button
      ref="buttonRef"
      @click="toggle"
      class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 transition shadow-sm"
    >
      <!-- Icon -->
      <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M3 4h18M6 12h12M10 20h4" />
      </svg>

      <span>Filters</span>

      <!-- Badge -->
      <span
        v-if="activeCount"
        class="ml-1 bg-blue-600 text-white text-xs px-2 py-0.5 rounded-full"
      >
        {{ activeCount }}
      </span>
    </button>

    <!-- Selected Filters Chips -->
    <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 mt-3">
      <span
        v-for="(val, key) in values"
        v-if="val"
        :key="key"
        class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs flex items-center gap-1"
      >
        {{ getLabel(key) }}: {{ val }}
        <button @click="clearFilter(key)">✕</button>
      </span>
    </div>

    <!-- Dropdown Panel -->
    <Transition name="accordion">
      <div
        v-show="open"
        ref="panelRef"
        class="absolute right-0 mt-2 w-[420px] bg-white border  border-gray-300 rounded-lg shadow-lg p-4 z-50"
      >

        <!-- Filters -->
        <div class="grid grid-cols-2 gap-4">
          <BaseInput
            v-for="filter in filters"
            :key="filter.key"
            v-model="values[filter.key]"
            :label="filter.label"
            :type="filter.type"
            :placeholder="filter.placeholder"
            :options="filter.options"
            :multiple="filter.multiple"
          />
        </div>

        <!-- Actions -->
        <div class="flex justify-between mt-4">
          <button
            @click="resetFilters"
            class="text-sm text-red-500 hover:underline"
          >
            Clear all
          </button>

          <button
            @click="applyFilters"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
          >
            Apply Filters
          </button>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup lang="ts">
import { reactive, computed, ref, onMounted, onBeforeUnmount } from "vue"
import BaseInput from "@/components/common/BaseInput.vue"

interface FilterOption {
  label: string
  value: string | number | boolean
}

interface FilterConfig {
  key: string
  label: string
  type: string   // text | select | date | checkbox
  placeholder?: string
  options?: FilterOption[]
  multiple?: boolean
}

const props = defineProps<{
  filters: FilterConfig[]
}>()

const emit = defineEmits(["filter-change"])

const open = ref(false)
const panelRef = ref<HTMLElement | null>(null)
const buttonRef = ref<HTMLElement | null>(null)

const values = reactive<Record<string, any>>({})

// Initialize filter values
props.filters.forEach(f => {
  if (f.multiple) {
    values[f.key] = []
  } else if (f.type === "checkbox") {
    values[f.key] = false
  } else {
    values[f.key] = ""
  }
})

const toggle = () => {
  open.value = !open.value
}

const applyFilters = () => {
  emit("filter-change", { ...values })
  open.value = false
}

const resetFilters = () => {
  Object.keys(values).forEach(k => {
    values[k] = typeof values[k] === "boolean" ? false : ""
  })
  emit("filter-change", { ...values })
}

const clearFilter = (key: string) => {
  values[key] = typeof values[key] === "boolean" ? false : ""
  emit("filter-change", { ...values })
}

const hasActiveFilters = computed(() => {
  return Object.values(values).some(v =>
    Array.isArray(v) ? v.length > 0 : v
  )
})

const activeCount = computed(() => {
  return Object.values(values).filter(v =>
    Array.isArray(v) ? v.length > 0 : v
  ).length
})

const getLabel = (key: string) => {
  return props.filters.find(f => f.key === key)?.label || key
}

// Close on outside click
const handleClickOutside = (event: MouseEvent) => {
  if (
    panelRef.value &&
    !panelRef.value.contains(event.target as Node) &&
    buttonRef.value &&
    !buttonRef.value.contains(event.target as Node)
  ) {
    open.value = false
  }
}

onMounted(() => {
  document.addEventListener("click", handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside)
})
</script>

<style scoped>
.accordion-enter-active,
.accordion-leave-active {
  transition: all 0.25s ease;
}
.accordion-enter-from,
.accordion-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
