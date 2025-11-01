<template>
  <div id="app">
    <!-- Navbar -->
    <Navbar v-if="isAuthenticated" />

    <!-- Main Content -->
    <main class="min-h-screen bg-gray-50">
      <router-view />
    </main>
  </div>
</template>

<script>
import { computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/authStore'
import Navbar from './Navbar.vue'

export default {
  name: 'App',
  components: {
    Navbar
  },
  setup() {
    const authStore = useAuthStore()
    
    const isAuthenticated = computed(() => authStore.isAuthenticated)
    
    // Check authentication on app mount
    onMounted(async () => {
      await authStore.checkAuth()
    })
    
    return {
      isAuthenticated
    }
  }
}
</script>
