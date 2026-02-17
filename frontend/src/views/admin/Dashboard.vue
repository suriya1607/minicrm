<template>
    <!-- Dashboard content goes into the slot of AppLayout -->
    <div class="p-6">
      <h1 class="text-xl font-bold mb-4">Dashboard</h1>

      <p v-if="user">Welcome {{ user?.name }}</p>

      <button
        class="mt-4 px-4 py-2 bg-red-500 text-white rounded"
        @click="logout"
      >
        Logout
      </button>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted ,inject} from "vue";
import type { Ref } from "vue";
import api from "@/api/axios";
import { useRouter } from "vue-router";
import AppLayout from "@/components/layout/AppLayout.vue";

const router = useRouter();
const user = inject<Ref<User | null>>('user')
const loading = inject<Ref<boolean>>('loading',ref(false))

interface User {
  name: string
  email?: string
}

const logout = async () => {
  await api.post("/logout");
  localStorage.removeItem("auth_token");
  localStorage.removeItem("user");
  router.push("/login");
};

const fetchUserData = async () => {
  loading.value = true

  try {
    const response = await api.get("/me", {});
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchUserData()
});
</script>
