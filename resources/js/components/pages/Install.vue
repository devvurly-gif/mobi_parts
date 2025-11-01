<template>
  <div class="min-h-screen bg-gray-50 py-10 px-4">
    <div class="max-w-xl mx-auto bg-white shadow rounded-lg p-6 relative">
      <h1 class="text-2xl font-bold text-gray-900">First-time Setup</h1>
      <p class="text-gray-600 mt-1">Define basic settings, then run installation.</p>

      <fieldset :disabled="saving || installing" class="mt-6 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Application Name</label>
          <input v-model="form.app_name" type="text" class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="My Inventory App" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Database Name</label>
          <input v-model="form.db_database" type="text" class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="mobi_part_apps" />
        </div>
        <div class="pt-2 border-t border-gray-200"/>
        <div>
          <label class="block text-sm font-medium text-gray-700">Admin Name</label>
          <input v-model="form.admin_name" type="text" class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Master" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Admin Email</label>
          <input v-model="form.admin_email" type="email" class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="master@example.com" />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Admin Password</label>
            <input v-model="form.admin_password" type="password" class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input v-model="form.admin_password_confirmation" type="password" class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
          </div>
        </div>
      </fieldset>

      <div class="mt-6 flex items-center space-x-3">
        <button @click="saveConfig" :disabled="saving" class="inline-flex items-center px-4 py-2 rounded-md text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50">
          <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0" />
          </svg>
          <span>{{ saving ? 'Processing...' : 'Save Config' }}</span>
        </button>
        <button @click="runInstall" :disabled="installing" class="inline-flex items-center px-4 py-2 rounded-md text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50">
          <svg v-if="installing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0" />
          </svg>
          Run Install
        </button>
        <button @click="checkStatus" class="inline-flex items-center px-4 py-2 rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200">
          Check Status
        </button>
      </div>

      <!-- Saving overlay -->
      <div v-if="saving" class="absolute inset-0 bg-white/60 flex items-center justify-center rounded-lg">
        <div class="flex items-center space-x-2 text-indigo-700">
          <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0" />
          </svg>
          <span class="text-sm font-medium">Saving configuration...</span>
        </div>
      </div>

      <div v-if="message" class="mt-4 text-sm" :class="{ 'text-green-700': ok, 'text-red-700': !ok }">{{ message }}</div>

      <!-- Actions preview -->
      <div v-if="actionList.length" class="mt-4 space-y-3">
        <div v-for="action in actionList" :key="action.key" class="border rounded-md">
          <div class="flex items-center justify-between px-3 py-2">
            <div class="flex items-center space-x-2">
              <span class="inline-flex items-center justify-center w-5 h-5 rounded-full"
                    :class="action.success ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'">
                <svg v-if="action.success" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <svg v-else class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2a10 10 0 1010 10A10.011 10.011 0 0012 2zm1 15h-2v-2h2zm0-4h-2V7h2z"/>
                </svg>
              </span>
              <span class="text-sm font-medium text-gray-800">{{ action.label }}</span>
            </div>
            <span class="text-xs text-gray-500">exit {{ action.exit_code }}</span>
          </div>
          <div v-if="action.output || action.error" class="px-3 pb-3">
            <pre class="text-xs bg-gray-50 border border-gray-200 rounded p-2 overflow-auto max-h-48 whitespace-pre-wrap">{{ action.error || action.output }}</pre>
          </div>
        </div>
      </div>

      <div v-if="details" class="mt-4 text-xs bg-gray-50 border border-gray-200 rounded p-3 whitespace-pre-wrap">
        {{ details }}
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { clearInstallStatusCache } from '../../router/index.js'
export default {
  name: 'Install',
  data() {
    return {
      form: {
        app_name: '',
        db_database: '',
        admin_name: '',
        admin_email: '',
        admin_password: '',
        admin_password_confirmation: ''
      },
      saving: false,
      installing: false,
      message: '',
      ok: true,
      details: ''
      ,
      steps: null
    }
  },
  computed: {
    actionList() {
      if (!this.steps) return []
      const order = ['migrate', 'storage_link', 'seed']
      const labels = {
        migrate: 'Run migrations',
        storage_link: 'Create storage symlink',
        seed: 'Seed database'
      }
      return order
        .filter(k => this.steps[k])
        .map(k => {
          const s = this.steps[k] || {}
          const exit = typeof s.exit_code === 'number' ? s.exit_code : 0
          return {
            key: k,
            label: labels[k] || k,
            success: exit === 0,
            exit_code: exit,
            output: s.output || '',
            error: s.error || ''
          }
        })
    }
  },
  methods: {
   
    async saveConfig() {
      this.saving = true
      this.message = ''
      this.details = ''
      try {
        const res = await axios.post('/api/install/config', this.form, {
          timeout: 30000,
          headers: { 'Content-Type': 'application/json' },
          transformRequest: [(data, headers) => { try { delete headers.Authorization } catch(e){}; return JSON.stringify(data) }]
        })
        this.ok = true
        this.message = res.data.message || 'Saved.'
        // Keep form persisted until install completes
      } catch (e) {
        console.error('Install.vue saveConfig error:', e)
        this.ok = false
        this.message = e?.response?.data?.message || 'Failed to save config'
        this.details = JSON.stringify(e?.response?.data || {}, null, 2)
        // Do not auto-reload; preserve form state
      } finally {
        this.saving = false
      }
    },
    async runInstall() {
      this.installing = true
      this.message = ''
      this.details = ''
      try {
        const res = await axios.post('/api/install', {}, {
          timeout: 60000,
          headers: { 'Content-Type': 'application/json' },
          transformRequest: [(data, headers) => { try { delete headers.Authorization } catch(e){}; return JSON.stringify(data) }]
        })
        this.ok = true
        this.message = res.data.message || 'Installed.'
        this.steps = res.data.steps || {}
        this.details = ''
        // Clear persisted form after successful installation
        try { localStorage.removeItem('installForm') } catch (e) {}
        // Clear install status cache to ensure router recognizes installation
        clearInstallStatusCache()
        // Redirect to login after successful installation
        setTimeout(() => {
          this.$router.push('/login')
        }, 1000)
      } catch (e) {
        console.error('Install.vue runInstall error:', e)
        this.ok = false
        this.message = e?.response?.data?.message || 'Install failed'
        this.steps = e?.response?.data?.steps || null
        this.details = JSON.stringify(e?.response?.data || {}, null, 2)
        // Do not auto-reload; preserve form state for retry
      } finally {
        this.installing = false
      }
    },
    async checkStatus() {
      this.message = ''
      this.details = ''
      try {
        const res = await axios.get('/api/install/status', { timeout: 10000 })
        this.ok = !!res.data.installed
        this.message = this.ok ? 'Installed' : 'Not installed'
        this.details = JSON.stringify(res.data, null, 2)
      } catch (e) {
        console.error('Install.vue checkStatus error:', e)
        this.ok = false
        this.message = 'Status check failed'
      }
    }
  },
  mounted() {
    // Restore persisted form data
    try {
      const saved = localStorage.getItem('installForm')
      if (saved) {
        const parsed = JSON.parse(saved)
        this.form = { ...this.form, ...parsed }
      }
    } catch (e) {}
  },
  watch: {
    form: {
      handler(val) {
        try { localStorage.setItem('installForm', JSON.stringify(val)) } catch (e) {}
      },
      deep: true
    }
  }
}
</script>
