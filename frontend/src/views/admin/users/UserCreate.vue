<template>
<BaseBreadCrump :items="breadcrumbItems" />

<div className="max-w-2xl mx-auto">

    <div className="bg-white rounded-lg shadow p-6">
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-xl font-semibold text-gray-800">{{ t.auth.usercreate }}</h2>
        </div>
        <div class="mb-4">
            <BaseInput
                v-for="field in fields"
                :key="field.name"
                v-model="form[field.name]"
                :label="field.label"
                :type="field.type"
                :placeholder="field.value"
            />
        </div>
         <div class="flex gap-3 pt-4">
          <BaseButton :class="'bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors'">{{ t.button.submit }}</BaseButton>
          <BaseButton :class="'bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors'" :type="'button'">{{ t.button.cancel }}</BaseButton>
        </div>
    </div>
</div>

</template>

<script setup type="ts">

import BaseBreadCrump from '@/components/common/BaseBreadCrump.vue';
import BaseInput from "@/components/common/BaseInput.vue";
import BaseButton from "@/components/common/BaseButton.vue";
import t from "@/lang/en";
import { inject, reactive } from "vue";

const breadcrumbItems = [
  { label: 'Users', link: '/admin/users' },
  { label: 'Create User' }
]

const form = reactive({
  email: '',
  name: '',
  phone: ''
});

const fields = [
  { name: 'email', label: t.auth.email, type: 'text', placeholder: 'Enter email'},
  { name: 'name', label: t.auth.name, type: 'text', placeholder: 'Enter name' },
  { name: 'phone', label: t.auth.phone, type: 'text', placeholder: 'Enter phone number' },
];

</script>
