<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
          <button
            @click="$router.push('/')"
            class="inline-flex items-center text-gray-600 hover:text-gray-900"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Products
          </button>
          <router-link
            to="/login"
            class="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
          >
            Login
          </router-link>
        </div>
      </div>
    </header>

    <!-- Loading State -->
    <div v-if="loading" class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        <p class="mt-4 text-gray-600">Loading product...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
      <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-red-800">Product Not Found</h3>
        <p class="mt-2 text-sm text-red-600">{{ error }}</p>
        <button
          @click="$router.push('/')"
          class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
        >
          Go Back to Products
        </button>
      </div>
    </div>

    <!-- Product Details -->
    <div v-else-if="product" class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="lg:grid lg:grid-cols-2 lg:gap-8">
          <!-- Product Images Gallery -->
          <div class="p-6">
            <!-- Main Image Display -->
            <div 
              class="aspect-w-1 aspect-h-1 rounded-lg overflow-hidden bg-gray-100 cursor-pointer hover:opacity-90 transition-opacity relative"
              @click="allImages.length > 0 && openGallery(allImages[currentImageIndex])"
            >
              <img
                v-if="currentDisplayImage?.image_url"
                :src="currentDisplayImage.image_url"
                :alt="product.name"
                class="w-full h-full object-cover"
                @error="handleImageError"
              />
              <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </div>
              <!-- Image Counter Badge -->
              <div v-if="allImages.length > 1" class="absolute top-4 right-4 bg-opacity-60 text-white px-3 py-1 rounded-full text-sm font-medium">
                {{ currentImageIndex + 1 }} / {{ allImages.length }}
              </div>
              <!-- Navigation Arrows (if more than 1 image) -->
              <div v-if="allImages.length > 1" class="absolute inset-0 flex items-center justify-between px-4 opacity-0 hover:opacity-100 transition-opacity">
                <button
                  @click.stop="previousImage"
                  class="bg-white bg-opacity-80 hover:bg-opacity-100 rounded-full p-2 shadow-lg"
                  aria-label="Previous image"
                >
                  <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                  </svg>
                </button>
                <button
                  @click.stop="nextImage"
                  class="bg-white bg-opacity-80 hover:bg-opacity-100 rounded-full p-2 shadow-lg"
                  aria-label="Next image"
                >
                  <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Thumbnail Gallery -->
            <div v-if="allImages.length > 1" class="mt-4">
              <h3 class="text-sm font-medium text-gray-700 mb-3">All Images ({{ allImages.length }})</h3>
              <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-2 max-h-64 overflow-y-auto">
                <div
                  v-for="(image, index) in allImages"
                  :key="image.id || index"
                  :class="[
                    'aspect-w-1 aspect-h-1 rounded-lg overflow-hidden bg-gray-100 cursor-pointer border-2 transition-all',
                    currentImageIndex === index ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-transparent hover:border-gray-300'
                  ]"
                  @click="selectImage(index)"
                >
                  <img
                    :src="image.image_url"
                    :alt="`${product.name} - Image ${index + 1}`"
                    class="w-full h-full object-cover"
                    @error="handleImageError"
                  />
                  <!-- Primary Badge -->
                  <div v-if="image.is_primary" class="absolute top-1 right-1">
                    <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Product Information -->
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900">{{ product.name }}</h1>
                <p class="mt-2 text-lg text-gray-600">{{ product.category?.name || 'No Category' }}</p>
              </div>
              <span
                v-if="!product.is_active"
                class="ml-4 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800"
              >
                Inactive
              </span>
            </div>

            <!-- Price -->
            <div class="mt-6">
              <p class="text-4xl font-bold text-indigo-600">${{ parseFloat(product.prix_vente || 0).toFixed(2) }}</p>
              <p v-if="product.prix_achat" class="mt-1 text-sm text-gray-500">
                Cost: ${{ parseFloat(product.prix_achat).toFixed(2) }}
              </p>
            </div>

            <!-- Stock Information -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Stock Status</span>
                <span
                  :class="[
                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                    product.stock_quantity > 0
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ product.stock_quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                </span>
              </div>
              <p class="mt-2 text-sm text-gray-600">
                Available: <span class="font-semibold">{{ product.stock_quantity || 0 }}</span> units
              </p>
              <p v-if="product.min_stock" class="mt-1 text-xs text-gray-500">
                Minimum stock: {{ product.min_stock }} units
              </p>
            </div>

            <!-- EAN13 -->
            <div v-if="product.ean13" class="mt-6">
              <label class="text-sm font-medium text-gray-700">EAN13 / Barcode</label>
              <p class="mt-1 text-lg font-mono text-gray-900">{{ product.ean13 }}</p>
            </div>

            <!-- Description -->
            <div v-if="product.description" class="mt-6">
              <label class="text-sm font-medium text-gray-700">Description</label>
              <p class="mt-2 text-gray-600 whitespace-pre-line">{{ product.description }}</p>
            </div>

            <!-- Contact Info for Public -->
            <div class="mt-8 pt-6 border-t border-gray-200">
              <p class="text-sm text-gray-600 mb-4">Interested in this product?</p>
              <router-link
                to="/login"
                class="inline-flex items-center justify-center w-full px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors"
              >
                Login to Contact Seller
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t mt-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">© {{ new Date().getFullYear() }} Mobi Parts</p>
        <router-link to="/" class="text-sm text-indigo-600 hover:text-indigo-800">
          Browse All Products
        </router-link>
      </div>
    </footer>

    <!-- Image Gallery Lightbox Modal -->
    <div
      v-if="galleryOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-opacity-90"
      @click.self="closeGallery"
    >
      <!-- Close Button -->
      <button
        @click="closeGallery"
        class="absolute top-4 right-4 text-white hover:text-gray-300 z-10"
        aria-label="Close gallery"
      >
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>

      <!-- Previous Button -->
      <button
        v-if="allImages.length > 1"
        @click.stop="previousImage"
        class="absolute left-4 text-white hover:text-gray-300 z-10 bg-opacity-50 rounded-full p-3"
        aria-label="Previous image"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
      </button>

      <!-- Next Button -->
      <button
        v-if="allImages.length > 1"
        @click.stop="nextImage"
        class="absolute right-4 text-white hover:text-gray-300 z-10 bg-opacity-50 rounded-full p-3"
        aria-label="Next image"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
      </button>

      <!-- Image Display -->
      <div class="relative max-w-7xl mx-auto px-4">
        <img
          :src="galleryImage?.image_url"
          :alt="product?.name"
          class="max-h-[90vh] max-w-full object-contain"
          @error="handleImageError"
        />
        
        <!-- Image Counter -->
        <div v-if="allImages.length > 1" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-opacity-60 text-white px-4 py-2 rounded-full text-sm font-medium">
          {{ currentImageIndex + 1 }} / {{ allImages.length }}
        </div>
      </div>

      <!-- Thumbnail Strip at Bottom -->
      <div v-if="allImages.length > 1" class="absolute bottom-0 left-0 right-0 bg-opacity-50 py-4">
        <div class="max-w-7xl mx-auto px-4 overflow-x-auto">
          <div class="flex space-x-2 justify-center">
            <div
              v-for="(image, index) in allImages"
              :key="image.id || index"
              :class="[
                'shrink-0 w-16 h-16 rounded-lg overflow-hidden cursor-pointer border-2 transition-all',
                currentImageIndex === index ? 'border-white ring-2 ring-indigo-400' : 'border-transparent hover:border-gray-400'
              ]"
              @click="selectImage(index)"
            >
              <img
                :src="image.image_url"
                :alt="`Thumbnail ${index + 1}`"
                class="w-full h-full object-cover"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductStore } from '../../stores/productStore'

