<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Add New Product</h1>
            <p class="mt-1 text-sm text-gray-600">Create a new product in your inventory.</p>
          </div>
          <button 
            @click="$router.push('/products')"
            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200"
          >
            Back to Products
          </button>
        </div>
      </div>
    </div>

    <!-- Add Form -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="bg-white shadow rounded-lg">
        <form @submit.prevent="handleSave" class="p-6 space-y-6">
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <!-- Product Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">Product Name *</label>
              <input
                id="name"
                ref="nameInput"
                v-model="form.name"
                type="text"
                required
                class="mt-1 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-300': errors.name }"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <!-- Category -->
            <div>
              <label for="category_id" class="block text-sm font-medium text-gray-700">Category *</label>
              <select
                id="category_id"
                v-model="form.category_id"
                required
                class="mt-1 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-300': errors.category_id }"
              >
                <option value="">Select a category</option>
                <option v-for="category in flatCategories" :key="category.id" :value="category.id">
                  {{ category.displayName || category.name }}
                </option>
              </select>
              <p v-if="errors.category_id" class="mt-1 text-sm text-red-600">{{ errors.category_id }}</p>
            </div>

            <!-- Brand -->
            <div>
              <label for="brand_id" class="block text-sm font-medium text-gray-700">Brand</label>
              <select
                id="brand_id"
                v-model="form.brand_id"
                class="mt-1 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-300': errors.brand_id }"
              >
                <option value="">Select a brand (optional)</option>
                <option v-for="brand in flatBrands" :key="brand.id" :value="brand.id">
                  {{ brand.displayName || brand.name }}
                </option>
              </select>
              <p v-if="errors.brand_id" class="mt-1 text-sm text-red-600">{{ errors.brand_id }}</p>
            </div>

            <!-- EAN13 -->
            <div>
              <label for="ean13" class="block text-sm font-medium text-gray-700">EAN13</label>
              <input
                id="ean13"
                v-model="form.ean13"
                type="text"
                class="mt-1 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-300': errors.ean13 }"
              />
              <p v-if="errors.ean13" class="mt-1 text-sm text-red-600">{{ errors.ean13 }}</p>
            </div>

            <!-- Purchase Price -->
            <div>
              <label for="prix_achat" class="block text-sm font-medium text-gray-700">Purchase Price *</label>
              <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-500 sm:text-sm">$</span>
                </div>
                <input
                  id="prix_achat"
                  v-model="form.prix_achat"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="pl-7 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                  :class="{ 'border-red-300': errors.prix_achat }"
                />
              </div>
              <p v-if="errors.prix_achat" class="mt-1 text-sm text-red-600">{{ errors.prix_achat }}</p>
            </div>

            <!-- Sale Price -->
            <div>
              <label for="prix_vente" class="block text-sm font-medium text-gray-700">Sale Price *</label>
              <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-500 sm:text-sm">$</span>
                </div>
                <input
                  id="prix_vente"
                  v-model="form.prix_vente"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="pl-7 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                  :class="{ 'border-red-300': errors.prix_vente }"
                />
              </div>
              <p v-if="errors.prix_vente" class="mt-1 text-sm text-red-600">{{ errors.prix_vente }}</p>
            </div>

            <!-- Stock Quantity -->
            <div>
              <label for="stock_quantity" class="block text-sm font-medium text-gray-700">Stock Quantity</label>
              <input
                id="stock_quantity"
                v-model="form.stock_quantity"
                type="number"
                min="0"
                class="mt-1 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-300': errors.stock_quantity }"
              />
              <p v-if="errors.stock_quantity" class="mt-1 text-sm text-red-600">{{ errors.stock_quantity }}</p>
            </div>

            <!-- Min Stock -->
            <div>
              <label for="min_stock" class="block text-sm font-medium text-gray-700">Min Stock</label>
              <input
                id="min_stock"
                v-model="form.min_stock"
                type="number"
                min="0"
                class="mt-1 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-300': errors.min_stock }"
              />
              <p v-if="errors.min_stock" class="mt-1 text-sm text-red-600">{{ errors.min_stock }}</p>
            </div>

            <!-- Image URL -->
            <div>
              <label for="image" class="block text-sm font-medium text-gray-700">Image URL</label>
              <input
                id="image"
                v-model="form.image"
                type="url"
                class="mt-1 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-300': errors.image }"
              />
              <p v-if="errors.image" class="mt-1 text-sm text-red-600">{{ errors.image }}</p>
            </div>

            <!-- Active Status -->
            <div class="sm:col-span-2">
              <div class="flex items-center">
                <input
                  id="is_active"
                  v-model="form.is_active"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                  Product is active
                </label>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea
              id="description"
              v-model="form.description"
              rows="4"
              class="mt-1 block w-full h-12 text-lg border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
              :class="{ 'border-red-300': errors.description }"
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
            <button
              type="button"
              @click="$router.push('/products')"
              class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
            >
              <svg v-if="saving" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ saving ? 'Creating...' : 'Create Product' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useProductStore } from '../../stores/productStore'
import { useCategoryStore } from '../../stores/categoryStore'
import { useBrandStore } from '../../stores/brandStore'
import { useToast } from 'vue-toastification'

export default {
  name: 'ProductAdd',
  setup() {
    const router = useRouter()
    const productStore = useProductStore()
    const categoryStore = useCategoryStore()
    const brandStore = useBrandStore()
    const toast = useToast()
    const nameInput = ref(null)
    const saving = ref(false)

    const form = ref({
      name: '',
      category_id: '',
      brand_id: '',
      description: '',
      ean13: '',
      prix_achat: 0,
      prix_vente: 0,
      stock_quantity: 0,
      min_stock: 0,
      image: '',
      is_active: true
    })

    const errors = ref({})
    const categories = computed(() => categoryStore.categories)
    const flatCategories = computed(() => categoryStore.flatCategories)
    const brands = computed(() => brandStore.activeBrands)
    const flatBrands = computed(() => brandStore.flatBrandsForSelect.filter(brand => brand.is_active))

    const handleSave = async () => {
      if (saving.value) {
        return
      }
      
      saving.value = true
      errors.value = {}

      try {
        const productData = {
          name: form.value.name,
          category_id: form.value.category_id,
          brand_id: form.value.brand_id || null,
          description: form.value.description || null,
          ean13: form.value.ean13 || null,
          prix_achat: parseFloat(form.value.prix_achat) || 0,
          prix_vente: parseFloat(form.value.prix_vente) || 0,
          stock_quantity: parseInt(form.value.stock_quantity) || 0,
          min_stock: parseInt(form.value.min_stock) || 0,
          image: form.value.image || null,
          is_active: form.value.is_active !== false
        }

        const result = await productStore.createProduct(productData)
        
        if (result.success) {
          router.push('/products')
        } else {
          if (result.errors) {
            errors.value = result.errors
          } else {
            toast.error(result.message || 'Failed to create product')
          }
        }
      } catch (error) {
        if (error.response?.data?.errors) {
          Object.keys(error.response.data.errors).forEach(key => {
            errors.value[key] = error.response.data.errors[key][0]
          })
        } else {
          toast.error(error.response?.data?.message || 'Failed to create product')
        }
      } finally {
        saving.value = false
      }
    }

    onMounted(async () => {
      await Promise.all([
        categoryStore.fetchCategories(),
        brandStore.fetchBrands()
      ])
      await nextTick()
      if (nameInput.value) {
        nameInput.value.focus()
      }
    })

    return {
      nameInput,
      form,
      errors,
      saving,
      categories,
      flatCategories,
      brands,
      flatBrands,
      handleSave
    }
  }
}
</script>
