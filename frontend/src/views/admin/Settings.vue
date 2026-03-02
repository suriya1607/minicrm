<template>
<div className="max-w-2xl mx-auto">

    <div className="bg-white rounded-lg shadow p-6">
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-xl font-semibold text-gray-800">Profile & Settings</h2>
          <button className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            Settings Password
          </button>
        </div>
        <!-- profile section  -->
      <!-- Profile Avatar -->
      <div class="flex flex-col items-center mb-8">
        <div class="relative">

          <!-- Avatar -->
          <div class="w-32 h-32 bg-gray-200 rounded-full overflow-hidden flex items-center justify-center">
            <img v-if="image" :src="image" class="w-full h-full object-cover" />
            <span v-else class="text-4xl font-semibold text-gray-600">JD</span>
          </div>

          <!-- Camera Button -->
          <button
            type="button"
            @click="openChooser"
            class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700"
          >
            <Camera class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Modal -->
      <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-96">

          <h3 class="text-lg font-semibold mb-4">Choose Image</h3>

          <video v-if="cameraOn" ref="video" autoplay playsinline class="w-full rounded mb-4"></video>

          <div class="flex gap-3 justify-center">

            <button
              v-if="!cameraOn"
              @click="startCamera"
              class="bg-blue-600 text-white px-4 py-2 rounded"
            >
              Open Camera
            </button>

            <button
              v-if="cameraOn"
              @click="takePhoto"
              class="bg-green-600 text-white px-4 py-2 rounded"
            >
              Take Photo
            </button>

            <button
              @click="openFilePicker"
              class="bg-gray-600 text-white px-4 py-2 rounded"
            >
              Upload
            </button>

            <button
              @click="closeModal"
              class="bg-red-600 text-white px-4 py-2 rounded"
            >
              Cancel
            </button>

          </div>
        </div>
      </div>

      <!-- Hidden file input -->
      <input
        ref="fileInput"
        type="file"
        accept="image/*"
        class="hidden"
        @change="onFileChange"
      />

      <canvas ref="canvas" class="hidden"></canvas>

        <div class="mb-4">
            <BaseInput
                v-for="field in fields"
                :key="field.name"
                v-model="form[field.name]"
                :label="field.label"
                :type="field.type"
                :placeholder="field.value"
                :readonly="field.name === 'email'"
            />
        </div>
         <div class="flex gap-3 pt-4">
          <BaseButton :class="'bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors'">{{ t.button.savechanges }}</BaseButton>
          <BaseButton :class="'bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors'" :type="'button'">{{ t.button.changepass }}</BaseButton>
        </div>
    </div>
</div>
</template>

<script setup lang="ts">
import type { Ref } from "vue";
import { inject, reactive, watchEffect, ref,onUnmounted, } from "vue";
import t from "@/lang/en";
import BaseInput from "@/components/common/BaseInput.vue";
import BaseButton from "@/components/common/BaseButton.vue";
import { Camera } from 'lucide-vue-next';
// --- User interface ---
interface User {
  name: string;
  email?: string;
  phone?: string;
}
const image = ref<string | null>(null);
const showModal = ref(false);
const cameraOn = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const video = ref<HTMLVideoElement | null>(null);
const canvas = ref<HTMLCanvasElement | null>(null);

// --- Inject user ---
const user = inject<Ref<User | null>>('user');

// --- Reactive form ---
const form = reactive({
  email: '',
  name: '',
  phone: ''
});

// --- Fields configuration ---
const fields = [
  { name: 'email', label: t.auth.email, type: 'text', placeholder: 'Enter email'},
  { name: 'name', label: t.auth.name, type: 'text', placeholder: 'Enter name' },
  { name: 'phone', label: t.auth.phone, type: 'text', placeholder: 'Enter phone number' },
];

// --- Keep form in sync with user ---
watchEffect(() => {
  if (user?.value) {
    form.email = user.value.email || '';
    form.name = user.value.name || '';
    form.phone = user.value.phone || '';
    console.log("User loaded in Settings:", user.value);
  }
});

let stream: MediaStream | null = null;

const openChooser = () => {
  showModal.value = true;
};

const closeModal = () => {
  stopCamera();
  showModal.value = false;
};

const openFilePicker = () => {
  fileInput.value?.click();
};

const startCamera = async () => {
  stream = await navigator.mediaDevices.getUserMedia({ video: true });
  cameraOn.value = true;
  if (video.value) video.value.srcObject = stream;
};

const stopCamera = () => {
  stream?.getTracks().forEach(track => track.stop());
  cameraOn.value = false;
};

const takePhoto = () => {
  if (!video.value || !canvas.value) return;

  canvas.value.width = video.value.videoWidth;
  canvas.value.height = video.value.videoHeight;

  const ctx = canvas.value.getContext("2d");
  if (!ctx) return;

  ctx.drawImage(video.value, 0, 0);
  image.value = canvas.value.toDataURL("image/png");

  closeModal();
};

const onFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (!file) return;

  image.value = URL.createObjectURL(file);
  closeModal();
};

onUnmounted(() => stopCamera());
</script>
