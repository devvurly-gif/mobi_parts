<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Image Gallery</h1>
            <p class="mt-1 text-sm text-gray-600">Manage and attach images to products. Select images and choose a product to attach them to.</p>
          </div>
          <div class="flex items-center space-x-3">
            <button 
              @click="$router.push('/images/upload')"
              class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
              </svg>
              Upload Images
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <!-- Filters and Actions -->
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center space-x-4">
            <label class="flex items-center">
              <input 
                type="checkbox" 
                v-model="showOnlyUnattached"
                @change="fetchImages"
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
              />
              <span class="ml-2 text-sm text-gray-700">Show only unattached images</span>
            </label>
          </div>
          <div v-if="selectedImages.length > 0" class="flex items-center space-x-3">
            <span class="text-sm text-gray-700">{{ selectedImages.length }} image(s) selected</span>
            <select 
              v-model="selectedProductId"
              class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
            >
              <option value="">Select a product...</option>
              <option v-for="product in products" :key="product.id" :value="product.id">
                {{ product.name }} ({{ product.ean13 || 'No EAN13' }})
              </option>
            </select>
            <button
              @click="attachImages"
              :disabled="!selectedProductId || attaching"
              class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="attaching">Attaching...</span>
              <span v-else>Attach to Product</span>
            </button>
            <button
              @click="clearSelection"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Clear Selection
            </button>
          </div>
        </div>
      </div>

      <!-- Images Table -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <input 
                    type="checkbox" 
                    :checked="allSelected"
                    @change="toggleSelectAll"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Image
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Alt Text
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Product
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Uploaded
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="loading" class="bg-white">
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                  <div class="flex items-center justify-center">
                    <svg class="animate-spin h-5 w-5 text-indigo-600 mr-2" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading images...
                  </div>
                </td>
              </tr>
              <tr v-else-if="images.length === 0" class="bg-white">
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                  No images found. 
                  <router-link to="/images/upload" class="text-indigo-600 hover:text-indigo-900">Upload some images</router-link>
                </td>
              </tr>
              <tr 
                v-else
                v-for="image in images" 
                :key="image.id"
                :class="[
                  'hover:bg-gray-50',
                  selectedImages.includes(image.id) ? 'bg-indigo-50' : ''
                ]"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <input 
                    type="checkbox" 
                    :value="image.id"
                    v-model="selectedImages"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-16 w-16 flex-shrink-0">
                      <img 
                        :src="image.image_url" 
                        :alt="image.alt_text || 'Image'"
                        class="h-16 w-16 object-cover rounded border border-gray-200"
                        @error="handleImageError"
                      />
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900 max-w-xs truncate" :title="image.alt_text || 'No alt text'">
                    {{ image.alt_text || 'No alt text' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div v-if="image.product" class="text-sm">
                    <div class="text-gray-900 font-medium">{{ image.product.name }}</div>
                    <div class="text-gray-500 text-xs">{{ image.product.ean13 || 'No EAN13' }}</div>
                  </div>
                  <div v-else class="text-sm text-gray-400 italic">Not attached</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    v-if="image.product_id"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                  >
                    Attached
                  </span>
                  <span 
                    v-else
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                  >
                    Unattached
                  </span>
                  <span 
                    v-if="image.is_primary"
                    class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                  >
                    Primary
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(image.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center space-x-2">
                    <button
                      v-if="image.product_id"
                      @click="viewProduct(image.product_id)"
                      class="text-indigo-600 hover:text-indigo-900"
                      title="View Product"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </button>
                    <button
                      @click="deleteImage(image)"
                      class="text-red-600 hover:bg-red-600 hover:text-white hover:shadow-md hover:scale-110 transition-all duration-200 p-2 rounded-md"
                      title="Delete Image"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useProductStore } from '../../stores/productStore'

const router = useRouter()
const toast = useToast()
const productStore = useProductStore()

const images = ref([])
const products = ref([])
const selectedImages = ref([])
const selectedProductId = ref('')
const showOnlyUnattached = ref(true)
const loading = ref(false)
const attaching = ref(false)

const allSelected = computed(() => {
  const selectableImages = images.value.filter(img => !img.product_id || !showOnlyUnattached.value)
  return selectableImages.length > 0 && selectableImages.every(img => selectedImages.value.includes(img.id))
})

const fetchImages = async () => {
  loading.value = true
  try {
    const url = showOnlyUnattached.value 
      ? '/api/product-images?unattached=true'
      : '/api/product-images'
    const response = await window.axios.get(url)
    images.value = response.data
  } catch (error) {
    console.error('Error fetching images:', error)
    toast.error('Failed to fetch images')
  } finally {
    loading.value = false
  }
}

const fetchProducts = async () => {
  try {
    await productStore.fetchProducts()
    products.value = productStore.products
  } catch (error) {
    console.error('Error fetching products:', error)
    toast.error('Failed to fetch products')
  }
}

const toggleSelectAll = () => {
  const selectableImages = images.value.filter(img => !img.product_id || !showOnlyUnattached.value)
  if (allSelected.value) {
    selectedImages.value = selectedImages.value.filter(id => 
      !selectableImages.some(img => img.id === id)
    )
  } else {
    const newSelections = selectableImages
      .filter(img => !selectedImages.value.includes(img.id))
      .map(img => img.id)
    selectedImages.value = [...selectedImages.value, ...newSelections]
  }
}

const clearSelection = () => {
  selectedImages.value = []
  selectedProductId.value = ''
}

const attachImages = async () => {
  if (selectedImages.value.length === 0 || !selectedProductId.value) {
    toast.error('Please select images and a product')
    return
  }

  attaching.value = true

  try {
    const response = await window.axios.post('/api/product-images/attach', {
      product_id: selectedProductId.value,
      image_ids: selectedImages.value
    })

    toast.success(`${response.data.images.length} image(s) attached successfully!`)
    clearSelection()
    await fetchImages()
  } catch (error) {
    console.error('Error attaching images:', error)
    toast.error(error.response?.data?.message || 'Failed to attach images')
  } finally {
    attaching.value = false
  }
}

const deleteImage = async (image) => {
  if (!confirm(`Are you sure you want to delete this image?`)) {
    return
  }

  try {
    await window.axios.delete(`/api/product-images/${image.id}`)
    toast.success('Image deleted successfully')
    await fetchImages()
    // Remove from selection if it was selected
    selectedImages.value = selectedImages.value.filter(id => id !== image.id)
  } catch (error) {
    console.error('Error deleting image:', error)
    toast.error('Failed to delete image')
  }
}

const viewProduct = (productId) => {
  router.push(`/product/${productId}`)
}

const handleImageError = (event) => {
  event.target.src = 'https://via.placeholder.com/150?text=Image+Error'
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

onMounted(async () => {
  await Promise.all([
    fetchImages(),
    fetchProducts()
  ])
})
</script>

