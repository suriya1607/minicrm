<template>
  <div class="form-group">
    <label v-if="type !== 'checkbox'" class="block text-sm font-medium text-gray-700 mb-2">
      {{ label }}
    </label>

    <div v-if="type === 'checkbox'" class="flex items-center gap-2">
      <input
        type="checkbox"
        :id="id"
        :checked="modelValue"
        @change="$emit('update:modelValue', ($event.target as HTMLInputElement).checked)"
        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
        
      />
      <label :for="id" class="text-sm text-gray-700">{{ label }}</label>
    </div>

        <!-- Select -->
    <select
      v-else-if="type === 'select'"
      :multiple="multiple"
      @change="onSelectChange"
      :value="modelValue"
      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
      :disabled="readonly"
    >
      <option value="" disabled>{{ placeholder || 'Select option' }}</option>
      <option
        v-for="opt in options"
        :key="opt.value"
        :value="opt.value"
      >
        {{ opt.label }}
      </option>
    </select>

    <input
      v-else
      :type="type"
      :placeholder="placeholder"
      :value="modelValue"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
      :readonly="readonly"
    />
      <p v-if="error" class="text-red-500 text-sm mt-1">{{ error }}</p>

  </div>

</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{
  modelValue: string | boolean | string[] | number;
  label: string;
  placeholder?: string;
  type?: string;
  id?: string;
  error?: string;
  readonly?: boolean;
  options?: Option[];
  multiple?: boolean;
}>(), {
  type: "text",
  readonly: false,
  options: () => [],
  multiple: false

});

interface Option {
  label: string
  value: string | number
}

const emit = defineEmits(["update:modelValue"]);

const onSelectChange = (event: Event) => {
  const target = event.target as HTMLSelectElement;

  if (props.multiple) {
    const values = Array.from(target.selectedOptions).map(o => o.value);
    emit("update:modelValue", values);
  } else {
    emit("update:modelValue", target.value);
  }
};
</script>

<style scoped>
.form-group {
  margin-bottom: 12px;
}
.label {
  display: block;
  margin-bottom: 5px;
  font-weight: 600;
}
.input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
}
</style>
