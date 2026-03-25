<!-- src/views/admin/Contacts.vue -->
<template>
  <BaseTable
    title="Contacts"
    :columns="columns"
    :rows="contacts"
    :currentPage="page"
    :totalPages="totalPages"
    @page-change="fetchContactsData"
    @per-page-change="changePerPage"
  >
    <template #header-action>
      <BaseButton
        @click="$router.push('/admin/contacts/create')"
        :class="'bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors'"
      >
        Add Contact
      </BaseButton>
    </template>

    <template #filters>
      <BaseFilter :filters="filterConfig" @filter-change="applyFilters" />
    </template>

    <template #actions="{ row }">
      <BaseActionMenu
        :row="row"
        :actions="[
          { label: 'View',   key: 'view',   route: `/admin/contacts/${row.id}` },
          { label: 'Edit',   key: 'edit',   route: `/admin/contacts/${row.id}/edit` },
          { label: 'Delete', key: 'delete', danger: true },
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
import { deleteContact } from '@/services/api'

interface Contact {
  id: number
  name: string
  email: string
  phone: string
  company: string
  status: string
  assigned_to: string
  created_at: string
}

const contacts   = ref<Contact[]>([])
const page       = ref(1)
const totalPages = ref(1)
const perPage    = ref(10)
const filters    = ref({})
const toast      = useToast()

const columns = [
  { key: 'id',          label: 'ID' },
  { key: 'name',        label: 'Name' },
  { key: 'email',       label: 'Email' },
  { key: 'phone',       label: 'Phone' },
  { key: 'company',     label: 'Company' },
  { key: 'status',      label: 'Status' },
  { key: 'assigned_to', label: 'Assigned To' },
  { key: 'created_at',  label: 'Created At' },
]

const filterConfig = computed(() => [
  { key: 'name',    label: 'Name',    type: 'text',   placeholder: 'Search name...' },
  { key: 'email',   label: 'Email',   type: 'text',   placeholder: 'Search email...' },
  { key: 'company', label: 'Company', type: 'text',   placeholder: 'Search company...' },
  {
    key: 'status', label: 'Status', type: 'select',
    options: [
      { label: 'Prospect', value: 'prospect' },
      { label: 'Active',   value: 'active' },
      { label: 'Inactive', value: 'inactive' },
    ],
  },
  { key: 'created_at', label: 'Created At', type: 'date' },
])

const fetchContactsData = async (pageNumber = 1) => {
  try {
    const cleanFilters = Object.fromEntries(
      Object.entries(filters.value).filter(([_, v]) => v !== '' && v !== null && v !== false)
    )
    const res = await api.get('/contacts', {
      params: { page: pageNumber, per_page: perPage.value, filter: cleanFilters },
    })

    contacts.value = res.data.data.map((c: any): Contact => ({
      id:          c.id,
      name:        c.name,
      email:       c.email,
      phone:       c.phone       ?? '—',
      company:     c.company     ?? '—',
      status:      c.status,
      assigned_to: c.assigned_to?.name ?? 'Unassigned',
      created_at:  c.created_at,
    }))

    page.value       = res.data.meta.current_page
    totalPages.value = res.data.meta.last_page
  } catch (e) {
    console.error(e)
  }
}

const applyFilters   = (f: any) => { filters.value = f; fetchContactsData(1) }
const changePerPage  = (v: number) => { perPage.value = v; fetchContactsData(1) }

const handleTableAction = async ({ action, row }: any) => {
  if (action === 'delete') {
    if (!confirm('Delete this contact?')) return
    try {
      const res = await deleteContact(row.id)
      toast.success(res.message)
      fetchContactsData(page.value)
    } catch {
      toast.error('Something went wrong')
    }
  }
}

onMounted(() => fetchContactsData(1))
</script>