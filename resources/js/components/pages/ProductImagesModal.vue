<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 h-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Background overlay with blur effect -->
    <div class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm transition-opacity z-40" @click="closeModal"></div>

    <!-- Modal panel - Responsive sizing -->
    <div class="relative z-50 flex items-center justify-center min-h-screen p-4">
      <div class="relative w-full max-w-xs sm:max-w-md md:max-w-lg lg:max-w-3xl xl:max-w-3xl mx-auto">
        <!-- Modal content -->
        <div class="bg-white rounded-2xl shadow-2xl h-auto">
          <!-- Header -->
          <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-4 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="text-lg font-bold text-white">
                    Manage Images
                  </h3>
                  <p class="text-indigo-100 text-xs truncate max-w-48">{{ product?.name }}</p>
                </div>
              </div>
              <button @click="closeModal" class="text-white hover:text-indigo-200 transition-colors duration-200 p-1 rounded-full hover:bg-white hover:bg-opacity-20">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Content -->
          <div class="  h-full">
            <div class="p-4 sm:p-6 h-auto">
              <!-- Upload Section -->
              <div class="mb-6">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border-2 border-dashed border-blue-200 hover:border-blue-300 transition-all duration-200">
                  <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-3">
                      <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                      </svg>
                    </div>
                    <h3 class="text-base font-medium text-gray-900 mb-2">Upload Images</h3>
                    <div class="flex justify-center">
                      <label for="file-upload" class="relative cursor-pointer bg-white rounded-md px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500 transition-colors">
                        <span>Choose Files</span>
                        <input 
                          id="file-upload" 
                          name="file-upload" 
                          type="file" 
                          class="sr-only" 
                          multiple 
                          accept="image/*"
                          @change="handleFileSelect"
                        />
                      </label>
                    </div>
                    <p class="mt-2 text-xs text-gray-600">or drag and drop</p>
                    <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF up to 2MB each</p>
                  </div>
                </div>
              </div>

              <!-- Upload Progress -->
              <div v-if="uploading" class="mb-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <svg class="h-5 w-5 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                    </div>
                    <div class="ml-3">
                      <p class="text-sm text-blue-700">Uploading images...</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Images Grid -->
              <div v-if="images.length > 0">
                <div class="flex items-center justify-between mb-4">
                  <h4 class="text-lg font-medium text-gray-900">Product Images ({{ images.length }})</h4>
                  <div class="text-sm text-gray-500">
                    Click star to set primary • Click trash to delete
                  </div>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                  <div 
                    v-for="(image, index) in images" 
                    :key="image.id || `temp-${index}`"
                    class="relative group bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-200"
                  >
                    <!-- Image Preview -->
                    <div class="aspect-square relative overflow-hidden">
                      <img 
                        :src="getImageUrl(image)" 
                        :alt="image.alt_text || 'Product image'"
                        class="w-full h-full object-cover object-center"
                        @error="handleImageError"
                      />
                      
                      <!-- Loading overlay for temp images -->
                      <div v-if="!image.id" class="absolute inset-0 bg-gray-100 flex items-center justify-center">
                        <svg class="h-8 w-8 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                      </div>
                      
                      <!-- Primary Badge -->
                      <div v-if="image.is_primary" class="absolute top-2 left-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 shadow-sm">
                          <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                          </svg>
                          Primary
                        </span>
                      </div>

                      <!-- Actions Overlay -->
                      <div class="absolute inset-0  bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200 flex items-center justify-center">
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex space-x-2">
                          <button 
                            @click="setPrimary(image)"
                            :disabled="image.is_primary || uploading"
                            class="p-2 bg-white rounded-full text-gray-600 hover:text-yellow-600 disabled:opacity-50 transition-colors shadow-lg"
                            title="Set as primary"
                          >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                          </button>
                          <button 
                            @click="deleteImage(image, index)"
                            :disabled="uploading"
                            class="p-2 bg-white rounded-full text-gray-600 hover:bg-red-600 hover:text-white hover:shadow-md hover:scale-110 disabled:opacity-50 transition-all duration-200 shadow-lg"
                            title="Delete image"
                          >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                          </button>
                        </div>
                      </div>
                    </div>

                    <!-- Alt Text Input -->
                    <div class="p-3">
                      <input 
                        v-model="image.alt_text"
                        type="text"
                        placeholder="Add alt text..."
                        class="w-full text-xs border-gray-200 rounded-lg px-2 py-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        @blur="updateImage(image)"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Empty State -->
              <div v-else class="text-center py-12">
                <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-gray-100 mb-4">
                  <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No images yet</h3>
                <p class="text-gray-500 mb-4">Upload some images to showcase this product.</p>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
            <div class="flex justify-end">
              <button 
                @click="closeModal"
                type="button" 
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Done
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch, onMounted } from 'vue'
import { useToast } from 'vue-toastification'

