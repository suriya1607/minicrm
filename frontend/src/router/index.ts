import { createRouter, createWebHistory, type RouteRecordRaw } from "vue-router";
import Login from "@/views/auth/Login.vue";
import Register from "@/views/auth/Register.vue";
import Dashboard from "@/views/admin/Dashboard.vue";
import AppLayout from '@/components/layout/AppLayout.vue'
import Customers from '@/views/admin/Customers.vue'
import Settings from '@/views/admin/Settings.vue'
import Users from '@/views/admin/User.vue'
import UserCreate from '@/views/admin/users/UserCreate.vue'
// import UserDashboard from '@/views/user/UserDashboard.vue'
// import UserProfile from '@/views/user/UserProfile.vue'



const routes: Array<RouteRecordRaw> = [
  { path: "/", redirect: "/login" },
  { path: "/login", component: Login },
  { path: "/register", component: Register },
    // Admin routes
  {
    path: '/admin',
    component: AppLayout,
    meta: { requiresAuth: true, role: 1 },
    children: [
      { path: 'dashboard', component: Dashboard },
      { path: 'customers', component: Customers },
      { path: 'settings', component: Settings },
      { path: 'users', component: Users },
      { path: 'users/create', component: UserCreate },
    ]
  },

  // Normal user routes
  {
    path: '/user',
    component: AppLayout,
    meta: { requiresAuth: true, role: 2 },
    children: [
      { path: 'dashboard', component: Dashboard },
      { path: 'settings', component: Settings }
    ]
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});
router.beforeEach((to, _from, next) => {
  const token = localStorage.getItem("auth_token");
  const userStr = localStorage.getItem('user')
  const user = userStr ? JSON.parse(userStr) : null

  if (to.meta.requiresAuth && !token) return next('/login')
  // Role check
  if (to.meta.role && user?.role_id !== to.meta.role) return next('/login') // or redirect to 403 page
  next()

});

export default router;
