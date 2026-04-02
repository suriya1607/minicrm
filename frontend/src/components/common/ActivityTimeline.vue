<template>
  <div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-800">Activity Timeline</h3>
      <BaseButton
        type="button"
        @click="showLogForm = !showLogForm"
        :class="'bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 text-sm'"
      >
        + Log activity
      </BaseButton>
    </div>

    <!-- Log form -->
    <div v-if="showLogForm" class="mb-4 border rounded-lg p-4 bg-gray-50 space-y-3">
      <BaseInput v-model="logForm.type" label="Type" type="select"
        :options="[
          {label:'Note',value:'note'},{label:'Call',value:'call'},
          {label:'Email',value:'email'},{label:'Meeting',value:'meeting'}
        ]"
      />
      <BaseInput v-model="logForm.title"       label="Title"       type="text" placeholder="e.g. Called and discussed proposal" />
      <BaseInput v-model="logForm.description" label="Description" type="text" placeholder="Optional details..." />
      <div class="flex gap-2">
        <BaseButton @click="submitLog" :class="'bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700'">Log</BaseButton>
        <BaseButton type="button" @click="showLogForm = false" :class="'bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm'">Cancel</BaseButton>
      </div>
    </div>

    <!-- Timeline -->
    <div v-if="activities.length" class="relative">
      <div class="absolute left-3 top-0 bottom-0 w-px bg-gray-200"></div>
      <div class="space-y-4">
        <div v-for="activity in activities" :key="activity.id" class="flex gap-4 relative">
          <div :class="['w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 z-10 text-xs font-bold', typeStyle(activity.type).bg]">
            {{ typeStyle(activity.type).icon }}
          </div>
          <div class="flex-1 pb-4">
            <div class="flex items-start justify-between">
              <p class="text-sm font-medium text-gray-800">{{ activity.title }}</p>
              <span class="text-xs text-gray-400 ml-2 flex-shrink-0">{{ activity.created_at }}</span>
            </div>
            <p v-if="activity.description" class="text-xs text-gray-500 mt-0.5">{{ activity.description }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ activity.user?.name ?? 'System' }}</p>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-sm text-gray-400 text-center py-4">No activities yet.</div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import BaseButton from '@/components/common/BaseButton.vue'
import BaseInput from '@/components/common/BaseInput.vue'
import { fetchActivities, logActivity } from '@/services/api'

const props = defineProps<{
  subjectType: 'Contact' | 'Lead' | 'Deal'
  subjectId: number
}>()

const toast       = useToast()
const activities  = ref<any[]>([])
const showLogForm = ref(false)

const logForm = reactive({ type: 'note', title: '', description: '' })

const typeStyle = (type: string) => {
  const map: Record<string, { icon: string; bg: string }> = {
    created:       { icon: '+', bg: 'bg-green-100 text-green-700' },
    updated:       { icon: '~', bg: 'bg-blue-100 text-blue-700' },
    note:          { icon: 'N', bg: 'bg-gray-100 text-gray-600' },
    call:          { icon: 'C', bg: 'bg-purple-100 text-purple-700' },
    email:         { icon: 'E', bg: 'bg-teal-100 text-teal-700' },
    meeting:       { icon: 'M', bg: 'bg-amber-100 text-amber-700' },
    task_completed:{ icon: 'T', bg: 'bg-green-100 text-green-700' },
    stage_changed: { icon: 'S', bg: 'bg-amber-100 text-amber-700' },
    converted:     { icon: '→', bg: 'bg-purple-100 text-purple-700' },
  }
  return map[type] ?? { icon: '·', bg: 'bg-gray-100 text-gray-600' }
}

const loadActivities = async () => {
  const res = await fetchActivities({ for_type: props.subjectType, for_id: props.subjectId })
  activities.value = res.data
}

const submitLog = async () => {
  if (!logForm.title) return
  try {
    await logActivity({
      ...logForm,
      subject_type: props.subjectType,
      subject_id:   props.subjectId,
    })
    toast.success('Activity logged')
    showLogForm.value = false
    logForm.title = ''; logForm.description = ''
    await loadActivities()
  } catch {
    toast.error('Failed to log activity')
  }
}

onMounted(loadActivities)
</script>