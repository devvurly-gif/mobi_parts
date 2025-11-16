<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Users</h1>
            <p class="mt-1 text-sm text-gray-600">Manage system users and access.</p>
          </div>
          <button 
            @click="openAddModal"
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200"
          >
            Add User
          </button>
        </div>
      </div>
    </div>

    <!-- Search -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
        <input
          id="search"
          v-model="filters.search"
          type="text"
          placeholder="Search by name or email..."
          class="mt-1 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
        />
      </div>

      <!-- Users Table -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div v-if="loading" class="p-6 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <p class="mt-2 text-gray-600">Loading users...</p>
        </div>

        <div v-else-if="filteredUsers.length === 0" class="p-6 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No users found</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by creating a new user.</p>
        </div>

        <ul v-else class="divide-y divide-gray-200">
          <li v-for="user in filteredUsers" :key="user.id" class="px-6 py-4 hover:bg-gray-50">
            <div class="flex items-center justify-between">
              <div class="flex items-center flex-1">
                <div class="shrink-0">
                  <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                    <span class="text-indigo-600 font-medium text-lg">{{ userInitials(user) }}</span>
                  </div>
                </div>
                <div class="ml-4 flex-1">
                  <div class="flex items-center">
                    <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                    <span v-if="user.id === currentUserId" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      You
                    </span>
                  </div>
                  <p class="text-sm text-gray-500">{{ user.email }}</p>
                  <p v-if="user.email_verified_at" class="mt-1 text-xs text-gray-400">
                    Verified {{ formatDate(user.email_verified_at) }}
                  </p>
                  <p v-else class="mt-1 text-xs text-orange-400">
                    Not verified
                  </p>
                </div>
              </div>
              <div class="flex items-center space-x-3">
                <button
                  @click="editUser(user)"
                  class="inline-flex items-center justify-center text-indigo-600 hover:bg-indigo-600 hover:text-white hover:shadow-md hover:scale-105 transition-all duration-200 p-2 rounded-md"
                  title="Edit user"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>
                <button
                  v-if="user.id !== currentUserId"
                  @click="deleteUser(user)"
                  class="inline-flex items-center justify-center text-red-600 hover:bg-red-600 hover:text-white hover:shadow-md hover:scale-105 transition-all duration-200 p-2 rounded-md"
                  title="Delete user"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
                <span v-else class="text-gray-400 text-sm">Cannot delete</span>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <!-- Add/Edit User Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 overflow-y-auto bg-gray-500"
    >
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                {{ editingUser ? 'Edit User' : 'Add User' }}
              </h3>
              <button
                @click="closeModal"
                class="text-gray-400 hover:text-gray-500"
              >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>

            <form @submit.prevent="saveUser">
              <div class="space-y-4">
                <!-- Name -->
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">
                    Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    class="mt-1 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    :class="{ 'border-red-300': errors.name }"
                  />
                  <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                </div>

                <!-- Email -->
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700">
                    Email <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    class="mt-1 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    :class="{ 'border-red-300': errors.email }"
                  />
                  <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                </div>

                <!-- Password -->
                <div>
                  <label for="password" class="block text-sm font-medium text-gray-700">
                    Password <span v-if="!editingUser" class="text-red-500">*</span>
                    <span v-else class="text-gray-500 text-xs">(leave blank to keep current password)</span>
                  </label>
                  <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    :required="!editingUser"
                    :minlength="editingUser ? 0 : 6"
                    class="mt-1 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    :class="{ 'border-red-300': errors.password }"
                  />
                  <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
                </div>

                <!-- Password Confirmation -->
                <div v-if="form.password || !editingUser">
                  <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                    Confirm Password <span v-if="!editingUser" class="text-red-500">*</span>
                  </label>
                  <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    :required="!editingUser || !!form.password"
                    :minlength="editingUser && form.password ? 6 : (editingUser ? 0 : 6)"
                    class="mt-1 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    :class="{ 'border-red-300': errors.password_confirmation }"
                  />
                  <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ errors.password_confirmation }}</p>
                </div>
              </div>

              <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button
                  type="submit"
                  :disabled="saving"
                  class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="saving">Saving...</span>
                  <span v-else>{{ editingUser ? 'Update' : 'Create' }}</span>
                </button>
                <button
                  type="button"
                  @click="closeModal"
                  class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { useUserStore } from '../../stores/userStore'
