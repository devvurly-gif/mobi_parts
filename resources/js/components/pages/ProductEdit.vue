<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Product</h1>
            <p class="mt-1 text-sm text-gray-600">Update product information and settings.</p>
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

    <!-- Loading State -->
    <div v-if="loading" class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="bg-white shadow rounded-lg p-6 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        <p class="mt-2 text-gray-600">Loading product...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="bg-red-50 border border-red-200 rounded-lg p-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error</h3>
            <div class="mt-2 text-sm text-red-700">
              <p>{{ error }}</p>
            </div>
            <div class="mt-4">
              <button 
                @click="loadProduct"
                class="bg-red-100 hover:bg-red-200 text-red-800 px-3 py-2 rounded-md text-sm font-medium"
              >
                Try Again
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Form -->
    <div v-else-if="product" class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="bg-white shadow rounded-lg">
        <form @submit.prevent="updateProduct" class="p-6 space-y-6">
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <!-- Product Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">Product Name *</label>
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
              {{ saving ? 'Saving...' : 'Update Product' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductStore } from '../../stores/productStore'
import { useCategoryStore } from '../../stores/categoryStore'
import { useBrandStore } from '../../stores/brandStore'
import { useToast } from 'vue-toastification'

export default {
  name: 'ProductEdit',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const productStore = useProductStore()
    const categoryStore = useCategoryStore()
    const brandStore = useBrandStore()
    const toast = useToast()

    const loading = ref(true)
    const saving = ref(false)
    const error = ref('')
    const product = ref(null)
    const errors = ref({})

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

    const categories = computed(() => categoryStore.categories)
    const flatCategories = computed(() => categoryStore.flatCategories)
    const brands = computed(() => brandStore.activeBrands)
    const flatBrands = computed(() => brandStore.flatBrandsForSelect.filter(brand => brand.is_active))

    const loadProduct = async () => {
      try {
        loading.value = true
        error.value = ''
        const productId = route.params.id
        
        // Try to get product from store first
        const existingProduct = productStore.products.find(p => p.id == productId)
        if (existingProduct) {
          product.value = existingProduct
          populateForm(existingProduct)
        } else {
          // Fetch from API if not in store
          const result = await productStore.fetchProduct(productId)
          if (result.success) {
            product.value = result.data
            populateForm(result.data)
          } else {
            error.value = result.message || 'Failed to load product'
          }
        }
      } catch (err) {
        console.error('Error loading product:', err)
        error.value = 'Failed to load product. Please try again.'
      } finally {
        loading.value = false
      }
    }

    const populateForm = (productData) => {
      form.value = {
        name: productData.name || '',
        category_id: productData.category_id || '',
        brand_id: productData.brand_id || '',
        description: productData.description || '',
        ean13: productData.ean13 || '',
        prix_achat: productData.prix_achat || 0,
        prix_vente: productData.prix_vente || 0,
        stock_quantity: productData.stock_quantity || 0,
        min_stock: productData.min_stock || 0,
        image: productData.image || '',
        is_active: productData.is_active !== false
      }
    }

    const updateProduct = async () => {
      try {
        saving.value = true
        errors.value = {}
        
        const result = await productStore.updateProduct(route.params.id, form.value)
        
        if (result.success) {
          router.push('/products')
        } else {
          if (result.errors) {
            errors.value = result.errors
          } else {
            toast.error(result.message || 'Failed to update product')
          }
        }
      } catch (err) {
        console.error('Error updating product:', err)
        toast.error('Failed to update product. Please try again.')
      } finally {
        saving.value = false
      }
    }

    onMounted(async () => {
      await Promise.all([
        categoryStore.fetchCategories(),
        brandStore.fetchBrands()
      ])
      await loadProduct()
    })

    return {
      loading,
      saving,
      error,
      product,
      form,
      errors,
      categories,
      flatCategories,
      brands,
      flatBrands,
      loadProduct,
      updateProduct
    }
  }
}
</script>
