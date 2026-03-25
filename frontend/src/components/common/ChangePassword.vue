<template>
    <div class="mb-4">
        <BaseInput
            v-for="field in fields"
            :key="field.name"
            v-model="form[field.name]"
            :label="field.label"
            :type="field.type"
            :placeholder="field.placeholder"
            :error="errors[field.name]"
        />
    </div>

    <div class="flex gap-3 pt-4">
        <BaseButton @click="submitPassword" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                {{ t.button.updatepass }}
        </BaseButton>

        <BaseButton @click="$emit('cancel')" type="button" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">
            {{ t.button.cancel }}
        </BaseButton>
    </div>
</template>

<script setup lang="ts">
import { reactive,watch } from "vue"
import BaseInput from "@/components/common/BaseInput.vue"
import BaseButton from "@/components/common/BaseButton.vue"
import t from "@/lang/en"
import { isRequired, minLength } from "@/helpers/validators";
import {updatePassword} from "@/services/api.ts";
import { useToast } from "vue-toastification";

const toast = useToast();

const form = reactive({
  oldpassword: "",
  newpassword: "",
  confirmpassword: ""
})

const errors = reactive({
  oldpassword: "",
  newpassword: "",
  confirmpassword: ""
});

const fields = [
  { name: "oldpassword", label: t.common.oldpass, type: "text", placeholder: "Enter old password" },
  { name: "newpassword", label: t.common.newpass, type: "text", placeholder: "Enter new password" },
  { name: "confirmpassword", label: t.common.confirmpass, type: "text", placeholder: "Confirm new password" }
]
const emit = defineEmits(['cancel']);


const validatePassword = () => {
  errors.oldpassword =
    isRequired(form.oldpassword, t.common.oldpass) ||
    minLength(form.oldpassword, 6, t.common.oldpass) ||
    "";
  errors.newpassword =
    isRequired(form.newpassword, t.common.newpass) ||
    minLength(form.newpassword, 6, t.common.newpass) ||
    "";
  errors.confirmpassword =
    isRequired(form.confirmpassword, t.common.confirmpass) ||
    minLength(form.confirmpassword, 6, t.common.confirmpass) ||
    (form.newpassword == form.confirmpassword ? "" : "Passwords do not match");
};

watch(() => form.oldpassword, validatePassword);
watch(() => form.newpassword, validatePassword);
watch(() => form.confirmpassword, validatePassword);
const submitPassword = async () => {
    validatePassword();
    if (errors.oldpassword || errors.newpassword || errors.confirmpassword) return;
        const data = {
            current_password: form.oldpassword,
            new_password: form.newpassword,
            new_password_confirmation: form.confirmpassword
        };
    try {
        const res = await updatePassword(data);
        toast.success(res.message);
        emit('cancel');
    } catch (error: any) {
        // console.error(error);
                toast.error(
            error?.response?.data?.message || "Failed to update password"
        );
        // toast.error("Failed to update password");
    }
}
</script>