export default {
  name: 'ProductImagesModal',
  props: {
    isOpen: {
      type: Boolean,
      default: false
    },
    product: {
      type: Object,
      default: null
    }
  },
  emits: ['close', 'primary-changed'],
  setup(props, { emit }) {
    const toast = useToast()
    const images = ref([])
    const uploading = ref(false)

    const closeModal = () => {
      emit('close')
    }

    const getImageUrl = (image) => {
      // For newly uploaded files that haven't been saved yet
      if (image.file) {
        return URL.createObjectURL(image.file)
      }
      // Use image_url from the API (always available via $appends)
      if (image.image_url) {
        return image.image_url
      }
      // Placeholder if no image available
      return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik04MCA4MEgxMjBWMTIwSDgwVjgwWiIgZmlsbD0iI0QxRDVEQyIvPgo8cGF0aCBkPSJNODAgMTAwSDEyMFYxMjBIODBWMTAwWiIgZmlsbD0iI0QxRDVEQyIvPgo8cGF0aCBkPSJNODAgMTIwSDEyMFYxNDBIODBWMTIwWiIgZmlsbD0iI0QxRDVEQyIvPgo8L3N2Zz4K'
    }

    const handleImageError = (event) => {
      // Create a simple placeholder using data URL
      event.target.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik04MCA4MEgxMjBWMTIwSDgwVjgwWiIgZmlsbD0iI0QxRDVEQyIvPgo8cGF0aCBkPSJNODAgMTAwSDEyMFYxMjBIODBWMTAwWiIgZmlsbD0iI0QxRDVEQyIvPgo8cGF0aCBkPSJNODAgMTIwSDEyMFYxNDBIODBWMTIwWiIgZmlsbD0iI0QxRDVEQyIvPgo8L3N2Zz4K'
    }

    const fetchImages = async () => {
      if (!props.product?.id) return

      try {
        const response = await window.axios.get('/api/product-images', {
          params: { product_id: props.product.id }
        })
        images.value = response.data
      } catch (error) {
        console.error('Error fetching images:', error)
        toast.error('Failed to load images')
      }
    }

    const handleFileSelect = async (event) => {
      const files = Array.from(event.target.files)
      if (files.length === 0) return

      // Validate files
      const maxSize = 2 * 1024 * 1024 // 2MB
      const validFiles = files.filter(file => {
        if (file.size > maxSize) {
          toast.error(`File ${file.name} is too large. Maximum size is 2MB.`)
          return false
        }
        if (!file.type.startsWith('image/')) {
          toast.error(`File ${file.name} is not an image.`)
          return false
        }
        return true
      })

      if (validFiles.length === 0) return

      // Add temp images for preview
      const tempImages = validFiles.map(file => ({
        file,
        alt_text: '',
        is_primary: false,
        status: 'uploading'
      }))
      
      images.value = [...images.value, ...tempImages]

      await uploadImages(validFiles)
    }

    const uploadImages = async (files) => {
      uploading.value = true
      // Detect if this is the first upload (no existing persisted images)
      const hadExistingImages = images.value.some(img => !!img.id)

      try {
        const formData = new FormData()
        formData.append('product_id', props.product.id)
        
        files.forEach((file, index) => {
          formData.append('images[]', file)
        })

        const response = await window.axios.post('/api/product-images', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        toast.success('Images uploaded successfully!')
        await fetchImages() // Refresh the images list

        // If no images existed before upload, ensure a primary is announced
        if (!hadExistingImages && images.value.length > 0) {
          const first = images.value[0]
          if (first) {
            if (!first.is_primary) {
              await setPrimary(first)
            } else {
              // Already primary by backend: notify parent immediately
              emit('primary-changed', {
                productId: props.product.id,
                image: { ...first }
              })
            }
          }
        }
      } catch (error) {
        console.error('Error uploading images:', error)
        toast.error('Failed to upload images')
        
        // Remove temp images on error
        images.value = images.value.filter(img => img.id)
      } finally {
        uploading.value = false
      }
    }

    const setPrimary = async (image) => {
      if (image.is_primary) return

      try {
        const resp = await window.axios.put(`/api/product-images/${image.id}`, {
          is_primary: true
        })

        // Update local state
        images.value.forEach(img => {
          img.is_primary = img.id === image.id
        })

        toast.success('Primary image updated!')

        // Notify parent so product list can update immediately
        // Prefer server-returned image (with fresh fields like image_url)
        const updated = resp?.data?.image ? { ...resp.data.image, is_primary: true } : { ...image, is_primary: true }
        emit('primary-changed', {
          productId: props.product.id,
          image: updated
        })
      } catch (error) {
        console.error('Error setting primary image:', error)
        toast.error('Failed to update primary image')
      }
    }

    const updateImage = async (image) => {
      if (!image.id) return // Skip temp images

      try {
        await window.axios.put(`/api/product-images/${image.id}`, {
          alt_text: image.alt_text
        })
      } catch (error) {
        console.error('Error updating image:', error)
        toast.error('Failed to update image')
      }
    }

    const deleteImage = async (image, index) => {
      if (!confirm('Are you sure you want to delete this image?')) return

      try {
        if (image.id) {
          await window.axios.delete(`/api/product-images/${image.id}`)
        }
        
        images.value.splice(index, 1)
        toast.success('Image deleted successfully!')
      } catch (error) {
        console.error('Error deleting image:', error)
        toast.error('Failed to delete image')
      }
    }

    // Watch for product changes
    watch(() => props.product, (newProduct) => {
      if (newProduct && props.isOpen) {
        fetchImages()
      }
    }, { immediate: true })

    // Watch for modal open/close
    watch(() => props.isOpen, (isOpen) => {
      if (isOpen && props.product) {
        fetchImages()
      }
    })

    return {
      images,
      uploading,
      closeModal,
      getImageUrl,
      handleImageError,
      handleFileSelect,
      setPrimary,
      updateImage,
      deleteImage
    }
  }
}
</script>