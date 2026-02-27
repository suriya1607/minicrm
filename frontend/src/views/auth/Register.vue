<template>
  <AuthLayout>
    <BaseLoader v-if="loading" />

    <div v-if="isRegistered" class="text-center p-6">
      <h2 class="text-xl font-bold text-green-600 mb-2">
        Registration Successful 
      </h2>

      <p class="text-gray-600">
        {{ "A verification link has been sent to your registered email." }}
      </p>

      <p class="mt-4 text-sm text-gray-500">
        Please check your inbox and verify your email before logging in.
      </p>
    </div>

    <form v-else @submit.prevent="register">

      <div class="mb-4">
      <BaseInput
        v-for="field in fields"
        :key="field.name"
        v-model="form[field.name]"
        :label="field.label"
        :type="field.type"
        :placeholder="field.placeholder"
        :error="errors[field.name]"
      />
      </div>

      <BaseButton>{{ t.auth.register }}</BaseButton>
    </form>
  <div class="mt-4 text-center text-sm">
    <router-link to="/login" class="text-blue-600 hover:text-blue-700">
      {{ t.auth.haveAccount }}
    </router-link>
  </div>
</AuthLayout>
</template>

<script setup lang="ts">
import { reactive, watch, computed, ref } from "vue";
import { useRouter } from "vue-router";
import api from "@/api/axios";
import t from "@/lang/en";

import AuthLayout from "@/components/layout/AuthLayout.vue";
import BaseInput from "@/components/common/BaseInput.vue";
import BaseButton from "@/components/common/BaseButton.vue";
import { useToast } from "vue-toastification";
import { isRequired, minLength, isValidEmail } from "@/helpers/validators";
import BaseLoader from "@/components/common/BaseLoader.vue";
const toast = useToast();


const router = useRouter();
const loading = ref(false);
const isRegistered = ref(false);


const form = reactive({
  name: "",
  email: "",
  phone: "",
  password: ""
});
const errors = reactive({
  email: "",
  name: "",
  phone: "",
  password: ""
});

const fields = [
  { name: 'email', label: t.auth.email, type: 'text', placeholder: 'Enter email' },
  { name: 'name', label: t.auth.name, type: 'text', placeholder: 'Enter name' },
  { name: 'phone', label: t.auth.phone, type: 'text', placeholder: 'Enter phone number' },
  { name: 'password', label: t.auth.password, type: 'password', placeholder: 'Enter password' },
];

const isFormValid = computed(() => {
  return  form.email && form.password && form.name && form.phone &&
          !errors.email && !errors.password && !errors.name && !errors.phone;
});

const validateEmail = () => {
  errors.email =
    isRequired(form.email,t.auth.email) ||
    isValidEmail(form.email) ||
    "";
};

const validatePassword = () => {
  errors.password =
    isRequired(form.password,t.auth.password) ||
    minLength(form.password, 6,t.auth.password) ||
    "";
};

const validateName = () => {
  errors.name =
    isRequired(form.name,t.auth.name) ||
    minLength(form.name, 3,t.auth.name) ||
    "";
};

const validatePhone = () => {
  errors.phone =
    isRequired(form.phone,t.auth.phone) ||
    minLength(form.phone, 10,t.auth.phone) ||
    "";
};

watch(() => form.email, validateEmail);
watch(() => form.password, validatePassword);
watch(() => form.name, validateName);
watch(() => form.phone, validatePhone);

const register = async () => {
  loading.value = true;
  validateEmail();
  validatePassword();
  validateName();
  validatePhone();
   try {
    const response = await api.post("/register", {
      email: form.email,
      password: form.password,
      name: form.name,
      phone: form.phone,
    });
    isRegistered.value = true;
  } catch (error: any) {
    toast.error('Something went wrong. Please try again.');
  } finally {
    loading.value = false;
  }
};
</script>
