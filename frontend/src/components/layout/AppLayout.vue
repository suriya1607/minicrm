<template>
  <div class="flex h-screen bg-[#2d3e50]">
    <!-- Sidebar -->
    <aside class="w-64 bg-[#34495e] text-white flex flex-col">
      <div class="p-6 flex items-center gap-2">
        <div class="w-8 h-8 bg-gradient-to-br from-teal-400 to-blue-500 rounded-lg flex items-center justify-center">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M10 3L3 8v9l7-5 7 5V8l-7-5z" fill="white"/>
          </svg>
        </div>
        <span class="text-xl font-semibold">{{ t.common.appname }}</span>
      </div>

      <nav class="flex-1 px-3">
        <RouterLink
          v-for="item in navItems"
          :key="item.path"
          :to="item.path"
          :class="[
            'flex items-center gap-3 px-4 py-3 mb-1 rounded-lg transition-colors',
            $route.path === item.path
              ? 'bg-blue-600 text-white'
              : 'text-gray-300 hover:bg-[#2d3e50]'
          ]"
        >
          <component :is="item.icon" class="w-5 h-5" />
          <span>{{ item.label }}</span>
        </RouterLink>
      </nav>
    </aside>

    <BaseLoader v-if="loading" />


    <!-- Main content -->
    <main class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-800"> Welcome, {{ user?.name || 'Admin' }}</h1>
        <div class="flex items-center gap-4">
          <button class="relative p-2 hover:bg-gray-100 rounded-full">
            <Bell class="w-5 h-5 text-gray-600" />
            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
          </button>
          <button class="p-2 hover:bg-gray-100 rounded-full">
            <MessageCircle class="w-5 h-5 text-gray-600" />
          </button>
          <button class="p-2 hover:bg-gray-100 rounded-full">
            <RefreshCw class="w-5 h-5 text-gray-600" />
          </button>
          <!-- <div class="w-9 h-9 bg-gray-300 rounded-full flex items-center justify-center">
            <img
              v-if="user?.avatar"
              :src="user.avatar"
              alt="avatar"
              class="w-full h-full object-cover"
            />

          <span
            v-else
            class="text-sm font-semibold text-gray-700"
          >
            {{ user?.name ? user.name.slice(0,2).toUpperCase() : 'U' }}
          </span>
          </div> -->
          <div class="relative">
  <button
    @click="toggleProfileMenu"
    class="w-9 h-9 bg-gray-300 rounded-full flex items-center justify-center overflow-hidden"
  >
    <img
      v-if="user?.avatar"
      :src="user.avatar"
      alt="avatar"
      class="w-full h-full object-cover"
    />

    <span
      v-else
      class="text-sm font-semibold text-gray-700"
    >
      {{ user?.name ? user.name.slice(0,2).toUpperCase() : 'U' }}
    </span>
  </button>

  <!-- Dropdown -->
  <div
    v-if="showProfileMenu"
    class="absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg border z-50"
  >
    <RouterLink
      to="/admin/settings"
      class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
      @click="showProfileMenu = false"
    >
      My Profile
    </RouterLink>

    <button
      @click="logout"
      class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
    >
      Logout
    </button>
  </div>
</div>
          
        </div>
      </header>

      <!-- Content area -->
      <div class="flex-1 overflow-auto bg-gray-50 p-6">
        <slot /> 
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref,onMounted,provide,computed,onUnmounted} from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import t from "@/lang/en";
import BaseLoader from "@/components/common/BaseLoader.vue";
import { useRouter } from 'vue-router'
import api from '@/api/axios'

// Import icons from Lucide Vue (same as React)
import { LayoutDashboard, Users, FileText, Calendar, BarChart3, BookUser, TrendingUp, Settings, Bell, MessageCircle, RefreshCw, Handshake,CheckSquare } from 'lucide-vue-next'

interface User {
  name: string
  email?: string
  role_id?: number
  avatar?: string
  // add other fields if needed
}
const loading = ref(false)
const route = useRoute()
const router = useRouter()
const adminRole = import.meta.env.VITE_ADMIN_ROLE;
const userRole = import.meta.env.VITE_USER_ROLE;
const managerRole = import.meta.env.VITE_MANAGER_ROLE;
const user = ref<User | null>(null)
const showProfileMenu = ref(false)


const adminNav = [
  { path: '/admin/dashboard',icon: LayoutDashboard, label: 'Dashboard' },
  { path: '/admin/users', icon: Users, label: 'Users' },
  { path: '/admin/contacts', icon: BookUser, label: 'Contacts' },
  { path: '/admin/leads',    icon: TrendingUp,  label: 'Leads' },
  { path: '/admin/deals',    icon: Handshake,  label: 'Deals' },
  { path: '/admin/tasks', icon: CheckSquare, label: 'Tasks' },
  { path: '/admin/settings', icon: Settings, label: 'Settings' },
];

const userNav = [
  { path: '/user/dashboard', icon: LayoutDashboard, label: 'Dashboard' },
  { path: '/user/settings', icon: Settings, label: 'Settings' },
];

const navItems = computed(() => {
  if (!user.value) return []

  if (user.value.role_id == adminRole) return adminNav
  if (user.value.role_id == userRole) return userNav
  if (user.value.role_id === managerRole) return adminNav

  return []
})

const toggleProfileMenu = () => {
  showProfileMenu.value = !showProfileMenu.value
}

const logout = async () => {
  await api.post("/logout");
  localStorage.removeItem("auth_token");
  localStorage.removeItem("user");
  router.push("/login");
};

const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  if (!target.closest('.relative')) {
    showProfileMenu.value = false
  }
}

provide('user', user)
provide('loading', loading)

onMounted(() => {
    document.addEventListener('click', handleClickOutside)

  const storedUser = localStorage.getItem('user')
  console.log('AppLayout mounted, storedUser:', storedUser)
  if (storedUser) {
    try {
      user.value = JSON.parse(storedUser)
    } catch (err) {
      console.error('Failed to parse user from localStorage')
    }
  }
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

</script>
