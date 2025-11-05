<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Profile Settings</h1>
        <p class="mt-2 text-sm text-gray-600">Manage your account information and password</p>
      </div>

      <!-- Profile Information Card -->
      <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-5 border-b border-gray-200">
          <h2 class="text-lg font-medium text-gray-900">Profile Information</h2>
          <p class="mt-1 text-sm text-gray-500">Update your account's profile information.</p>
        </div>

        <form @submit.prevent="updateProfile" class="px-6 py-5">
          <div class="space-y-6">
            <!-- Name Field -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">
                Name
              </label>
              <div class="mt-1">
                <input
                  id="name"
                  v-model="profileForm.name"
                  type="text"
                  required
                  class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                  :class="{ 'border-red-300': errors.name }"
                />
                <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
              </div>
            </div>

            <!-- Email Field -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700">
                Email
              </label>
              <div class="mt-1">
                <input
                  id="email"
                  v-model="profileForm.email"
                  type="email"
                  required
                  class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                  :class="{ 'border-red-300': errors.email }"
                />
                <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
              <button
                type="submit"
                :disabled="saving || loading"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="saving">Saving...</span>
                <span v-else>Save Changes</span>
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Change Password Card -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-5 border-b border-gray-200">
          <h2 class="text-lg font-medium text-gray-900">Change Password</h2>
          <p class="mt-1 text-sm text-gray-500">Update your password to keep your account secure.</p>
        </div>

        <form @submit.prevent="changePassword" class="px-6 py-5">
          <div class="space-y-6">
            <!-- Current Password -->
            <div>
              <label for="current_password" class="block text-sm font-medium text-gray-700">
                Current Password
              </label>
              <div class="mt-1">
                <input
                  id="current_password"
                  v-model="passwordForm.current_password"
                  type="password"
                  required
                  class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                  :class="{ 'border-red-300': errors.current_password }"
                />
                <p v-if="errors.current_password" class="mt-1 text-sm text-red-600">{{ errors.current_password }}</p>
              </div>
            </div>

            <!-- New Password -->
            <div>
              <label for="new_password" class="block text-sm font-medium text-gray-700">
                New Password
              </label>
              <div class="mt-1">
                <input
                  id="new_password"
                  v-model="passwordForm.password"
                  type="password"
                  required
                  minlength="6"
                  class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                  :class="{ 'border-red-300': errors.password }"
                />
                <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
              </div>
            </div>

            <!-- Confirm New Password -->
            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                Confirm New Password
              </label>
              <div class="mt-1">
                <input
                  id="password_confirmation"
                  v-model="passwordForm.password_confirmation"
                  type="password"
                  required
                  minlength="6"
                  class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                  :class="{ 'border-red-300': errors.password_confirmation }"
                />
                <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ errors.password_confirmation }}</p>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
              <button
                type="submit"
                :disabled="changingPassword || loading"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="changingPassword">Changing...</span>
                <span v-else>Change Password</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '../../stores/authStore'
import { useToast } from 'vue-toastification'

export default {
  name: 'Profile',
  setup() {
    const authStore = useAuthStore()
    const toast = useToast()
    
    const loading = ref(false)
    const saving = ref(false)
    const changingPassword = ref(false)
    const errors = reactive({})

    const profileForm = reactive({
      name: '',
      email: ''
    })

    const passwordForm = reactive({
      current_password: '',
      password: '',
      password_confirmation: ''
    })

    // Load user data
    onMounted(() => {
      if (authStore.user) {
        profileForm.name = authStore.user.name || ''
        profileForm.email = authStore.user.email || ''
      }
    })

    const clearErrors = () => {
      Object.keys(errors).forEach(key => {
        errors[key] = ''
      })
    }

    const updateProfile = async () => {
      clearErrors()
      saving.value = true
      loading.value = true

      try {
        const result = await authStore.updateProfile({
          name: profileForm.name,
          email: profileForm.email
        })

        if (result.success) {
          toast.success('Profile updated successfully!')
        }
      } catch (error) {
        if (error.response?.data?.errors) {
          Object.keys(error.response.data.errors).forEach(key => {
            errors[key] = error.response.data.errors[key][0]
          })
        } else {
          toast.error(error.response?.data?.message || 'Failed to update profile')
        }
      } finally {
        saving.value = false
        loading.value = false
      }
    }

    const changePassword = async () => {
      clearErrors()

      // Validate password confirmation
      if (passwordForm.password !== passwordForm.password_confirmation) {
        errors.password_confirmation = 'Passwords do not match'
        return
      }

      if (passwordForm.password.length < 6) {
        errors.password = 'Password must be at least 6 characters'
        return
      }

      changingPassword.value = true
      loading.value = true

      try {
        const result = await authStore.changePassword({
          current_password: passwordForm.current_password,
          password: passwordForm.password,
          password_confirmation: passwordForm.password_confirmation
        })

        if (result.success) {
          toast.success('Password changed successfully!')
          // Clear password form
          passwordForm.current_password = ''
          passwordForm.password = ''
          passwordForm.password_confirmation = ''
        }
      } catch (error) {
        if (error.response?.data?.errors) {
          Object.keys(error.response.data.errors).forEach(key => {
            errors[key] = error.response.data.errors[key][0]
          })
        } else {
          toast.error(error.response?.data?.message || 'Failed to change password')
        }
      } finally {
        changingPassword.value = false
        loading.value = false
      }
    }

    return {
      profileForm,
      passwordForm,
      errors,
      loading,
      saving,
      changingPassword,
      updateProfile,
      changePassword
    }
  }
}
</script>

