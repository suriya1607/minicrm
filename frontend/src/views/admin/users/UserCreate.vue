<template>
<BaseBreadCrump :items="breadcrumbItems" />

<div className="max-w-2xl mx-auto">

    <div className="bg-white rounded-lg shadow p-6">
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-xl font-semibold text-gray-800">{{ breadcrumbLabel }}</h2>
        </div>
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
                  :readonly="field.name  === 'email'&& isEmailVerified"

              />
          </div>
        
         <div class="flex gap-3 pt-4">
          <BaseButton :class="'bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors'">{{ t.button.submit }}</BaseButton>
          <BaseButton @click="$router.push('/admin/users')" :class="'bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors'" :type="'button'">{{ t.button.cancel }}</BaseButton>
        </div>
        </form>
    </div>
</div>

</template>

<script setup lang="ts">

import BaseBreadCrump from '@/components/common/BaseBreadCrump.vue';
import BaseInput from "@/components/common/BaseInput.vue";
import BaseButton from "@/components/common/BaseButton.vue";
import t from "@/lang/en";
import { watch, reactive,onMounted,ref,computed} from "vue";
import api from "@/api/axios";
import { isRequired, minLength, isValidEmail } from "@/helpers/validators";
import { createUser, updateUser ,fetchUser } from "@/services/api.ts";
import { useToast } from "vue-toastification";
import { useRouter,useRoute } from "vue-router";

const toast = useToast();
const router = useRouter();
const route = useRoute();

const breadcrumbLabel = computed(() => {
  return route.params.id ? t.auth.useredit : t.auth.usercreate
})
const userId = computed<string | undefined>(() => route.params.id as string);
const isEmailVerified = ref(false);
const breadcrumbItems = [
  { label: 'Users', link: '/admin/users' },
  { label: breadcrumbLabel.value }
]
const roles = ref([]);


const form = reactive({
  email: '',
  name: '',
  phone: '',
  role_id: ''
});

const errors = reactive({
  email: "",
  name: "",
  phone: "",
  role_id: ""
});

const fields = computed(() => [
  { name: 'email', label: t.auth.email, type: 'text', placeholder: 'Enter email'},
  { name: 'name', label: t.auth.name, type: 'text', placeholder: 'Enter name' },
  { name: 'phone', label: t.auth.phone, type: 'text', placeholder: 'Enter phone number' },
  { name: 'role_id', label: t.auth.role, type: 'select', placeholder: 'Select role', options: roles.value }
]);

onMounted(() => {
  fetchRoles();

  if (userId.value) {
    fetchUserData(userId.value);
  }
});
const validateEmail = () => {
  errors.email =
    isRequired(form.email,t.auth.email) ||
    isValidEmail(form.email) ||
    "";
};

const validateRole = () => {
  errors.role_id =
    isRequired(form.role_id,t.auth.role) ||
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
watch(() => form.role_id, validateRole);
watch(() => form.name, validateName);
watch(() => form.phone, validatePhone);

const submitForm = async () => {

  validateRole();
  validateEmail();
  validateName();
  validatePhone();

  if (Object.values(errors).some((error) => error)) {
    return;
  }
  
  try {

    let response;
    if (userId.value) {
      response = await updateUser(userId.value, form);
    } else {
      response = await createUser(form);
    }
    toast.success(response.message);
    router.push({name: 'admin-User'});

  } catch (error: any) {
   toast.error(error.response?.data?.message || "Something went wrong");
  }
  
};

const fetchRoles = async () => {
    const response = await api.get("/roles");
    roles.value = response.data.data.map((role: any) => ({
      label: role.name,
      value: role.id
    }))
    console.log("Roles:", roles.value);
}

const fetchUserData = async (id: any) => {
  try {
    const user = await fetchUser(`/users/${id}`);
    form.email = user.email;
    form.name = user.name;
    form.phone = user.phone;
    form.role_id = user.role?.id || '';
    isEmailVerified.value = !!user.is_verified;
    console.log("User data fetched:", user);
  } catch (error) {
    console.error("Error fetching user data:", error);
  }
}

</script>
