<template>
  <BaseBreadCrump :items="breadcrumbItems" />

  <div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-6">
        {{ isEdit ? 'Edit Lead' : 'Create Lead' }}
      </h2>

      <form @submit.prevent="submitForm">
        <div class="mb-4">
          <BaseInput
            v-for="field in fields"
            :key="field.name"
            v-model="form[field.name]"
            :label="field.label"
            :type="field.type"
            :placeholder="field.placeholder"
            :options="field.options"
            :error="errors[field.name]"
          />
        </div>

        <div class="flex gap-3 pt-4">
          <BaseButton :class="'bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700'">
            {{ isEdit ? 'Save Changes' : 'Create Lead' }}
          </BaseButton>
          <BaseButton
            type="button"
            @click="$router.push('/admin/leads')"
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
import { reactive, computed, onMounted, watch ,ref} from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useToast } from 'vue-toastification'
import BaseBreadCrump from '@/components/common/BaseBreadCrump.vue'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseButton from '@/components/common/BaseButton.vue'
import { isRequired } from '@/helpers/validators'
import { createLead, updateLead, fetchLead,fetchUsers} from '@/services/api'
import api from '@/api/axios'

const router = useRouter()
const route  = useRoute()
const toast  = useToast()

const isEdit  = computed(() => !!route.params.id)
const leadId  = computed(() => route.params.id as string)
const users = ref<{ label: string; value: number }[]>([])


const breadcrumbItems = computed(() => [
  { label: 'Leads', link: '/admin/leads' },
  { label: isEdit.value ? 'Edit Lead' : 'Create Lead' },
])

const contacts = reactive<{ label: string; value: number }[]>([])

const form = reactive({
  contact_id:  route.query.contact_id as string ?? '',
  title:       '',
  source:      'other',
  stage:       'new',
  notes:       '',
  assigned_to: '',
})

const errors = reactive({
  contact_id: '', title: '', source: '', stage: '',
})

const fields = computed(() => [
  {
    name: 'contact_id', label: 'Contact', type: 'select',
    options: contacts,
  },
  { name: 'title',  label: 'Lead Title', type: 'text', placeholder: 'e.g. E-commerce website project' },
  {
    name: 'source', label: 'Source', type: 'select',
    options: [
      { label: 'Website',  value: 'website' },
      { label: 'Referral', value: 'referral' },
      { label: 'Social',   value: 'social' },
      { label: 'Email',    value: 'email' },
      { label: 'Phone',    value: 'phone' },
      { label: 'Other',    value: 'other' },
    ],
  },
  { name: 'assigned_to', label: 'Assign To', type: 'select', options: users.value },
  {
    name: 'stage', label: 'Stage', type: 'select',
    options: [
      { label: 'New',       value: 'new' },
      { label: 'Contacted', value: 'contacted' },
      { label: 'Qualified', value: 'qualified' },
      { label: 'Lost',      value: 'lost' },
    ],
  },
  { name: 'notes', label: 'Notes', type: 'text', placeholder: 'Any notes...' },
])

const validate = () => {
  errors.contact_id = isRequired(form.contact_id, 'Contact') || ''
  errors.title      = isRequired(form.title, 'Title')         || ''
  errors.source     = isRequired(form.source, 'Source')       || ''
  errors.stage      = isRequired(form.stage, 'Stage')         || ''
}

watch(() => form.contact_id, () => { errors.contact_id = isRequired(form.contact_id, 'Contact') || '' })
watch(() => form.title,      () => { errors.title      = isRequired(form.title, 'Title')         || '' })

const submitForm = async () => {
  validate()
  if (Object.values(errors).some(e => e)) return

  try {
    const res = isEdit.value
      ? await updateLead(leadId.value, form)
      : await createLead(form)
    toast.success(res.message)
    router.push('/admin/leads')
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Something went wrong')
  }
}

const fetchContacts = async () => {
  const res = await api.get('/contacts', { params: { per_page: 100 } })
  contacts.splice(0, contacts.length,
    ...res.data.data.map((c: any) => ({ label: c.name, value: c.id }))
  )
}

onMounted(async () => {
  await fetchContacts()
  users.value = await fetchUsers()
  if (isEdit.value) {
    const lead = await fetchLead(leadId.value)
    Object.assign(form, {
      contact_id:  lead.contact?.id   ?? '',
      title:       lead.title,
      source:      lead.source,
      stage:       lead.stage,
      notes:       lead.notes         ?? '',
      assigned_to: lead.assigned_to?.id ?? '',
    })
  }
})
</script>