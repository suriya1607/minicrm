<template>
  <div class="auth-wrapper">
    <div class="card">
        <div class="flex items-center justify-center gap-2 mb-8">
          <div class="w-10 h-10 bg-gradient-to-br from-teal-400 to-blue-500 rounded-lg flex items-center justify-center" >
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M12 4L4 10v11l8-6 8 6V10l-8-6z" fill="white" />
            </svg>
          </div>
          <h1 class="text-2xl font-bold text-gray-800"> {{ t.common.appname }}</h1>
        </div>
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">  
import t from "@/lang/en";
</script>

<style scoped>
.auth-wrapper {
  min-height: 100vh;
  background: #2d3e50;
  display: flex;
  align-items: center;
  justify-content: center;
}
.card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  width: 450px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
</style>
