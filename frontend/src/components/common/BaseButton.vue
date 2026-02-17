<template>
  <button :class="class" :type="type">
    <slot />
  </button>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  type?: string;
  class?: string;
}>(), {
  type: "submit",
  class: "w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors"
});
</script>
