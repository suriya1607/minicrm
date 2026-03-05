<template>
  <BaseTable title="Users" :columns="columns" :rows="users"   :currentPage="page" :totalPages="totalPages"
  @page-change="fetchUserData" @per-page-change="changePerPage">
    <template #header-action>
        <BaseButton @click="$router.push('/admin/users/create')" :class="'bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors'">{{ t.button.adduser }}</BaseButton>
    </template>

  <template #filters>
  <BaseFilter
    :filters="filterConfig"
    @filter-change="applyFilters"
  />
</template>

<template #actions="{ row }">
  <BaseActionMenu
    :row="row"
    :actions="[
      { label: 'Edit', key: 'edit' , route: `/admin/users/${row.id}/edit`},
      { label: 'Delete', key: 'delete', danger: true }
    ]"
    @action="handleTableAction"
  />
</template>



  </BaseTable>
</template>

<script setup lang="ts">
import BaseTable from '@/components/common/BaseTable.vue'
import BaseButton from '@/components/common/BaseButton.vue'
import BaseActionMenu from '@/components/common/BaseActionMenu.vue'
import BaseFilter from '@/components/common/BaseFilter.vue'
import t from "@/lang/en";
import { ref,onMounted, onBeforeUnmount,inject,computed,watch} from 'vue'
import type { Ref } from 'vue'
import api from "@/api/axios";
import { useToast } from "vue-toastification";
import { deleteUser } from "@/services/api.ts";


interface User {
  id: number
  name: string
  email: string
  phone: string
  role: string
  created_at: string
}

const users = ref<User[]>([])

const page = ref(1)
const totalPages = ref(1)

const filters = ref({})

const roles = ref([]);
const toast = useToast();

const columns = [
  { key: 'id', label: 'ID' },
  { key: 'name', label: 'Name' },
  { key: 'email', label: 'Email' },
  { key: 'phone', label: 'Phone' },
  { key: 'role', label: 'Role' },
  { key: 'created_at', label: 'Created At' },
]

const filterConfig = computed(() => {
  return columns
    .filter(col => !['id'].includes(col.key)) 
    .map(col => {
      if (col.key === "role") {
        return {
          key: "role_id",
          label: "Role",
          type: "select",   // custom type
          multiple: false,
          options: roles.value
        }
      }

      if (col.key === "created_at") {
        return {
          key: col.key,
          label: col.label,
          type: "date"
        }
      }

      return {
        key: col.key,
        label: col.label,
        type: "text",
        placeholder: `Search ${col.label}...`
      }
    })
})

watch(filterConfig, (newConfig) => {
  const obj: any = {}
  newConfig.forEach(f => {
    if (f.multiple) {
      obj[f.key] = [] 
    } else {
      obj[f.key] = ""
    }
  })
  filters.value = obj
}, { immediate: true })

const applyFilters = (newFilters: any) => {
  filters.value = newFilters
  fetchUserData(1)
}

const openRow = ref<number | null>(null)
const loading = inject<Ref<boolean>>('loading', ref(false))

const perPage = ref(10)


const changePerPage = (value: number) => {
  perPage.value = value
  fetchUserData(1) // reset to page 1
}

const closeMenu = () => {
  openRow.value = null
}

const fetchUserData = async (pageNumber = 1)  => {
  loading.value = true
  try {
    const cleanFilters = Object.fromEntries(
      Object.entries(filters.value).filter(([_, v]) => {
        if (Array.isArray(v)) return v.length > 0
        return v !== "" && v !== null && v !== false
      })
    )
    const params = {
      page: pageNumber,
      per_page: perPage.value,
      filter: cleanFilters   
    }
    const response = await api.get("/users", { params })

      users.value = response.data.data.map((user: any): User => ({
        id: user.id,
        name: user.name,
        email: user.email,
        phone: user.phone,
        role: user.role?.name,
        created_at: user.created_at
      }))

    page.value = response.data.meta.current_page
    totalPages.value = response.data.meta.last_page

  } catch (error) {
    console.error("Error fetching users:", error);
  } finally {
    loading.value = false
  }
}

const fetchRoles = async () => {
  try {
    const response = await api.get("/roles");
    roles.value = response.data.data.map((role: any) => ({
      label: role.name,
      value: role.id
    }))
    console.log("Roles:", roles.value);

  } catch (error) {
    console.error("Error fetching roles:", error);
  }
}

const handleTableAction = async ({ action, row }: any) => {
  if (action === "delete") {
    if (!confirm("Are you sure you want to delete this user?")) {
      return
    }
  }

  try {
    const res = await deleteUser(row.id)
    toast.success(res.message)
    fetchUserData(page.value) // Refresh data after action
  } catch (error) {
    console.error(error)
    toast.error("Something went wrong")
  }
}

onMounted(() => {
  document.addEventListener('click', closeMenu);
  fetchUserData(1);
  fetchRoles()
})

onBeforeUnmount(() => {
  document.removeEventListener('click', closeMenu)
})
</script>

