<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from "vue"
import { useRouter } from "vue-router"
import api from "@/api/axios";
import { useToast } from "vue-toastification"

const router = useRouter()
const toast = useToast()

const handleAction = async (action: ActionItem) => {
  try {

    // Route action
    if (action.route) {
      await router.push(action.route)
      return
    }

    // Default emit
    emit("action", {
      action: action.key,
      row: props.row
    })

  } finally {

    close()

  }
}


interface ActionItem {
  label: string
  key: string
  danger?: boolean
  route?: string
  api?: {
    method: string
    url: string
  }
}

const props = defineProps<{
  actions: ActionItem[]
  row: any
}>()

const emit = defineEmits(["action"])

const open = ref(false)

const toggle = () => (open.value = !open.value)
const close = () => (open.value = false)

const handleClickOutside = (e: MouseEvent) => {
  if (!(e.target as HTMLElement).closest(".action-menu")) close()
}

onMounted(() => document.addEventListener("click", handleClickOutside))
onBeforeUnmount(() => document.removeEventListener("click", handleClickOutside))
</script>

<template>
  <div class="relative inline-block">
    <!-- 3 dot -->
    <button
      @click.stop="toggle"
      class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100"
    >
      ⋮
    </button>

    <!-- dropdown (right side) -->
    <div
      v-if="open"
      class="absolute left-full top-0 ml-2 w-28 bg-white border border-gray-200 rounded shadow z-50"
    >
      <button
        v-for="action in actions"
        :key="action.key"
        @click="handleAction(action)"
        class="block w-full text-left px-3 py-2 hover:bg-gray-100"
        :class="action.danger ? 'text-red-600' : ''"
      >
        {{ action.label }}
      </button>
    </div>
  </div>
</template>