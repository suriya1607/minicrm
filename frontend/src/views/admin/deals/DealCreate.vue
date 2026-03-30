<template>
  <BaseBreadCrump :items="breadcrumbItems" />

  <div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-6">
        {{ isEdit ? 'Edit Deal' : 'Create Deal' }}
      </h2>

      <form @submit.prevent="submitForm" class="space-y-4">
        <BaseInput
          v-for="field in fields"
          :key="field.name"
          v-model="form[field.name]"
          :label="field.label"
          :type="field.type"
          :placeholder="field.placeholder ?? ''"
          :options="field.options"
          :error="errors[field.name]"
        />

        <div class="flex gap-3 pt-4">
          <BaseButton :class="'bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700'">
            {{ isEdit ? 'Save Changes' : 'Create Deal' }}
          </BaseButton>
          <BaseButton
            type="button"
            @click="$router.push('/admin/deals')"
            :class="'bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300'"
          >
            Cancel
          </BaseButton>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useToast } from 'vue-toastification'
import BaseBreadCrump from '@/components/common/BaseBreadCrump.vue'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseButton from '@/components/common/BaseButton.vue'
import { isRequired } from '@/helpers/validators'
import { createDeal, updateDeal, fetchDeal, fetchUsers } from '@/services/api'
import api from '@/api/axios'

const router = useRouter()
const route  = useRoute()
const toast  = useToast()

const isEdit = computed(() => !!route.params.id)

const breadcrumbItems = computed(() => [
  { label: 'Deals', link: '/admin/deals' },
  { label: isEdit.value ? 'Edit Deal' : 'Create Deal' },
])

const contacts = reactive<{ label: string; value: number }[]>([])
const users    = reactive<{ label: string; value: number }[]>([])

const form = reactive<Record<string, any>>({
  contact_id:          route.query.contact_id ?? '',
  lead_id:             route.query.lead_id    ?? '',
  title:               '',
  value:               '',
  stage:               'proposal',
  expected_close_date: '',
  notes:               '',
  assigned_to:         '',
})

const errors = reactive<Record<string, string>>({
  contact_id: '', title: '', stage: '',
})

const fields = computed(() => [
  { name: 'contact_id', label: 'Contact', type: 'select', options: contacts },
  { name: 'title',  label: 'Deal Title',    type: 'text', placeholder: 'e.g. E-commerce website project' },
  { name: 'value',  label: 'Deal Value (₹)', type: 'text', placeholder: 'e.g. 150000' },
  {
    name: 'stage', label: 'Pipeline Stage', type: 'select',
    options: [
      { label: 'Proposal',    value: 'proposal' },
      { label: 'Negotiation', value: 'negotiation' },
      { label: 'Agreement',   value: 'agreement' },
      { label: 'Won',         value: 'won' },
      { label: 'Lost',        value: 'lost' },
    ],
  },
  { name: 'expected_close_date', label: 'Expected Close Date', type: 'date' },
  { name: 'assigned_to', label: 'Assign To', type: 'select', options: users },
  { name: 'notes', label: 'Notes', type: 'text', placeholder: 'Any notes...' },
])

const validate = () => {
  errors.contact_id = isRequired(form.contact_id, 'Contact') || ''
  errors.title      = isRequired(form.title, 'Title')         || ''
  errors.stage      = isRequired(form.stage, 'Stage')         || ''
}

const submitForm = async () => {
  validate()
  if (Object.values(errors).some(e => e)) return

  try {
    const res = isEdit.value
      ? await updateDeal(route.params.id as string, form)
      : await createDeal(form)
    toast.success(res.message)
    router.push('/admin/deals')
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Something went wrong')
  }
}

onMounted(async () => {
  const contactsRes = await api.get('/contacts', { params: { per_page: 100 } })
  contacts.splice(0, contacts.length,
    ...contactsRes.data.data.map((c: any) => ({ label: c.name, value: c.id }))
  )

  const usersData = await fetchUsers()
  users.splice(0, users.length, ...usersData)

  if (isEdit.value) {
    const deal = await fetchDeal(route.params.id as string)
    Object.assign(form, {
      contact_id:          deal.contact?.id    ?? '',
      lead_id:             deal.lead?.id       ?? '',
      title:               deal.title,
      value:               deal.value,
      stage:               deal.stage,
      expected_close_date: deal.expected_close_date ?? '',
      assigned_to:         deal.assigned_to?.id ?? '',
      notes:               deal.notes ?? '',
    })
  }
})
</script>