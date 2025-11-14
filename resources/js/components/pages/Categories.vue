<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Categories</h1>
            <p class="mt-1 text-sm text-gray-600">Manage your product categories.</p>
          </div>
          <button 
            @click="openAddModal"
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200"
          >
            Add Category
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
          placeholder="Search by name..."
          class="mt-1 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
        />
      </div>

      <!-- Categories Table -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div v-if="loading" class="p-6 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <p class="mt-2 text-gray-600">Loading categories...</p>
        </div>

        <div v-else-if="filteredCategories.length === 0" class="p-6 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No categories found</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by creating a new category.</p>
        </div>

        <ul v-else class="divide-y divide-gray-200">
          <li v-for="category in filteredCategories" :key="category.id" class="px-6 py-4 hover:bg-gray-50">
            <div class="flex items-center justify-between">
              <div class="flex items-center flex-1">
                <div class="shrink-0">
                  <div class="h-12 w-12 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex items-center justify-center">
                    <span class="text-lg font-medium text-gray-500">{{ category.name.charAt(0).toUpperCase() }}</span>
                  </div>
                </div>
                <div class="ml-4 flex-1">
                  <div class="flex items-center">
                    <p class="text-sm font-medium text-gray-900">
                      {{ category.name }}
                    </p>
                    <span v-if="category.is_active" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      Active
                    </span>
                    <span v-else class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                      Inactive
                    </span>
                  </div>
                </div>
              </div>
              <div class="flex items-center space-x-3">
                <button
                  @click="editCategory(category)"
                  class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                  title="Edit category"
                >
                  Edit
                </button>
                <button
                  @click="deleteCategory(category)"
                  class="text-red-600 hover:text-red-900 text-sm font-medium"
                  title="Delete category"
                >
                  Delete
                </button>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <!-- Add/Edit Category Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 overflow-y-auto bg-gray-500"
    >
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                {{ editingCategory ? 'Edit Category' : 'Add Category' }}
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

            <form @submit.prevent="saveCategory">
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

                <!-- Active Status -->
                <div class="flex items-center">
                  <input
                    id="is_active"
                    v-model="form.is_active"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  />
                  <label for="is_active" class="ml-2 block text-sm text-gray-900">
                    Active
                  </label>
                </div>
              </div>

              <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button
                  type="submit"
                  :disabled="saving"
                  class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="saving">Saving...</span>
                  <span v-else>{{ editingCategory ? 'Update' : 'Create' }}</span>
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
import { useCategoryStore } from '../../stores/categoryStore'
import { useToast } from 'vue-toastification'

export default {
  name: 'Categories',
  setup() {
    const categoryStore = useCategoryStore()
    const toast = useToast()

    const showModal = ref(false)
    const editingCategory = ref(null)
    const saving = ref(false)
    const errors = reactive({})

    const filters = reactive({
      search: ''
    })

    const form = reactive({
      name: '',
      is_active: true
    })

    const loading = computed(() => categoryStore.loading)
    const categories = computed(() => categoryStore.categories)

    const filteredCategories = computed(() => {
      let filtered = categories.value

      // Search filter
      if (filters.search) {
        const search = filters.search.toLowerCase()
        filtered = filtered.filter(category => 
          category.name.toLowerCase().includes(search)
        )
      }

      return filtered
    })

    const clearErrors = () => {
      Object.keys(errors).forEach(key => {
        errors[key] = ''
      })
    }

    const resetForm = () => {
      form.name = ''
      form.is_active = true
      clearErrors()
    }

    const openAddModal = () => {
      editingCategory.value = null
      resetForm()
      showModal.value = true
    }

    const editCategory = (category) => {
      editingCategory.value = category
      form.name = category.name || ''
      form.is_active = category.is_active !== false
      clearErrors()
      showModal.value = true
    }

    const closeModal = () => {
      showModal.value = false
      editingCategory.value = null
      resetForm()
    }

    const saveCategory = async () => {
      clearErrors()
      saving.value = true

      try {
        const categoryData = {
          name: form.name,
          is_active: form.is_active
        }

        if (editingCategory.value) {
          const result = await categoryStore.updateCategory(editingCategory.value.id, categoryData)
          if (result.success) {
            closeModal()
          }
        } else {
          const result = await categoryStore.createCategory(categoryData)
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
          toast.error(error.response?.data?.message || 'Failed to save category')
        }
      } finally {
        saving.value = false
      }
    }

    const deleteCategory = async (category) => {
      if (!confirm(`Are you sure you want to delete "${category.name}"? This action cannot be undone.`)) {
        return
      }

      try {
        const result = await categoryStore.deleteCategory(category.id)
        if (!result.success) {
          toast.error(result.error || 'Failed to delete category')
        }
      } catch (error) {
        toast.error(error.response?.data?.message || 'Failed to delete category')
      }
    }

    const clearFilters = () => {
      filters.search = ''
    }

    onMounted(async () => {
      await categoryStore.fetchCategories()
    })

    return {
      showModal,
      editingCategory,
      saving,
      errors,
      filters,
      form,
      loading,
      categories,
      filteredCategories,
      openAddModal,
      editCategory,
      closeModal,
      saveCategory,
      deleteCategory,
      clearFilters
    }
  }
}
</script>
