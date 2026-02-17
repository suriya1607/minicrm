<template>
<div className="max-w-2xl mx-auto">

    <div className="bg-white rounded-lg shadow p-6">
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-xl font-semibold text-gray-800">Profile & Settings</h2>
          <button className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            Settings Password
          </button>
        </div>
        <div class="mb-4">
            <BaseInput
                v-for="field in fields"
                :key="field.name"
                v-model="form[field.name]"
                :label="field.label"
                :type="field.type"
                :placeholder="field.value"
                :readonly="field.name === 'email'"
            />
        </div>
         <div class="flex gap-3 pt-4">
          <BaseButton :class="'bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors'">{{ t.button.savechanges }}</BaseButton>
          <BaseButton :class="'bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors'" :type="'button'">{{ t.button.changepass }}</BaseButton>
        </div>
    </div>
</div>
</template>

<script setup lang="ts">
import type { Ref } from "vue";
import { inject, reactive, watchEffect } from "vue";
import t from "@/lang/en";
import BaseInput from "@/components/common/BaseInput.vue";
import BaseButton from "@/components/common/BaseButton.vue";

// --- User interface ---
interface User {
  name: string;
  email?: string;
  phone?: string;
}

// --- Inject user ---
const user = inject<Ref<User | null>>('user');

// --- Reactive form ---
const form = reactive({
  email: '',
  name: '',
  phone: ''
});

// --- Fields configuration ---
const fields = [
  { name: 'email', label: t.auth.email, type: 'text', placeholder: 'Enter email'},
  { name: 'name', label: t.auth.name, type: 'text', placeholder: 'Enter name' },
  { name: 'phone', label: t.auth.phone, type: 'text', placeholder: 'Enter phone number' },
];

// --- Keep form in sync with user ---
watchEffect(() => {
  if (user?.value) {
    form.email = user.value.email || '';
    form.name = user.value.name || '';
    form.phone = user.value.phone || '';
    console.log("User loaded in Settings:", user.value);
  }
});
</script>
