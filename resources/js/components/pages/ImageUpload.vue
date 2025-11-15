<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Upload Images</h1>
            <p class="mt-1 text-sm text-gray-600">Upload images without linking them to a product. You can attach them later from the gallery.</p>
          </div>
          <div class="flex items-center space-x-3">
            <button 
              @click="$router.push('/images/gallery')"
              class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              View Gallery
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <!-- Upload Section -->
      <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-8 border-2 border-dashed border-blue-200 hover:border-blue-300 transition-all duration-200">
          <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 mb-4">
              <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Upload Images</h3>
            <p class="text-sm text-gray-600 mb-4">Select multiple images to upload</p>
            <div class="flex justify-center">
              <label for="file-upload" class="relative cursor-pointer bg-white rounded-md px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500 transition-colors">
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
            <p class="mt-3 text-xs text-gray-500">PNG, JPG, GIF up to 2MB each. Maximum 10 files at once.</p>
          </div>
        </div>

        <!-- Upload Progress -->
        <div v-if="uploading" class="mt-6">
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-blue-700">Uploading {{ selectedFiles.length }} image(s)...</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Selected Files Preview -->
        <div v-if="selectedFiles.length > 0 && !uploading" class="mt-6">
          <h4 class="text-sm font-medium text-gray-900 mb-3">Selected Files ({{ selectedFiles.length }})</h4>
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <div 
              v-for="(file, index) in selectedFiles" 
              :key="index"
              class="relative group bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
            >
              <img 
                :src="file.preview" 
                :alt="file.name"
                class="w-full h-32 object-cover"
              />
              <div class="p-2">
                <p class="text-xs text-gray-600 truncate" :title="file.name">{{ file.name }}</p>
                <p class="text-xs text-gray-400">{{ formatFileSize(file.size) }}</p>
              </div>
              <button
                @click="removeFile(index)"
                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>
          <div class="mt-4 flex justify-end space-x-3">
            <button
              @click="clearSelection"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Clear Selection
            </button>
            <button
              @click="uploadImages"
              :disabled="uploading"
              class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Upload {{ selectedFiles.length }} Image(s)
            </button>
          </div>
        </div>
      </div>

      <!-- Recently Uploaded -->
      <div v-if="recentUploads.length > 0" class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Recently Uploaded</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <div 
            v-for="image in recentUploads" 
            :key="image.id"
            class="relative group bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
          >
            <img 
              :src="image.image_url" 
              :alt="image.alt_text || 'Uploaded image'"
              class="w-full h-32 object-cover"
            />
            <div class="p-2">
              <p class="text-xs text-gray-500 truncate" :title="image.alt_text || 'No description'">
                {{ image.alt_text || 'No description' }}
              </p>
              <p class="text-xs text-gray-400 mt-1">{{ formatDate(image.created_at) }}</p>
            </div>
            <div v-if="!image.product_id" class="absolute top-2 left-2 bg-yellow-400 text-yellow-900 text-xs px-2 py-1 rounded">
              Unattached
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'

const toast = useToast()

const selectedFiles = ref([])
const uploading = ref(false)
const recentUploads = ref([])

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)
  
  if (files.length === 0) return

  // Validate files
  const maxSize = 2 * 1024 * 1024 // 2MB
  const maxFiles = 10
  
  if (files.length > maxFiles) {
    toast.error(`Maximum ${maxFiles} files allowed at once`)
    return
  }

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

  // Create preview URLs
  validFiles.forEach(file => {
    const preview = URL.createObjectURL(file)
    selectedFiles.value.push({
      file,
      name: file.name,
      size: file.size,
      preview
    })
  })
}

const removeFile = (index) => {
  URL.revokeObjectURL(selectedFiles.value[index].preview)
  selectedFiles.value.splice(index, 1)
}

const clearSelection = () => {
  selectedFiles.value.forEach(item => {
    URL.revokeObjectURL(item.preview)
  })
  selectedFiles.value = []
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const uploadImages = async () => {
  if (selectedFiles.value.length === 0) return

  uploading.value = true

  try {
    const formData = new FormData()
    
    selectedFiles.value.forEach((item) => {
      formData.append('images[]', item.file)
    })

    const response = await window.axios.post('/api/product-images', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    toast.success(`${response.data.images.length} image(s) uploaded successfully!`)
    
    // Add to recent uploads
    recentUploads.value = [...response.data.images, ...recentUploads.value].slice(0, 12)
    
    // Clear selection
    clearSelection()
    
    // Reset file input
    const fileInput = document.getElementById('file-upload')
    if (fileInput) {
      fileInput.value = ''
    }
  } catch (error) {
    console.error('Upload error:', error)
    toast.error(error.response?.data?.message || 'Failed to upload images')
  } finally {
    uploading.value = false
  }
}

const fetchRecentUploads = async () => {
  try {
    const response = await window.axios.get('/api/product-images?unattached=true')
    recentUploads.value = response.data.slice(0, 12)
  } catch (error) {
    console.error('Error fetching recent uploads:', error)
  }
}

onMounted(() => {
  fetchRecentUploads()
})
</script>

