<template>
  <BaseBreadCrump :items="breadcrumbItems" />

  <div class="max-w-3xl mx-auto space-y-6">

    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-start justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-800">{{ deal?.title }}</h2>
          <div class="mt-2 flex items-center gap-3">
            <span :class="stageColor(deal?.stage)" class="text-xs px-3 py-1 rounded-full font-semibold capitalize">
              {{ deal?.stage }}
            </span>
            <span class="text-lg font-bold text-green-700">₹{{ formatValue(deal?.value) }}</span>
          </div>
        </div>
        <BaseButton
          type="button"
          @click="$router.push(`/admin/deals/${deal?.id}/edit`)"
          :class="'bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm'"
        >
          Edit
        </BaseButton>
      </div>

      <div class="mt-6 grid grid-cols-2 gap-4 border-t pt-4">
        <div v-for="d in details" :key="d.label">
          <p class="text-xs text-gray-400 uppercase tracking-wide">{{ d.label }}</p>
          <p class="text-sm text-gray-700 font-medium mt-0.5">{{ d.value || '—' }}</p>
        </div>
      </div>

      <div v-if="deal?.notes" class="mt-4 border-t pt-4">
        <p class="text-xs text-gray-400 uppercase">Notes</p>
        <p class="text-sm text-gray-700 mt-1">{{ deal.notes }}</p>
      </div>
    </div>

    <!-- Pipeline Stage Progress -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-sm font-semibold text-gray-700 mb-4">Pipeline Stage</h3>
      <div class="flex items-center gap-2 flex-wrap">
        <div
          v-for="(s, i) in stages"
          :key="s.key"
          class="flex items-center gap-2"
        >
          <div :class="[
            'px-3 py-1 rounded-full text-xs font-semibold',
            deal?.stage === s.key ? s.active : s.inactive
          ]">
            {{ s.label }}
          </div>
          <span v-if="i < stages.length - 1" class="text-gray-300 text-sm">→</span>
        </div>
      </div>
    </div>

    <!-- Linked Lead -->
    <div v-if="deal?.lead" class="bg-white rounded-lg shadow p-6">
      <h3 class="text-sm font-semibold text-gray-700 mb-2">Converted from Lead</h3>
      <router-link
        :to="`/admin/leads/${deal.lead.id}`"
        class="text-blue-600 hover:underline text-sm"
      >
        {{ deal.lead.title }}
      </router-link>
    </div>

    <!-- Activity placeholder -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-2">Activity Timeline</h3>
      <p class="text-sm text-gray-400">Activities will appear here in Sprint 4.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import BaseBreadCrump from '@/components/common/BaseBreadCrump.vue'
import BaseButton from '@/components/common/BaseButton.vue'
import { fetchDeal } from '@/services/api'

const route = useRoute()

interface Deal {
  id: number
  title: string
  value: number
  stage: string
  notes?: string
  expected_close_date?: string
  contact?: { id: number; name: string; email: string }
  lead?: { id: number; title: string }
  assigned_to?: { id: number; name: string }
  created_at: string
}

const deal = ref<Deal | null>(null)

const breadcrumbItems = computed(() => [
  { label: 'Deals', link: '/admin/deals' },
  { label: deal.value?.title ?? 'View' },
])

const details = computed(() => [
  { label: 'Contact',            value: deal.value?.contact?.name },
  { label: 'Assigned To',        value: deal.value?.assigned_to?.name },
  { label: 'Expected Close Date', value: deal.value?.expected_close_date },
  { label: 'Created At',         value: deal.value?.created_at },
])

const stages = [
  { key: 'proposal',    label: 'Proposal',    active: 'bg-blue-600 text-white',   inactive: 'bg-blue-100 text-blue-600' },
  { key: 'negotiation', label: 'Negotiation', active: 'bg-yellow-500 text-white', inactive: 'bg-yellow-100 text-yellow-700' },
  { key: 'agreement',   label: 'Agreement',   active: 'bg-purple-600 text-white', inactive: 'bg-purple-100 text-purple-700' },
  { key: 'won',         label: 'Won',         active: 'bg-green-600 text-white',  inactive: 'bg-green-100 text-green-700' },
  { key: 'lost',        label: 'Lost',        active: 'bg-red-600 text-white',    inactive: 'bg-red-100 text-red-700' },
]

const stageColor = (stage?: string) => ({
  'bg-blue-100 text-blue-700':    stage === 'proposal',
  'bg-yellow-100 text-yellow-700': stage === 'negotiation',
  'bg-purple-100 text-purple-700': stage === 'agreement',
  'bg-green-100 text-green-700':  stage === 'won',
  'bg-red-100 text-red-700':      stage === 'lost',
})

const formatValue = (v?: number) =>
  v !== undefined ? new Intl.NumberFormat('en-IN').format(v) : '0'

onMounted(async () => {
  deal.value = await fetchDeal(route.params.id as string)
})
</script>