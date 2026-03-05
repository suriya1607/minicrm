<template>
<div class="max-w-2xl mx-auto">

  <div class="bg-white rounded-lg shadow p-6">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-semibold text-gray-800">
        Profile & Settings
      </h2>

      <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
        Settings Password
      </button>
    </div>

    <!-- Avatar -->
    <div class="flex flex-col items-center mb-8">
      <div class="relative">

        <div class="w-32 h-32 bg-gray-200 rounded-full overflow-hidden flex items-center justify-center">
          <img
            v-if="image || user?.avatar"
            :src="image || user?.avatar"
            alt="avatar"
            class="w-full h-full object-cover"
          />

          <span
            v-else
            class="text-4xl font-semibold text-gray-600"
          >
            {{ user?.name ? user.name.slice(0,2).toUpperCase() : 'U' }}
          </span>
        </div>

          <BaseButton  type="button"  @click="openChooser" class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700">
             <Camera class="w-4 h-4"/>
          </BaseButton>
              <BaseButton
            v-if="image || user?.avatar"
            type="button"
            @click="removePhoto"
            class="absolute top-0 right-0 bg-gray-300 text-white p-1 rounded-full"
          >
            ✕
          </BaseButton>

      </div>
    </div>

    <!-- Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
    >
      <div class="bg-white p-6 rounded-lg w-96">

        <h3 class="text-lg font-semibold mb-4">
          Choose Image
        </h3>

        <video
          v-if="cameraOn"
          ref="video"
          autoplay
          muted
          playsinline
          class="w-full rounded mb-4"
        />

        <div class="flex gap-3 justify-center">

          <BaseButton  v-if="!cameraOn" @click="startCamera" class="bg-blue-600 text-white px-4 py-2 rounded">
            {{ t.button.opencam }}
          </BaseButton>
           <BaseButton v-if="cameraOn" @click="takePhoto" class="bg-green-600 text-white px-4 py-2 rounded">
            {{ t.button.takephoto }}
          </BaseButton>
           <BaseButton type="button" @click="openFilePicker" class="bg-gray-600 text-white px-4 py-2 rounded">
            {{ t.button.upload }}
          </BaseButton>
           <BaseButton type="button" @click="closeModal" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
            {{ t.button.cancel }}
          </BaseButton>

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

    <!-- Form -->
    <div class="mb-4">

      <BaseInput
        v-for="field in fields"
        :key="field.name"
        v-model="form[field.name]"
        :label="field.label"
        :type="field.type"
        :placeholder="field.placeholder"
        :readonly="field.name === 'email'"
      />

    </div>

    <div class="flex gap-3 pt-4">

      <BaseButton @click="submitProfile"
        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700"
      >
        {{ t.button.savechanges }}
      </BaseButton>

      <BaseButton
        type="button"
        class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300"
      >
        {{ t.button.changepass }}
      </BaseButton>

    </div>

  </div>

</div>
</template>

<script setup lang="ts">
import { ref, reactive, inject, watchEffect, nextTick, onUnmounted } from "vue"
import type { Ref } from "vue"

import BaseInput from "@/components/common/BaseInput.vue"
import BaseButton from "@/components/common/BaseButton.vue"
import {updateProfile} from "@/services/api.ts";

import {useToast} from "vue-toastification";
import { Camera } from "lucide-vue-next"

import t from "@/lang/en"

interface User {
  name: string
  email?: string
  phone?: string
  avatar?: string
}

const user = inject<Ref<User | null>>("user")

const image = ref<string | null>(null)

const toast = useToast()

const showModal = ref(false)
const cameraOn = ref(false)
const removeAvatar = ref(false)


const fileInput = ref<HTMLInputElement | null>(null)
const video = ref<HTMLVideoElement | null>(null)
const canvas = ref<HTMLCanvasElement | null>(null)

let stream: MediaStream | null = null

const form = reactive({
  email: "",
  name: "",
  phone: ""
})

const fields = [
  { name: "email", label: t.auth.email, type: "text", placeholder: "Enter email" },
  { name: "name", label: t.auth.name, type: "text", placeholder: "Enter name" },
  { name: "phone", label: t.auth.phone, type: "text", placeholder: "Enter phone number" }
]


const removePhoto = () => {
  image.value = null

  if (user?.value) {
    user.value.avatar = ""
  }

  removeAvatar.value = true
}

watchEffect(() => {
  if (user?.value) {
    form.email = user.value.email || ""
    form.name = user.value.name || ""
    form.phone = user.value.phone || ""
  }
})

const openChooser = () => {
  showModal.value = true
}

const closeModal = () => {
  stopCamera()
  showModal.value = false
}

const openFilePicker = () => {
  fileInput.value?.click()
}

const startCamera = async () => {
  try {

    stream = await navigator.mediaDevices.getUserMedia({
      video: true,
      audio: false
    })

    cameraOn.value = true

    await nextTick()

    if (video.value) {
      video.value.srcObject = stream
      await video.value.play()
    }

  } catch (error) {

    console.error("Camera error:", error)
    alert("Unable to access camera")

  }
}

const stopCamera = () => {
  stream?.getTracks().forEach(track => track.stop())
  stream = null
  cameraOn.value = false
}

const takePhoto = () => {

  if (!video.value || !canvas.value) return

  canvas.value.width = video.value.videoWidth
  canvas.value.height = video.value.videoHeight

  const ctx = canvas.value.getContext("2d")
  if (!ctx) return

  ctx.drawImage(video.value, 0, 0)

  image.value = canvas.value.toDataURL("image/png")

  closeModal()

}

const onFileChange = (event: Event) => {

  const target = event.target as HTMLInputElement

  const file = target.files?.[0]

  if (!file) return

  image.value = URL.createObjectURL(file)

  closeModal()

}

const submitProfile = async () => {

  const formData = new FormData()
  formData.append("name", form.name)
  formData.append("phone", form.phone)

  if (image.value) {

    const blob = await fetch(image.value).then(r => r.blob())
    formData.append("image", blob, "avatar.png")

  }
  
  if (removeAvatar.value) {
    formData.append("remove_avatar", "1")
  }

  try {
    const res = await updateProfile(formData)
    toast.success(res.message);
    console.log("Profile updated:", res)
    localStorage.setItem("user", JSON.stringify(res.user))
    if (user) {
      user.value = res.user
    }

  } catch (error) {
    console.error(error)
  } 

}

onUnmounted(() => stopCamera())
</script>
