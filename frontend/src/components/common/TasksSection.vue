<template>
  <div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-800">Tasks</h3>
      <BaseButton
        type="button"
        @click="showForm = true"
        :class="'bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm'"
      >
        + Add Task
      </BaseButton>
    </div>

    <!-- Task list -->
    <div v-if="tasks.length" class="space-y-2">
      <div
        v-for="task in tasks"
        :key="task.id"
        :class="['flex items-start gap-3 p-3 rounded-lg border', task.status === 'completed' ? 'bg-gray-50 border-gray-100 opacity-70' : 'bg-white border-gray-200']"
      >
        <!-- Complete toggle -->
        <input
          type="checkbox"
          :checked="task.status === 'completed'"
          @change="toggleComplete(task)"
          class="mt-1 cursor-pointer"
        />

        <div class="flex-1 min-w-0">
          <p :class="['text-sm font-medium', task.status === 'completed' ? 'line-through text-gray-400' : 'text-gray-800']">
            {{ task.title }}
          </p>
          <div class="flex items-center gap-3 mt-1">
            <span :class="priorityClass(task.priority)" class="text-xs px-2 py-0.5 rounded-full font-medium capitalize">
              {{ task.priority }}
            </span>
            <span v-if="task.due_date" class="text-xs text-gray-400">Due: {{ task.due_date }}</span>
            <span v-if="task.assigned_to?.name" class="text-xs text-gray-400">{{ task.assigned_to.name }}</span>
          </div>
        </div>

        <button @click="deleteTaskItem(task.id)" class="text-gray-300 hover:text-red-400 text-sm ml-2">✕</button>
      </div>
    </div>

    <div v-else class="text-sm text-gray-400 text-center py-4">No tasks yet.</div>

    <!-- Inline add form -->
    <div v-if="showForm" class="mt-4 border-t pt-4 space-y-3">
      <BaseInput v-model="form.title"       label="Task title"  type="text"   placeholder="e.g. Follow up call" />
      <BaseInput v-model="form.due_date"    label="Due date"    type="date"   placeholder="" />
      <BaseInput v-model="form.priority"    label="Priority"    type="select"
        :options="[{label:'Low',value:'low'},{label:'Medium',value:'medium'},{label:'High',value:'high'}]"
      />
      <BaseInput v-model="form.assigned_to" label="Assign to"   type="select" :options="users" />

      <div class="flex gap-2">
        <BaseButton @click="submitTask" :class="'bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700'">
          Save
        </BaseButton>
        <BaseButton type="button" @click="showForm = false" :class="'bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm'">
          Cancel
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import BaseButton from '@/components/common/BaseButton.vue'
import BaseInput from '@/components/common/BaseInput.vue'
import { fetchTasks, createTask, updateTask, deleteTask, fetchUsers } from '@/services/api'

const props = defineProps<{
  subjectType: 'Contact' | 'Lead' | 'Deal'
  subjectId: number
}>()

const toast    = useToast()
const tasks    = ref<any[]>([])
const showForm = ref(false)
const users    = ref<{ label: string; value: number }[]>([])

const form = reactive({
  title:       '',
  due_date:    '',
  priority:    'medium',
  assigned_to: '',
})

const priorityClass = (p: string) => ({
  'bg-green-100 text-green-700':  p === 'low',
  'bg-yellow-100 text-yellow-700': p === 'medium',
  'bg-red-100 text-red-700':      p === 'high',
})

const loadTasks = async () => {
  const res = await fetchTasks({ for_type: props.subjectType, for_id: props.subjectId })
  tasks.value = res.data
}

const submitTask = async () => {
  if (!form.title) return
  try {
    await createTask({
      ...form,
      taskable_type: props.subjectType,
      taskable_id:   props.subjectId,
      status:        'pending',
    })
    toast.success('Task added')
    showForm.value = false
    form.title = ''; form.due_date = ''; form.priority = 'medium'; form.assigned_to = ''
    await loadTasks()
  } catch {
    toast.error('Failed to add task')
  }
}

const toggleComplete = async (task: any) => {
  const newStatus = task.status === 'completed' ? 'pending' : 'completed'
  try {
    await updateTask(task.id, { ...task, status: newStatus, taskable_type: props.subjectType })
    task.status = newStatus
  } catch {
    toast.error('Failed to update task')
  }
}

const deleteTaskItem = async (id: number) => {
  if (!confirm('Delete this task?')) return
  try {
    await deleteTask(id)
    tasks.value = tasks.value.filter(t => t.id !== id)
  } catch {
    toast.error('Failed to delete')
  }
}

onMounted(async () => {
  await loadTasks()
  users.value = await fetchUsers()
})
</script>