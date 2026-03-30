<template>
  <BaseBreadCrump :items="breadcrumbItems" />

  <div class="max-w-3xl mx-auto space-y-6">

    <!-- Lead Card -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-start justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-800">{{ lead?.title }}</h2>
          <p class="text-sm text-gray-500 mt-1">
            Contact:
            <router-link
              :to="`/admin/contacts/${lead?.contact?.id}`"
              class="text-blue-600 hover:underline"
            >
              {{ lead?.contact?.name }}
            </router-link>
          </p>
        </div>

        <div class="flex gap-2">
          <BaseButton
            type="button"
            @click="$router.push(`/admin/leads/${lead?.id}/edit`)"
            :class="'bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm'"
          >
            Edit
          </BaseButton>

          <BaseButton
            v-if="!lead?.has_deal"
            type="button"
            @click="showConvertModal = true"
            :class="'bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm'"
          >
            Convert to Deal
          </BaseButton>

          <span
            v-else
            class="bg-purple-100 text-purple-700 text-xs px-3 py-2 rounded-lg font-medium"
          >
            Converted to Deal
          </span>
        </div>
      </div>

      <!-- Details -->
      <div class="mt-6 grid grid-cols-2 gap-4 border-t pt-4">
        <div v-for="detail in details" :key="detail.label">
          <p class="text-xs text-gray-400 uppercase tracking-wide">{{ detail.label }}</p>
          <p class="text-sm text-gray-700 font-medium mt-0.5">{{ detail.value || '—' }}</p>
        </div>
      </div>

      <div v-if="lead?.notes" class="mt-4 border-t pt-4">
        <p class="text-xs text-gray-400 uppercase">Notes</p>
        <p class="text-sm text-gray-700 mt-1">{{ lead.notes }}</p>
      </div>
    </div>

    <!-- Stage Progress -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-sm font-semibold text-gray-700 mb-4">Lead Stage</h3>
      <div class="flex items-center gap-2">
        <div
          v-for="(s, i) in stages"
          :key="s"
          class="flex items-center gap-2"
        >
          <div
            :class="[
              'px-3 py-1 rounded-full text-xs font-medium capitalize',
              lead?.stage === s
                ? 'bg-blue-600 text-white'
                : stageIndex(lead?.stage) > i
                  ? 'bg-green-100 text-green-700'
                  : 'bg-gray-100 text-gray-500'
            ]"
          >
            {{ s }}
          </div>
          <span v-if="i < stages.length - 1" class="text-gray-300">→</span>
        </div>
      </div>
    </div>

  </div>

  <!-- Convert Modal -->
  <div
    v-if="showConvertModal"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
  >
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
      <h3 class="text-lg font-semibold mb-4">Convert Lead to Deal</h3>

      <div class="space-y-3">
        <BaseInput v-model="dealForm.title"               label="Deal Title"        type="text"   placeholder="e.g. E-commerce Website Project" />
        <BaseInput v-model="dealForm.value"               label="Deal Value (₹)"    type="text"   placeholder="e.g. 150000" />
        <BaseInput v-model="dealForm.expected_close_date" label="Expected Close Date" type="date" placeholder="" />
        <BaseInput
          v-model="dealForm.stage"
          label="Pipeline Stage"
          type="select"
          :options="[
            { label: 'Proposal',     value: 'proposal' },
            { label: 'Negotiation',  value: 'negotiation' },
            { label: 'Agreement',    value: 'agreement' },
          ]"
        />
      </div>

      <div class="flex gap-3 mt-6">
        <BaseButton @click="convertLead" :class="'bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700'">
          Convert
        </BaseButton>
        <BaseButton
          type="button"
          @click="showConvertModal = false"
          :class="'bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300'"
        >
          Cancel
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import BaseBreadCrump from '@/components/common/BaseBreadCrump.vue'
import BaseButton from '@/components/common/BaseButton.vue'
import BaseInput from '@/components/common/BaseInput.vue'
import { fetchLead, convertLead as convertLeadApi } from '@/services/api'

const route  = useRoute()
const router = useRouter()
const toast  = useToast()

interface Lead {
  id: number
  title: string
  source: string
  stage: string
  notes?: string
  has_deal: boolean
  contact?: { id: number; name: string; email: string }
  assigned_to?: { id: number; name: string }
  created_at: string
}

const lead            = ref<Lead | null>(null)
const showConvertModal = ref(false)

const dealForm = reactive({
  title: '',
  value: '',
  stage: 'proposal',
  expected_close_date: '',
})

const stages     = ['new', 'contacted', 'qualified', 'lost']
const stageIndex = (s?: string) => stages.indexOf(s ?? '')

const breadcrumbItems = computed(() => [
  { label: 'Leads', link: '/admin/leads' },
  { label: lead.value?.title ?? 'View' },
])

const details = computed(() => [
  { label: 'Source',      value: lead.value?.source },
  { label: 'Stage',       value: lead.value?.stage },
  { label: 'Assigned To', value: lead.value?.assigned_to?.name },
  { label: 'Contact',     value: lead.value?.contact?.name },
  { label: 'Created At',  value: lead.value?.created_at },
])

const convertLead = async () => {
  try {
    const res = await convertLeadApi(route.params.id as string, dealForm)
    toast.success(res.message)
    showConvertModal.value = false
    router.push(`/admin/deals/${res.deal_id}`)
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Something went wrong')
  }
}

onMounted(async () => {
  lead.value = await fetchLead(route.params.id as string)
  dealForm.title = `Deal — ${lead.value?.contact?.name ?? ''}`
})
</script>