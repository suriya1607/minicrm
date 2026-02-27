<template>
  <AuthLayout>

        <form @submit.prevent="handleSubmit">

      <div class="mb-4">
      <BaseInput
        v-for="field in fields"
        :key="field.name"
        v-model="form[field.name]"
        :label="field.label"
        :type=passwordType
        :placeholder="field.placeholder"
        :error="errors[field.name]"
      />
      </div>

    <div class="flex items-center mb-6">
      <BaseInput
        v-model="showPassword"
        label="Show Password"
        type="checkbox"
        id="showPassword"
      />
    </div>

      <!-- Button -->
      <BaseButton>{{ t.auth.setpassword }}</BaseButton>

    </form>

  </AuthLayout>
</template>

<script setup lang="ts">

import AuthLayout from "@/components/layout/AuthLayout.vue";
import t from "@/lang/en";
import BaseInput from "@/components/common/BaseInput.vue";
import BaseButton from "@/components/common/BaseButton.vue";
import { isRequired, minLength } from "@/helpers/validators";
import api from "@/api/axios";
import {reactive, watch,ref,computed} from "vue";
import { setPassword } from "@/services/api.ts";
import { useToast } from "vue-toastification";
import { useRouter, useRoute } from "vue-router";

const form = reactive({
  password: "",
  confirm_password: ""
});

const toast = useToast();
const router = useRouter();
const route = useRoute();

const errors = reactive({
  password: "",
  confirm_password: ""
});

const showPassword = ref(false);

const passwordType = computed(() => showPassword.value ? "text" : "password");



const validatePassword = () => {
  errors.password =
    isRequired(form.password, t.auth.password) ||
    minLength(form.password, 6, t.auth.password) ||
    "";
  errors.confirm_password =
    isRequired(form.confirm_password, t.auth.confirm_password) ||
    minLength(form.confirm_password, 6, t.auth.confirm_password) ||
    (form.password == form.confirm_password ? "" : "Passwords do not match");
};

watch(() => form.password, validatePassword);
watch(() => form.confirm_password, validatePassword);

const fields = [
  {
    name: "password",
    label: "New Password",
    placeholder: "Enter new password"
  },
  {
    name: "confirm_password",
    label: "Confirm Password",
    placeholder: "Confirm new password"
  }
];

const handleSubmit = async () => {
  validatePassword();
  if (errors.password || errors.confirm_password) return;
    const token = route.query.token;
    const data = {
        password: form.password,      
        password_confirmation: form.confirm_password,
        token: token        
    };

    try {
    const response = await setPassword('/set-password', data);
    toast.success(response.message);
    router.push("/login");
  } catch (error: any) {
   toast.error(error.response?.data?.message || "Something went wrong");
  }

};
</script>