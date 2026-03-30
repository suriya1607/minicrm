<template>
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold text-gray-800">Deals Pipeline</h1>
    <BaseButton
      @click="$router.push('/admin/deals/create')"
      :class="'bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700'"
    >
      + Add Deal
    </BaseButton>
  </div>

  <BaseLoader v-if="loading" />

  <div v-else class="flex gap-4 overflow-x-auto pb-4">
    <div
      v-for="stage in stages"
      :key="stage.key"
      class="flex-shrink-0 w-72 bg-gray-50 rounded-xl p-3"
      @dragover.prevent
      @drop="onDrop($event, stage.key)"
    >
      <!-- Column header -->
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
          <span :class="stage.dot" class="w-2.5 h-2.5 rounded-full inline-block"></span>
          <span class="text-sm font-semibold text-gray-700 capitalize">{{ stage.label }}</span>
        </div>
        <span class="text-xs bg-gray-200 text-gray-600 rounded-full px-2 py-0.5">
          {{ board[stage.key]?.length ?? 0 }}
        </span>
      </div>

      <!-- Total value -->
      <div class="text-xs text-gray-400 mb-3">
        Total: ₹{{ formatValue(stageTotal(stage.key)) }}
      </div>

      <!-- Deal cards -->
      <div class="space-y-2 min-h-24">
        <div
          v-for="deal in board[stage.key]"
          :key="deal.id"
          draggable="true"
          @dragstart="onDragStart($event, deal)"
          class="bg-white rounded-lg p-3 shadow-sm border border-gray-100 cursor-grab active:cursor-grabbing hover:shadow-md transition-shadow"
        >
          <!-- Card title + actions -->
          <div class="flex items-start justify-between gap-1">
            <router-link
              :to="`/admin/deals/${deal.id}`"
              class="text-sm font-medium text-gray-800 hover:text-blue-600 leading-tight"
            >
              {{ deal.title }}
            </router-link>
            <BaseActionMenu
              :row="deal"
              :actions="[
                { label: 'View',   key: 'view',   route: `/admin/deals/${deal.id}` },
                { label: 'Edit',   key: 'edit',   route: `/admin/deals/${deal.id}/edit` },
                { label: 'Delete', key: 'delete', danger: true },
              ]"
              @action="handleAction"
            />
          </div>

          <!-- Value badge -->
          <div class="mt-2 text-xs font-semibold text-green-700 bg-green-50 inline-block px-2 py-0.5 rounded-full">
            ₹{{ formatValue(deal.value) }}
          </div>

          <!-- Contact -->
          <div class="mt-2 flex items-center gap-1 text-xs text-gray-500">
            <span class="w-4 h-4 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-semibold text-[10px]">
              {{ deal.contact?.name?.slice(0, 1).toUpperCase() }}
            </span>
            {{ deal.contact?.name }}
          </div>

          <!-- Close date -->
          <div v-if="deal.expected_close_date" class="mt-1.5 text-xs text-gray-400">
            Close: {{ deal.expected_close_date }}
          </div>

          <!-- Assigned -->
          <div v-if="deal.assigned_to?.name" class="mt-1 text-xs text-gray-400">
            Assigned: {{ deal.assigned_to.name }}
          </div>
        </div>

        <!-- Empty state -->
        <div
          v-if="!board[stage.key]?.length"
          class="text-xs text-gray-300 text-center py-6 border-2 border-dashed border-gray-200 rounded-lg"
        >
          Drop deals here
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import BaseButton from '@/components/common/BaseButton.vue'
import BaseLoader from '@/components/common/BaseLoader.vue'
import BaseActionMenu from '@/components/common/BaseActionMenu.vue'
import api from '@/api/axios'
import { deleteDeal, moveDeal } from '@/services/api'

interface Deal {
  id: number
  title: string
  value: number
  stage: string
  contact?: { id: number; name: string }
  assigned_to?: { id: number; name: string }
  expected_close_date?: string
}

const toast   = useToast()
const loading = ref(true)

const stages = [
  { key: 'proposal',    label: 'Proposal',    dot: 'bg-blue-400' },
  { key: 'negotiation', label: 'Negotiation', dot: 'bg-yellow-400' },
  { key: 'agreement',   label: 'Agreement',   dot: 'bg-purple-400' },
  { key: 'won',         label: 'Won',         dot: 'bg-green-500' },
  { key: 'lost',        label: 'Lost',        dot: 'bg-red-400' },
]

const board = reactive<Record<string, Deal[]>>({
  proposal: [], negotiation: [], agreement: [], won: [], lost: [],
})

const draggedDeal = ref<Deal | null>(null)

const fetchBoard = async () => {
  loading.value = true
  try {
    const res = await api.get('/deals', { params: { kanban: 1 } })
    const data = res.data.data
    for (const stage of stages) {
      board[stage.key] = data[stage.key] ?? []
    }
  } catch (e) {
    toast.error('Failed to load deals')
  } finally {
    loading.value = false
  }
}

const onDragStart = (event: DragEvent, deal: Deal) => {
  draggedDeal.value = deal
  event.dataTransfer?.setData('text/plain', String(deal.id))
}

const onDrop = async (event: DragEvent, targetStage: string) => {
  if (!draggedDeal.value) return
  const deal = draggedDeal.value
  if (deal.stage === targetStage) return

  // Optimistic UI update
  board[deal.stage] = board[deal.stage].filter(d => d.id !== deal.id)
  deal.stage = targetStage
  board[targetStage].unshift(deal)

  try {
    await moveDeal(deal.id, targetStage)
    toast.success('Deal moved')
  } catch {
    toast.error('Failed to move deal')
    await fetchBoard()
  }

  draggedDeal.value = null
}

const stageTotal = (stage: string) =>
  (board[stage] ?? []).reduce((sum, d) => sum + Number(d.value), 0)

const formatValue = (v: number) =>
  new Intl.NumberFormat('en-IN').format(v)

const handleAction = async ({ action, row }: any) => {
  if (action === 'delete') {
    if (!confirm('Delete this deal?')) return
    try {
      await deleteDeal(row.id)
      toast.success('Deal deleted')
      await fetchBoard()
    } catch {
      toast.error('Failed to delete')
    }
  }
}

onMounted(fetchBoard)
</script>