import { useAuthStore } from '../../stores/authStore'
import { useToast } from 'vue-toastification'

export default {
  name: 'Users',
  setup() {
    const userStore = useUserStore()
    const authStore = useAuthStore()
    const toast = useToast()

    const showModal = ref(false)
    const editingUser = ref(null)
    const saving = ref(false)
    const errors = reactive({})

    const filters = reactive({
      search: ''
    })

    const form = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: ''
    })

    const loading = computed(() => userStore.loading)
    const users = computed(() => userStore.users)
    const currentUserId = computed(() => authStore.user?.id)

    const filteredUsers = computed(() => {
      let filtered = users.value

      // Search filter
      if (filters.search) {
        const search = filters.search.toLowerCase()
        filtered = filtered.filter(user => 
          user.name.toLowerCase().includes(search) ||
          user.email.toLowerCase().includes(search)
        )
      }

      return filtered
    })

    const userInitials = (user) => {
      if (!user.name) return 'U'
      return user.name
        .split(' ')
        .map(name => name[0])
        .join('')
        .toUpperCase()
        .slice(0, 2)
    }

    const formatDate = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString()
    }

    const clearErrors = () => {
      Object.keys(errors).forEach(key => {
        errors[key] = ''
      })
    }

    const resetForm = () => {
      form.name = ''
      form.email = ''
      form.password = ''
      form.password_confirmation = ''
      clearErrors()
    }

    const openAddModal = () => {
      editingUser.value = null
      resetForm()
      showModal.value = true
    }

    const editUser = (user) => {
      editingUser.value = user
      form.name = user.name || ''
      form.email = user.email || ''
      form.password = ''
      form.password_confirmation = ''
      clearErrors()
      showModal.value = true
    }

    const closeModal = () => {
      showModal.value = false
      editingUser.value = null
      resetForm()
    }

    const saveUser = async () => {
      clearErrors()

      // Validate password confirmation
      if (form.password && form.password !== form.password_confirmation) {
        errors.password_confirmation = 'Passwords do not match'
        return
      }

      if (!editingUser.value && !form.password) {
        errors.password = 'Password is required'
        return
      }

      if (form.password && form.password.length < 6) {
        errors.password = 'Password must be at least 6 characters'
        return
      }

      saving.value = true

      try {
        const userData = {
          name: form.name,
          email: form.email
        }

        // Only include password if it's provided
        if (form.password) {
          userData.password = form.password
          userData.password_confirmation = form.password_confirmation
        }

        if (editingUser.value) {
          const result = await userStore.updateUser(editingUser.value.id, userData)
          if (result.success) {
            // If updating current user, refresh auth store
            if (editingUser.value.id === currentUserId.value) {
              await authStore.checkAuth()
            }
            closeModal()
          }
        } else {
          const result = await userStore.createUser(userData)
          if (result.success) {
            closeModal()
          }
        }
      } catch (error) {
        if (error.response?.data?.errors) {
          Object.keys(error.response.data.errors).forEach(key => {
            errors[key] = error.response.data.errors[key][0]
          })
        } else {
          toast.error(error.response?.data?.message || 'Failed to save user')
        }
      } finally {
        saving.value = false
      }
    }

    const deleteUser = async (user) => {
      if (user.id === currentUserId.value) {
        toast.error('You cannot delete your own account')
        return
      }

      if (!confirm(`Are you sure you want to delete "${user.name}"? This action cannot be undone.`)) {
        return
      }

      try {
        const result = await userStore.deleteUser(user.id)
        if (!result.success) {
          toast.error(result.error || 'Failed to delete user')
        }
      } catch (error) {
        toast.error(error.response?.data?.message || 'Failed to delete user')
      }
    }

    onMounted(async () => {
      await userStore.fetchUsers()
    })

    return {
      showModal,
      editingUser,
      saving,
      errors,
      filters,
      form,
      loading,
      users,
      filteredUsers,
      currentUserId,
      userInitials,
      formatDate,
      openAddModal,
      editUser,
      closeModal,
      saveUser,
      deleteUser
    }
  }
}
</script>

