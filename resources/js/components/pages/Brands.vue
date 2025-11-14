<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Brands</h1>
            <p class="mt-1 text-sm text-gray-600">Manage your product brands.</p>
          </div>
          <button 
            @click="openAddModal"
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200"
          >
            Add Brand
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
          placeholder="Search by name or description..."
          class="mt-1 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
        />
      </div>

      <!-- Brands Table -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div v-if="loading" class="p-6 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <p class="mt-2 text-gray-600">Loading brands...</p>
        </div>

        <div v-else-if="filteredBrands.length === 0" class="p-6 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No brands found</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by creating a new brand.</p>
        </div>

        <ul v-else class="divide-y divide-gray-200">
          <li v-for="brand in filteredBrands" :key="brand.id" class="px-6 py-4 hover:bg-gray-50">
            <div class="flex items-center justify-between">
              <div class="flex items-center flex-1">
                <div class="shrink-0">
                  <div class="h-12 w-12 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex items-center justify-center">
                    <img
                      v-if="brand.logo"
                      :src="brand.logo"
                      :alt="brand.name"
                      class="h-full w-full object-cover"
                    />
                    <span v-else class="text-lg font-medium text-gray-500">{{ brand.name.charAt(0).toUpperCase() }}</span>
                  </div>
                </div>
                <div class="ml-4 flex-1">
                  <div class="flex items-center">
                    <p class="text-sm font-medium text-gray-900">
                      <span v-if="brand.parent" class="text-gray-400 mr-1">└─</span>
                      {{ brand.name }}
                    </p>
                    <span v-if="brand.is_active" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      Active
                    </span>
                    <span v-else class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                      Inactive
                    </span>
                  </div>
                  <p v-if="brand.parent" class="mt-1 text-xs text-gray-400">
                    Parent: {{ brand.parent.name }}
                  </p>
                  <p v-if="brand.description" class="mt-1 text-sm text-gray-500">{{ brand.description }}</p>
                </div>
              </div>
              <div class="flex items-center space-x-3">
                <button
                  v-if="brand.parent_id"
                  @click="removeParent(brand)"
                  class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                  title="Remove parent (make root brand)"
                >
                  Make Root
                </button>
                <button
                  @click="editBrand(brand)"
                  class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                  title="Edit brand"
                >
                  Edit
                </button>
                <button
                  @click="deleteBrand(brand)"
                  class="text-red-600 hover:text-red-900 text-sm font-medium"
                  title="Delete brand"
                >
                  Delete
                </button>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <!-- Add/Edit Brand Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 overflow-y-auto bg-gray-500"
    >
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                {{ editingBrand ? 'Edit Brand' : 'Add Brand' }}
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

            <form @submit.prevent="saveBrand">
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

                <!-- Parent Brand -->
                <div>
                  <label for="parent_id" class="block text-sm font-medium text-gray-700">
                    Parent Brand
                  </label>
                  <select
                    id="parent_id"
                    v-model="form.parent_id"
                    class="mt-1 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    :class="{ 'border-red-300': errors.parent_id }"
                  >
                    <option value="">None (Root Brand)</option>
                    <option v-for="brand in availableParents" :key="brand.id" :value="String(brand.id)">
                      {{ brand.displayName || brand.name }}
                    </option>
                  </select>
                  <p v-if="errors.parent_id" class="mt-1 text-sm text-red-600">{{ errors.parent_id }}</p>
                  <p class="mt-1 text-xs text-gray-500">Optional: Select a parent brand to create a sub-brand</p>
                </div>

                <!-- Description -->
                <div>
                  <label for="description" class="block text-sm font-medium text-gray-700">
                    Description
                  </label>
                  <textarea
                    id="description"
                    v-model="form.description"
                    rows="3"
                    class="mt-1 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    :class="{ 'border-red-300': errors.description }"
                  ></textarea>
                  <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
                </div>

                <!-- Logo URL -->
                <div>
                  <label for="logo" class="block text-sm font-medium text-gray-700">
                    Logo URL
                  </label>
                  <input
                    id="logo"
                    v-model="form.logo"
                    type="text"
                    placeholder="https://example.com/logo.png"
                    class="mt-1 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    :class="{ 'border-red-300': errors.logo }"
                  />
                  <p v-if="errors.logo" class="mt-1 text-sm text-red-600">{{ errors.logo }}</p>
                  <p class="mt-1 text-xs text-gray-500">Optional: URL to a logo image for this brand</p>
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
                  <span v-else>{{ editingBrand ? 'Update' : 'Create' }}</span>
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
import { useBrandStore } from '../../stores/brandStore'
import { useToast } from 'vue-toastification'

