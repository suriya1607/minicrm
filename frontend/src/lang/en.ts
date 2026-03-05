import Setpassword from "@/views/auth/Setpassword.vue";
import { Upload } from "lucide-vue-next";

export default {
  common: {
    appname: "Fi CRM",
    loading: "Loading...",
    error: "Something went wrong.",
    success: "Operation successful."
  },
  button: {
    submit: "Submit",
    cancel: "Cancel",
    logout: "Logout",
    savechanges: "Save Changes",
    changepass: "Change Password",
    adduser: "Add User",
    addcustomer: "Add Customer",
    opencam: "Open Camera",
    takephoto: "Take Photo",
    upload: "Upload"
  },
  auth: {
    login: "Login",
    register: "Register",
    setpassword: "Set Password",
    name: "Full Name",
    phone: "Phone Number",
    email: "Email",
    password: "Password",
    confirm_password: "Confirm Password",
    haveAccount: "Already have an account?",
    noAccount: "Don't have an account?",
    forgotPass: "Forgot your password?",
    usercreate: "Create User",
    useredit: "Edit User",
    role: "Role"
  },
  messages: {
    loginError: "Invalid email or password",
    registerSuccess: "Registration successful"
  }
};
