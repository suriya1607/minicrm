<template>
  <BaseTable
    title="Leads"
    :columns="columns"
    :rows="leads"
    :currentPage="page"
    :totalPages="totalPages"
    @page-change="fetchLeadsData"
    @per-page-change="changePerPage"
  >
    <template #header-action>
      <BaseButton
        @click="$router.push('/admin/leads/create')"
        :class="'bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700'"
      >
        Add Lead
      </BaseButton>
    </template>

    <template #filters>
      <BaseFilter :filters="filterConfig" @filter-change="applyFilters" />
    </template>

    <template #actions="{ row }">
      <BaseActionMenu
        :row="row"
        :actions="[
          { label: 'View',    key: 'view',    route: `/admin/leads/${row.id}` },
          { label: 'Edit',    key: 'edit',    route: `/admin/leads/${row.id}/edit` },
          { label: 'Delete',  key: 'delete',  danger: true },
        ]"
        @action="handleTableAction"
      />
    </template>
  </BaseTable>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import BaseTable from '@/components/common/BaseTable.vue'
import BaseButton from '@/components/common/BaseButton.vue'
import BaseFilter from '@/components/common/BaseFilter.vue'
import BaseActionMenu from '@/components/common/BaseActionMenu.vue'
import api from '@/api/axios'
import { deleteLead } from '@/services/api'

interface Lead {
  id: number
  title: string
  contact: string
  source: string
  stage: string
  assigned_to: string
  has_deal: boolean
  created_at: string
}

const leads      = ref<Lead[]>([])
const page       = ref(1)
const totalPages = ref(1)
const perPage    = ref(10)
const filters    = ref({})
const toast      = useToast()

const columns = [
  { key: 'id',          label: 'ID' },
  { key: 'title',       label: 'Title' },
  { key: 'contact',     label: 'Contact' },
  { key: 'source',      label: 'Source' },
  { key: 'stage',       label: 'Stage' },
  { key: 'assigned_to', label: 'Assigned To' },
  { key: 'created_at',  label: 'Created At' },
]

const filterConfig = computed(() => [
  { key: 'title',  label: 'Title',  type: 'text', placeholder: 'Search title...' },
  {
    key: 'stage', label: 'Stage', type: 'select',
    options: [
      { label: 'New',       value: 'new' },
      { label: 'Contacted', value: 'contacted' },
      { label: 'Qualified', value: 'qualified' },
      { label: 'Lost',      value: 'lost' },
    ],
  },
  {
    key: 'source', label: 'Source', type: 'select',
    options: [
      { label: 'Website',  value: 'website' },
      { label: 'Referral', value: 'referral' },
      { label: 'Social',   value: 'social' },
      { label: 'Email',    value: 'email' },
      { label: 'Phone',    value: 'phone' },
      { label: 'Other',    value: 'other' },
    ],
  },
  { key: 'created_at', label: 'Created At', type: 'date' },
])

const fetchLeadsData = async (pageNumber = 1) => {
  try {
    const cleanFilters = Object.fromEntries(
      Object.entries(filters.value).filter(([_, v]) => v !== '' && v !== null && v !== false)
    )
    const res = await api.get('/leads', {
      params: { page: pageNumber, per_page: perPage.value, filter: cleanFilters },
    })

    leads.value = res.data.data.map((l: any): Lead => ({
      id:          l.id,
      title:       l.title,
      contact:     l.contact?.name ?? '—',
      source:      l.source,
      stage:       l.stage,
      assigned_to: l.assigned_to?.name ?? 'Unassigned',
      has_deal:    l.has_deal,
      created_at:  l.created_at,
    }))

    page.value       = res.data.meta.current_page
    totalPages.value = res.data.meta.last_page
  } catch (e) {
    console.error(e)
  }
}

const applyFilters  = (f: any) => { filters.value = f; fetchLeadsData(1) }
const changePerPage = (v: number) => { perPage.value = v; fetchLeadsData(1) }

const handleTableAction = async ({ action, row }: any) => {
  if (action === 'delete') {
    if (!confirm('Delete this lead?')) return
    try {
      const res = await deleteLead(row.id)
      toast.success(res.message)
      fetchLeadsData(page.value)
    } catch {
      toast.error('Something went wrong')
    }
  }
}

onMounted(() => fetchLeadsData(1))
</script>