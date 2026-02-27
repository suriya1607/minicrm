<template>
  <AuthLayout>
    <BaseLoader v-if="loading" />

    <form @submit.prevent="handleSubmit">

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

      <!-- Remember Me -->
      <div class="flex items-center mb-6">
      <BaseInput
        v-model="rememberMe"
        label="Remember Me"
        type="checkbox"
        id="remember"
      />
      </div>
      <!-- Button -->
      <BaseButton>{{ t.auth.login }}</BaseButton>

      <!-- Links -->
      <div class="mt-4 text-center text-sm">
        <router-link to="/forgot" class="text-gray-600 hover:text-gray-800">
          {{t.auth.forgotPass}}
        </router-link>
        <span class="mx-2 text-gray-400">|</span>
        <router-link to="/register" class="text-blue-600 hover:text-blue-700">
          {{t.auth.noAccount}}
        </router-link>
      </div>
    </form>
  </AuthLayout>
</template>

<script setup lang="ts">
import { ref ,reactive, watch, computed , onMounted} from "vue";
import { useRoute,useRouter } from "vue-router";
import AuthLayout from "@/components/layout/AuthLayout.vue";
import t from "@/lang/en";
import BaseInput from "@/components/common/BaseInput.vue";
import BaseButton from "@/components/common/BaseButton.vue";
import { isRequired, minLength, isValidEmail } from "@/helpers/validators";
import BaseLoader from "@/components/common/BaseLoader.vue";
import api from "@/api/axios";
import { useToast } from "vue-toastification";



const router = useRouter();
const route = useRoute();
const toast = useToast();

const rememberMe = ref(false);
const errors = reactive({
  email: "",
  password: ""
});
const form = reactive({
  email: "",
  password: ""
});

const fields = [
  { name: 'email', label: t.auth.email, type: 'text', placeholder: 'Enter email' },
  { name: 'password', label: t.auth.password, type: 'password', placeholder: 'Enter password' },
];

const isFormValid = computed(() => {
  return !errors.email && !errors.password && form.email && form.password;
});

onMounted(() => {
  if (route.query.verified === "1") {
    toast.success("Email verified successfully. Please login.");
  }
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

watch(() => form.email, validateEmail);
watch(() => form.password, validatePassword);

const loading = ref(false);

const handleSubmit = async () => {
  validateEmail();
  validatePassword();

  if (!isFormValid.value) return;

  loading.value = true;
  try {
    const response = await api.post("/login", {
      email: form.email,
      password: form.password,
    });
    const { access_token, user } = response.data;

    localStorage.setItem("auth_token", access_token);
    localStorage.setItem("user", JSON.stringify(user));
    
    if(user.role_id == import.meta.env.VITE_ADMIN_ROLE){
      router.push("/admin/dashboard");
    } else {
      router.push("/user/dashboard");
    }

  } catch (error: any) {
      const message = error.response?.data?.message || "Login failed";
      toast.error(message);
  } finally {
    loading.value = false;
  }
};


</script>