export default {
  name: 'Brands',
  setup() {
    const brandStore = useBrandStore()
    const toast = useToast()

    const showModal = ref(false)
    const editingBrand = ref(null)
    const saving = ref(false)
    const errors = reactive({})

    const filters = reactive({
      search: ''
    })

    const form = reactive({
      name: '',
      parent_id: '',
      description: '',
      logo: '',
      is_active: true
    })

    const loading = computed(() => brandStore.loading)
    const brands = computed(() => brandStore.brands)
    
    // Get available parent brands (exclude current brand if editing)
    const availableParents = computed(() => {
      if (editingBrand.value) {
        // Exclude current brand and its descendants
        return brandStore.flatBrandsForSelect.filter(brand => 
          brand.id !== editingBrand.value.id
        )
      }
      return brandStore.flatBrandsForSelect
    })

    const filteredBrands = computed(() => {
      let filtered = brands.value

      // Search filter
      if (filters.search) {
        const search = filters.search.toLowerCase()
        filtered = filtered.filter(brand => 
          brand.name.toLowerCase().includes(search) ||
          (brand.description && brand.description.toLowerCase().includes(search))
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
      form.parent_id = ''
      form.description = ''
      form.logo = ''
      form.is_active = true
      clearErrors()
    }

    const openAddModal = () => {
      editingBrand.value = null
      resetForm()
      showModal.value = true
    }

    const editBrand = (brand) => {
      editingBrand.value = brand
      form.name = brand.name || ''
      form.parent_id = brand.parent_id ? String(brand.parent_id) : ''
      form.description = brand.description || ''
      form.logo = brand.logo || ''
      form.is_active = brand.is_active !== false
      clearErrors()
      showModal.value = true
    }

    const closeModal = () => {
      showModal.value = false
      editingBrand.value = null
      resetForm()
    }

    const saveBrand = async () => {
      clearErrors()
      saving.value = true

      try {
        const brandData = {
          name: form.name,
          parent_id: form.parent_id ? parseInt(form.parent_id) : null,
          description: form.description || null,
          logo: form.logo || null,
          is_active: form.is_active
        }

        if (editingBrand.value) {
          const result = await brandStore.updateBrand(editingBrand.value.id, brandData)
          if (result.success) {
            closeModal()
          }
        } else {
          const result = await brandStore.createBrand(brandData)
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
          toast.error(error.response?.data?.message || 'Failed to save brand')
        }
      } finally {
        saving.value = false
      }
    }

    const removeParent = async (brand) => {
      if (!confirm(`Are you sure you want to remove the parent from "${brand.name}"? It will become a root brand.`)) {
        return
      }

      try {
        const result = await brandStore.removeParent(brand.id)
        if (result.success) {
          // Close modal if open and refresh brands
          if (showModal.value && editingBrand.value?.id === brand.id) {
            closeModal()
          }
          // Refresh brands list to ensure UI is updated
          await brandStore.fetchBrands()
        } else {
          toast.error(result.error || 'Failed to remove parent')
        }
      } catch (error) {
        toast.error(error.response?.data?.message || 'Failed to remove parent')
      }
    }

    const deleteBrand = async (brand) => {
      if (!confirm(`Are you sure you want to delete "${brand.name}"? This action cannot be undone.`)) {
        return
      }

      try {
        const result = await brandStore.deleteBrand(brand.id)
        if (!result.success) {
          toast.error(result.error || 'Failed to delete brand')
        }
      } catch (error) {
        toast.error(error.response?.data?.message || 'Failed to delete brand')
      }
    }

    const clearFilters = () => {
      filters.search = ''
    }

    onMounted(async () => {
      await brandStore.fetchBrands()
    })

    return {
      showModal,
      editingBrand,
      saving,
      errors,
      filters,
      form,
      loading,
      brands,
      filteredBrands,
      availableParents,
      openAddModal,
      editBrand,
      closeModal,
      saveBrand,
      removeParent,
      deleteBrand,
      clearFilters
    }
  }
}
</script>

