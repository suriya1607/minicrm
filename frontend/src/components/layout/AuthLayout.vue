<template>
  <div class="auth-wrapper">
    <div class="card">
      <slot />
    </div>
  </div>
</template>

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
