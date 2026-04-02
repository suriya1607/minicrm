<template>
    <!-- Dashboard content goes into the slot of AppLayout -->
    <div class="p-6">
      <h1 class="text-xl font-bold mb-4">Dashboard</h1>

       <div class="space-y-6">

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="kpi in kpis"
        :key="kpi.label"
        class="bg-white rounded-xl border border-gray-100 p-5"
      >
        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">{{ kpi.label }}</p>
        <p class="text-3xl font-semibold mt-1" :style="{ color: kpi.color }">{{ kpi.value }}</p>
        <p v-if="kpi.sub" class="text-xs text-gray-400 mt-1">{{ kpi.sub }}</p>
      </div>
    </div>

    <!-- Middle row: Pipeline chart + Tasks summary -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

      <!-- Pipeline by stage chart -->
      <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Deal pipeline</h3>
        <div v-if="stats" class="space-y-3">
          <div
            v-for="stage in dealStages"
            :key="stage.key"
            class="flex items-center gap-3"
          >
            <span class="text-xs text-gray-500 w-24 flex-shrink-0 capitalize">{{ stage.label }}</span>
            <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
              <div
                class="h-2 rounded-full transition-all duration-500"
                :style="{
                  width: stageBarWidth(stage.key) + '%',
                  background: stage.color
                }"
              ></div>
            </div>
            <span class="text-xs font-medium text-gray-600 w-6 text-right">
              {{ stats.deals.by_stage[stage.key] ?? 0 }}
            </span>
          </div>
        </div>

        <!-- Won value summary -->
        <div class="mt-5 pt-4 border-t border-gray-100 grid grid-cols-2 gap-4">
          <div>
            <p class="text-xs text-gray-400">Total pipeline value</p>
            <p class="text-lg font-semibold text-blue-700 mt-0.5">
              ₹{{ formatValue(stats?.deals.pipeline_value) }}
            </p>
          </div>
          <div>
            <p class="text-xs text-gray-400">Won this month</p>
            <p class="text-lg font-semibold text-green-700 mt-0.5">
              ₹{{ formatValue(stats?.deals.won_this_month) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Tasks summary -->
      <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Tasks</h3>
        <div class="space-y-3">
          <div
            v-for="t in taskStats"
            :key="t.label"
            class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0"
          >
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full flex-shrink-0" :style="{ background: t.color }"></span>
              <span class="text-sm text-gray-600">{{ t.label }}</span>
            </div>
            <span class="text-sm font-semibold" :style="{ color: t.color }">{{ t.value }}</span>
          </div>
        </div>
        <router-link
          to="/admin/tasks"
          class="mt-4 block text-center text-xs text-blue-600 hover:underline"
        >
          View all tasks →
        </router-link>
      </div>
    </div>

    <!-- Leads by stage -->
    <div class="bg-white rounded-xl border border-gray-100 p-5">
      <h3 class="text-sm font-semibold text-gray-700 mb-4">Lead funnel</h3>
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div
          v-for="stage in leadStages"
          :key="stage.key"
          class="rounded-lg p-4 text-center"
          :style="{ background: stage.bg }"
        >
          <p class="text-2xl font-semibold" :style="{ color: stage.color }">
            {{ stats?.leads.by_stage[stage.key] ?? 0 }}
          </p>
          <p class="text-xs font-medium mt-1 capitalize" :style="{ color: stage.color }">
            {{ stage.label }}
          </p>
        </div>
      </div>
    </div>

    <!-- Recent activity feed -->
    <div class="bg-white rounded-xl border border-gray-100 p-5">
      <h3 class="text-sm font-semibold text-gray-700 mb-4">Recent activity</h3>

      <div v-if="stats?.recent_activities?.length" class="space-y-1">
        <div
          v-for="activity in stats.recent_activities"
          :key="activity.id"
          class="flex items-start gap-3 py-2.5 border-b border-gray-50 last:border-0"
        >
          <div
            class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0 mt-0.5"
            :style="activityStyle(activity.type)"
          >
            {{ activityIcon(activity.type) }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm text-gray-700">{{ activity.title }}</p>
            <p class="text-xs text-gray-400 mt-0.5">
              {{ activity.subject_type }} · {{ activity.user }} · {{ activity.created_at }}
            </p>
          </div>
        </div>
      </div>

      <div v-else class="text-sm text-gray-400 text-center py-4">No recent activity.</div>
    </div>

  </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'

interface Stats {
  contacts: { total: number; this_month: number }
  leads:    { total: number; by_stage: Record<string, number> }
  deals:    { total: number; by_stage: Record<string, number>; pipeline_value: number; won_value: number; won_this_month: number }
  tasks:    { due_today: number; overdue: number; pending: number }
  recent_activities: any[]
}

const stats = ref<Stats | null>(null)

const dealStages = [
  { key: 'proposal',    label: 'Proposal',    color: '#378ADD' },
  { key: 'negotiation', label: 'Negotiation', color: '#EF9F27' },
  { key: 'agreement',   label: 'Agreement',   color: '#7F77DD' },
  { key: 'won',         label: 'Won',         color: '#1D9E75' },
  { key: 'lost',        label: 'Lost',        color: '#E24B4A' },
]

const leadStages = [
  { key: 'new',       label: 'New',       bg: '#E6F1FB', color: '#185FA5' },
  { key: 'contacted', label: 'Contacted', bg: '#FAEEDA', color: '#854F0B' },
  { key: 'qualified', label: 'Qualified', bg: '#EEEDFE', color: '#534AB7' },
  { key: 'lost',      label: 'Lost',      bg: '#FCEBEB', color: '#A32D2D' },
]

const kpis = computed(() => {
  if (!stats.value) return []
  return [
    {
      label: 'Total contacts',
      value: stats.value.contacts.total,
      sub:   `+${stats.value.contacts.this_month} this month`,
      color: '#534AB7',
    },
    {
      label: 'Open leads',
      value: stats.value.leads.total,
      sub:   '',
      color: '#0F6E56',
    },
    {
      label: 'Pipeline value',
      value: '₹' + formatValue(stats.value.deals.pipeline_value),
      sub:   `${stats.value.deals.total} open deals`,
      color: '#185FA5',
    },
    {
      label: 'Tasks overdue',
      value: stats.value.tasks.overdue,
      sub:   `${stats.value.tasks.due_today} due today`,
      color: stats.value.tasks.overdue > 0 ? '#A32D2D' : '#3B6D11',
    },
  ]
})

const taskStats = computed(() => {
  if (!stats.value) return []
  return [
    { label: 'Due today',  value: stats.value.tasks.due_today, color: '#BA7517' },
    { label: 'Overdue',    value: stats.value.tasks.overdue,   color: '#E24B4A' },
    { label: 'Pending',    value: stats.value.tasks.pending,   color: '#378ADD' },
  ]
})

const maxDealCount = computed(() => {
  if (!stats.value) return 1
  return Math.max(1, ...Object.values(stats.value.deals.by_stage))
})

const stageBarWidth = (key: string) => {
  const count = stats.value?.deals.by_stage[key] ?? 0
  return Math.round((count / maxDealCount.value) * 100)
}

const formatValue = (v?: number) =>
  v !== undefined ? new Intl.NumberFormat('en-IN').format(Math.round(v)) : '0'

const activityIcon = (type: string): string => {
  const m: Record<string, string> = {
    created: '+', updated: '~', note: 'N', call: 'C',
    email: 'E', meeting: 'M', task_completed: 'T',
    stage_changed: 'S', converted: '→',
  }
  return m[type] ?? '·'
}

const activityStyle = (type: string) => {
  const m: Record<string, { background: string; color: string }> = {
    created:       { background: '#E1F5EE', color: '#0F6E56' },
    updated:       { background: '#E6F1FB', color: '#185FA5' },
    note:          { background: '#F1EFE8', color: '#5F5E5A' },
    call:          { background: '#EEEDFE', color: '#534AB7' },
    email:         { background: '#E1F5EE', color: '#0F6E56' },
    meeting:       { background: '#FAEEDA', color: '#854F0B' },
    task_completed:{ background: '#EAF3DE', color: '#3B6D11' },
    stage_changed: { background: '#FAEEDA', color: '#854F0B' },
    converted:     { background: '#EEEDFE', color: '#534AB7' },
  }
  return m[type] ?? { background: '#F1EFE8', color: '#5F5E5A' }
}

onMounted(async () => {
  const res  = await api.get('/stats')
  stats.value = res.data.data
})
</script>
