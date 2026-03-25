<template>
  <BaseBreadCrump :items="breadcrumbItems" />

  <div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-6">
        {{ isEdit ? 'Edit Contact' : 'Create Contact' }}
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
            {{ isEdit ? 'Save Changes' : 'Create Contact' }}
          </BaseButton>
          <BaseButton
            type="button"
            @click="$router.push('/admin/contacts')"
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
import { reactive, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useToast } from 'vue-toastification'
import BaseBreadCrump from '@/components/common/BaseBreadCrump.vue'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseButton from '@/components/common/BaseButton.vue'
import { isRequired, isValidEmail } from '@/helpers/validators'
import { createContact, updateContact, fetchContact } from '@/services/api'

const router = useRouter()
const route  = useRoute()
const toast  = useToast()

const isEdit    = computed(() => !!route.params.id)
const contactId = computed(() => route.params.id as string)

const breadcrumbItems = computed(() => [
  { label: 'Contacts', link: '/admin/contacts' },
  { label: isEdit.value ? 'Edit Contact' : 'Create Contact' },
])

const form = reactive({
  name:        '',
  email:       '',
  phone:       '',
  company:     '',
  status:      'prospect',
  notes:       '',
  assigned_to: '',
})

const errors = reactive({
  name: '', email: '', phone: '', company: '',
  status: '', notes: '', assigned_to: '',
})

const fields = [
  { name: 'name',    label: 'Full Name',   type: 'text',   placeholder: 'Enter full name' },
  { name: 'email',   label: 'Email',       type: 'text',   placeholder: 'Enter email' },
  { name: 'phone',   label: 'Phone',       type: 'text',   placeholder: 'Enter phone number' },
  { name: 'company', label: 'Company',     type: 'text',   placeholder: 'Enter company name' },
  {
    name: 'status', label: 'Status', type: 'select',
    options: [
      { label: 'Prospect', value: 'prospect' },
      { label: 'Active',   value: 'active' },
      { label: 'Inactive', value: 'inactive' },
    ],
  },
  { name: 'notes', label: 'Notes', type: 'text', placeholder: 'Any notes...' },
]

const validate = () => {
  errors.name  = isRequired(form.name,  'Name')  || ''
  errors.email = isRequired(form.email, 'Email') || isValidEmail(form.email) || ''
  errors.status = isRequired(form.status, 'Status') || ''
}

watch(() => form.name,   () => { errors.name  = isRequired(form.name,  'Name')  || '' })
watch(() => form.email,  () => { errors.email = isRequired(form.email, 'Email') || isValidEmail(form.email) || '' })
watch(() => form.status, () => { errors.status = isRequired(form.status, 'Status') || '' })

const submitForm = async () => {
  validate()
  if (Object.values(errors).some(e => e)) return

  try {
    let res
    if (isEdit.value) {
      res = await updateContact(contactId.value, form)
    } else {
      res = await createContact(form)
    }
    toast.success(res.message)
    router.push('/admin/contacts')
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Something went wrong')
  }
}

onMounted(async () => {
  if (isEdit.value) {
    const contact = await fetchContact(contactId.value)
    Object.assign(form, {
      name:        contact.name,
      email:       contact.email,
      phone:       contact.phone        ?? '',
      company:     contact.company      ?? '',
      status:      contact.status,
      notes:       contact.notes        ?? '',
      assigned_to: contact.assigned_to?.id ?? '',
    })
  }
})
</script>