<!-- src/views/admin/contacts/ContactView.vue -->
<template>
  <BaseBreadCrump :items="breadcrumbItems" />

  <div class="max-w-4xl mx-auto space-y-6">

    <!-- Profile Card -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-4">
          <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-2xl font-bold text-blue-600">
            {{ contact?.name?.slice(0, 2).toUpperCase() }}
          </div>
          <div>
            <h2 class="text-xl font-semibold text-gray-800">{{ contact?.name }}</h2>
            <p class="text-sm text-gray-500">{{ contact?.company ?? '—' }}</p>
            <span
              :class="statusClass(contact?.status)"
              class="mt-1 inline-block text-xs px-2 py-0.5 rounded-full font-medium"
            >
              {{ contact?.status }}
            </span>
          </div>
        </div>

        <BaseButton
          type="button"
          @click="$router.push(`/admin/contacts/${contact?.id}/edit`)"
          :class="'bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700'"
        >
          Edit Contact
        </BaseButton>
      </div>

      <!-- Details Grid -->
      <div class="mt-6 grid grid-cols-2 gap-4 border-t pt-4 border-gray-200">
        <div v-for="detail in details" :key="detail.label">
          <p class="text-xs text-gray-400 uppercase tracking-wide">{{ detail.label }}</p>
          <p class="text-sm text-gray-700 font-medium mt-0.5">{{ detail.value || '—' }}</p>
        </div>
      </div>

      <div v-if="contact?.notes" class="mt-4 border-t pt-4 border-gray-200">
        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Notes</p>
        <p class="text-sm text-gray-700">{{ contact.notes }}</p>
      </div>
    </div>

    <!-- Linked Leads -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Leads</h3>
        <BaseButton
          type="button"
          @click="$router.push(`/admin/leads/create?contact_id=${contact?.id}`)"
          :class="'bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm'"
        >
          + Add Lead
        </BaseButton>
      </div>

      <div v-if="leads.length === 0" class="text-sm text-gray-400 py-4 text-center">
        No leads yet for this contact.
      </div>

      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
          <tr>
            <th class="px-4 py-2 text-left">Title</th>
            <th class="px-4 py-2 text-left">Source</th>
            <th class="px-4 py-2 text-left">Stage</th>
            <th class="px-4 py-2 text-left">Assigned To</th>
            <th class="px-4 py-2 text-left">Created</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="lead in leads" :key="lead.id" class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <router-link
                :to="`/admin/leads/${lead.id}`"
                class="text-blue-600 hover:underline font-medium"
              >
                {{ lead.title }}
              </router-link>
            </td>
            <td class="px-4 py-3 capitalize">{{ lead.source }}</td>
            <td class="px-4 py-3">
              <span :class="stageClass(lead.stage)" class="px-2 py-0.5 rounded-full text-xs font-medium">
                {{ lead.stage }}
              </span>
            </td>
            <td class="px-4 py-3">{{ lead.assigned_to?.name ?? 'Unassigned' }}</td>
            <td class="px-4 py-3">{{ lead.created_at }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Activity Timeline placeholder (Sprint 4) -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-2">Activity Timeline</h3>
        <TasksSection
        :subject-type="'Contact'"
        :subject-id="contact?.id ?? 0"
        />

      <ActivityTimeline
        :subject-type="'Contact'"
        :subject-id="contact?.id ?? 0"
      />

    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import BaseBreadCrump from '@/components/common/BaseBreadCrump.vue'
import BaseButton from '@/components/common/BaseButton.vue'
import api from '@/api/axios'
import TasksSection      from '@/components/common/TasksSection.vue'
import ActivityTimeline  from '@/components/common/ActivityTimeline.vue'

const route = useRoute()
const router = useRouter()

interface Contact {
  id: number
  name: string
  email: string
  phone?: string
  company?: string
  status: string
  notes?: string
  assigned_to?: { id: number; name: string }
  created_at: string
}

interface Lead {
  id: number
  title: string
  source: string
  stage: string
  assigned_to?: { name: string }
  created_at: string
}

const contact = ref<Contact | null>(null)
const leads   = ref<Lead[]>([])

const breadcrumbItems = computed(() => [
  { label: 'Contacts', link: '/admin/contacts' },
  { label: contact.value?.name ?? 'View' },
])

const details = computed(() => [
  { label: 'Email',       value: contact.value?.email },
  { label: 'Phone',       value: contact.value?.phone },
  { label: 'Company',     value: contact.value?.company },
  { label: 'Assigned To', value: contact.value?.assigned_to?.name },
  { label: 'Created At',  value: contact.value?.created_at },
])

const statusClass = (status?: string) => ({
  'bg-green-100 text-green-700':  status === 'active',
  'bg-gray-100 text-gray-600':    status === 'inactive',
  'bg-yellow-100 text-yellow-700': status === 'prospect',
})

const stageClass = (stage?: string) => ({
  'bg-blue-100 text-blue-700':    stage === 'new',
  'bg-yellow-100 text-yellow-700': stage === 'contacted',
  'bg-purple-100 text-purple-700': stage === 'qualified',
  'bg-red-100 text-red-700':      stage === 'lost',
})

const fetchContact = async () => {
  const res = await api.get(`/contacts/${route.params.id}`)
  contact.value = res.data.data
}

const fetchLeads = async () => {
  try {
    const res = await api.get('/leads', {
      params: { filter: { contact_id: route.params.id }, per_page: 100 }
    })
    leads.value = res.data.data
  } catch {
    leads.value = []
  }
}

onMounted(async () => {
  await fetchContact()
  await fetchLeads()
})
</script>