export default {
  name: 'ProductView',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const productStore = useProductStore()

    const loading = computed(() => productStore.loading)
    const error = computed(() => productStore.error)
    const product = computed(() => productStore.currentProduct)
    
    // Gallery state
    const currentImageIndex = ref(0)
    const galleryOpen = ref(false)
    const galleryImage = ref(null)

    // Get all images (including primary image)
    const allImages = computed(() => {
      if (!product.value) return []
      
      const images = []
      
      // Add primary image first if exists
      if (product.value.primary_image?.image_url) {
        images.push(product.value.primary_image)
      }
      
      // Add other images (excluding primary if already added)
      if (product.value.images && product.value.images.length > 0) {
        product.value.images.forEach(img => {
          // Only add if it's not the primary image
          if (!img.is_primary || !product.value.primary_image) {
            images.push(img)
          }
        })
      }
      
      return images.length > 0 ? images : []
    })

    // Current display image
    const currentDisplayImage = computed(() => {
      return allImages.value[currentImageIndex.value] || null
    })

    const handleImageError = (event) => {
      console.error('Image failed to load:', event.target.src)
      const img = event.target
      const placeholderUrl = `https://via.placeholder.com/800x800/E5E7EB/9CA3AF?text=${encodeURIComponent(product.value?.name?.charAt(0) || 'P')}`
      
      if (!img.src.includes('placeholder') && !img.src.includes('via.placeholder')) {
        img.src = placeholderUrl
      } else {
        img.style.display = 'none'
      }
    }

    // Gallery functions
    const selectImage = (index) => {
      currentImageIndex.value = index
    }

    const nextImage = () => {
      if (currentImageIndex.value < allImages.value.length - 1) {
        currentImageIndex.value++
      } else {
        currentImageIndex.value = 0 // Loop to first
      }
      if (galleryOpen.value) {
        galleryImage.value = allImages.value[currentImageIndex.value]
      }
    }

    const previousImage = () => {
      if (currentImageIndex.value > 0) {
        currentImageIndex.value--
      } else {
        currentImageIndex.value = allImages.value.length - 1 // Loop to last
      }
      if (galleryOpen.value) {
        galleryImage.value = allImages.value[currentImageIndex.value]
      }
    }

    const openGallery = (image) => {
      if (!image) return
      const index = allImages.value.findIndex(img => img.id === image.id || img.image_url === image.image_url)
      if (index !== -1) {
        currentImageIndex.value = index
      }
      galleryImage.value = image
      galleryOpen.value = true
      // Prevent body scroll when gallery is open
      document.body.style.overflow = 'hidden'
    }

    const closeGallery = () => {
      galleryOpen.value = false
      galleryImage.value = null
      document.body.style.overflow = ''
    }

    // Keyboard navigation for gallery
    const handleKeyPress = (event) => {
      if (!galleryOpen.value) return
      
      if (event.key === 'Escape') {
        closeGallery()
      } else if (event.key === 'ArrowLeft') {
        previousImage()
      } else if (event.key === 'ArrowRight') {
        nextImage()
      }
    }

    onMounted(async () => {
      const productId = route.params.id
      
      // Check if product is already in store
      const existingProduct = productStore.productById(productId)
      
      if (existingProduct) {
        productStore.setCurrentProduct(existingProduct)
      } else {
        // Fetch product from API
        const result = await productStore.fetchProduct(productId)
        if (!result.success) {
          // Product not found or error
          console.error('Failed to load product:', result.error)
        }
      }

      // Add keyboard event listener
      window.addEventListener('keydown', handleKeyPress)
    })

    // Cleanup
    onUnmounted(() => {
      window.removeEventListener('keydown', handleKeyPress)
      document.body.style.overflow = ''
    })

    return {
      loading,
      error,
      product,
      allImages,
      currentImageIndex,
      currentDisplayImage,
      galleryOpen,
      galleryImage,
      selectImage,
      nextImage,
      previousImage,
      openGallery,
      closeGallery,
      handleImageError
    }
  }
}
</script>

<style scoped>
.aspect-w-1 {
  position: relative;
  padding-bottom: 100%;
}

.aspect-w-1 > * {
  position: absolute;
  height: 100%;
  width: 100%;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}
</style>

