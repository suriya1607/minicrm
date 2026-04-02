<template>
  <BaseTable
    title="All Tasks"
    :columns="columns"
    :rows="tasks"
    :currentPage="page"
    :totalPages="totalPages"
    @page-change="loadTasks"
    @per-page-change="changePerPage"
  >
    <template #filters>
      <BaseFilter :filters="filterConfig" @filter-change="applyFilters" />
    </template>
    <template #actions="{ row }">
      <BaseActionMenu
        :row="row"
        :actions="[{ label: 'Delete', key: 'delete', danger: true }]"
        @action="handleAction"
      />
    </template>
  </BaseTable>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import BaseTable from '@/components/common/BaseTable.vue'
import BaseFilter from '@/components/common/BaseFilter.vue'
import BaseActionMenu from '@/components/common/BaseActionMenu.vue'
import { fetchTasks, deleteTask } from '@/services/api'

const tasks      = ref<any[]>([])
const page       = ref(1)
const totalPages = ref(1)
const perPage    = ref(15)
const filters    = ref({})
const toast      = useToast()

const columns = [
  { key: 'id',          label: 'ID' },
  { key: 'title',       label: 'Title' },
  { key: 'priority',    label: 'Priority' },
  { key: 'status',      label: 'Status' },
  { key: 'due_date',    label: 'Due Date' },
  { key: 'assigned_to', label: 'Assigned To' },
  { key: 'for',         label: 'For' },
]

const filterConfig = computed(() => [
  {
    key: 'status', label: 'Status', type: 'select',
    options: [
      { label: 'Pending',     value: 'pending' },
      { label: 'In progress', value: 'in_progress' },
      { label: 'Completed',   value: 'completed' },
    ],
  },
  {
    key: 'priority', label: 'Priority', type: 'select',
    options: [
      { label: 'Low',    value: 'low' },
      { label: 'Medium', value: 'medium' },
      { label: 'High',   value: 'high' },
    ],
  },
])

const loadTasks = async (pageNum = 1) => {
  const clean = Object.fromEntries(
    Object.entries(filters.value).filter(([_, v]) => v !== '')
  )
  const res = await fetchTasks({ page: pageNum, per_page: perPage.value, filter: clean })
  tasks.value = res.data.map((t: any) => ({
    ...t,
    assigned_to: t.assigned_to?.name ?? 'Unassigned',
    for: `${t.taskable_type} #${t.taskable_id}`,
  }))
  page.value       = res.meta.current_page
  totalPages.value = res.meta.last_page
}

const applyFilters  = (f: any) => { filters.value = f; loadTasks(1) }
const changePerPage = (v: number) => { perPage.value = v; loadTasks(1) }

const handleAction = async ({ action, row }: any) => {
  if (action === 'delete') {
    if (!confirm('Delete this task?')) return
    try {
      await deleteTask(row.id)
      toast.success('Task deleted')
      loadTasks(page.value)
    } catch { toast.error('Error') }
  }
}

onMounted(() => loadTasks(1))
</script>