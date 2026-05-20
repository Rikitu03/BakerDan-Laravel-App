<template>
  <div class="p-8">
    <!-- User Profile Header -->
    <div class="bg-gray-100 rounded-2xl p-8 mb-8 flex items-center gap-6">
      <div class="w-24 h-24 rounded-full bg-gradient-to-br from-[#C9876C] to-[#B8765B] flex items-center justify-center text-white text-3xl font-bold shadow-lg">
        {{ user.avatar }}
      </div>
      <div>
        <h2 class="text-3xl font-bold text-gray-800 mb-1" style="font-family: 'Urbanist', sans-serif;">
          {{ user.name }}
        </h2>
        <p class="text-gray-600">{{ user.email }}</p>
      </div>
    </div>

    <!-- Settings Container -->
    <div class="flex gap-8">
      <!-- Sidebar Tabs -->
      <div class="w-64 flex-shrink-0">
        <div class="space-y-2">
          <button
            @click="activeTab = 'personal'"
            class="w-full flex items-center gap-3 px-6 py-3 rounded-lg transition-all duration-200 text-left"
            :class="activeTab === 'personal' 
              ? 'bg-[#C9876C] text-white shadow-md' 
              : 'text-gray-700 hover:bg-gray-100'"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span class="font-medium">Personal Info</span>
          </button>

          <button
            @click="activeTab = 'security'"
            class="w-full flex items-center gap-3 px-6 py-3 rounded-lg transition-all duration-200 text-left"
            :class="activeTab === 'security' 
              ? 'bg-[#C9876C] text-white shadow-md' 
              : 'text-gray-700 hover:bg-gray-100'"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <span class="font-medium">Security</span>
          </button>

          <button
            @click="activeTab = 'preferences'"
            class="w-full flex items-center gap-3 px-6 py-3 rounded-lg transition-all duration-200 text-left"
            :class="activeTab === 'preferences' 
              ? 'bg-[#C9876C] text-white shadow-md' 
              : 'text-gray-700 hover:bg-gray-100'"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="font-medium">Preferences</span>
          </button>
        </div>
      </div>

      <!-- Content Area -->
      <div class="flex-1">
        <!-- Personal Info Tab -->
        <div v-if="activeTab === 'personal'" class="bg-white rounded-2xl p-8 shadow-md">
          <h3 class="text-2xl font-bold text-gray-800 mb-6">Personal Info</h3>
          
          <form @submit.prevent="savePersonalInfo" class="space-y-6">
            <div class="grid grid-cols-2 gap-6">
              <!-- Full Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full name</label>
                <input
                  v-model="personalInfo.fullName"
                  :disabled="!isEditingPersonal"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none transition-colors"
                  :class="isEditingPersonal 
                    ? 'focus:ring-2 focus:ring-[#C9876C] focus:border-transparent cursor-text' 
                    : 'bg-gray-100 cursor-not-allowed text-gray-600'"
                  placeholder="Jason Jay Recto"
                />
              </div>

              <!-- Phone Number -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone number</label>
                <input
                  v-model="personalInfo.phoneNumber"
                  :disabled="!isEditingPersonal"
                  type="tel"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none transition-colors"
                  :class="isEditingPersonal 
                    ? 'focus:ring-2 focus:ring-[#C9876C] focus:border-transparent cursor-text' 
                    : 'bg-gray-100 cursor-not-allowed text-gray-600'"
                  placeholder="09391654377"
                />
              </div>
            </div>

            <!-- Delivery Address -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Address</label>
              <input
                v-model="personalInfo.deliveryAddress"
                :disabled="!isEditingPersonal"
                type="text"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none transition-colors"
                :class="isEditingPersonal 
                  ? 'focus:ring-2 focus:ring-[#C9876C] focus:border-transparent cursor-text' 
                  : 'bg-gray-100 cursor-not-allowed text-gray-600'"
                placeholder="123 Sourdough Street, La Vista Ville, Pinagbuhatan, Pasig"
              />
            </div>

            <!-- Loading Error Message -->
            <div v-if="personalInfoError" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
              {{ personalInfoError }}
            </div>

            <!-- Success Message -->
            <div v-if="personalInfoSuccess" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
              {{ personalInfoSuccess }}
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3">
              <button
                v-if="isEditingPersonal"
                type="button"
                @click="cancelEditingPersonal"
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-8 py-3 rounded-lg transition-colors shadow-md"
              >
                Cancel
              </button>
              <button
                v-if="!isEditingPersonal"
                type="button"
                @click="isEditingPersonal = true"
                class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-semibold px-8 py-3 rounded-lg transition-colors shadow-md"
              >
                Edit
              </button>
              <button
                v-if="isEditingPersonal"
                type="submit"
                :disabled="isSavingPersonal"
                class="bg-[#C9876C] hover:bg-[#B8765B] disabled:bg-gray-400 text-white font-semibold px-8 py-3 rounded-lg transition-colors shadow-md disabled:cursor-not-allowed"
              >
                {{ isSavingPersonal ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Security Tab -->
        <div v-if="activeTab === 'security'" class="bg-white rounded-2xl p-8 shadow-md">
          <h3 class="text-2xl font-bold text-gray-800 mb-6">Security</h3>
          
          <form @submit.prevent="saveSecuritySettings" class="space-y-6">
            <!-- Current Password -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
              <div class="relative">
                <input
                  v-model="securityInfo.currentPassword"
                  @input="validateCurrentPassword"
                  :disabled="!isEditingSecurity"
                  :type="showCurrentPassword ? 'text' : 'password'"
                  class="w-full px-4 py-3 pr-12 border rounded-lg outline-none transition-colors"
                  :class="isEditingSecurity ? [
                    currentPasswordStatus === 'correct' ? 'border-green-500 focus:ring-2 focus:ring-green-500' : 
                    currentPasswordStatus === 'incorrect' ? 'border-red-500 focus:ring-2 focus:ring-red-500' :
                    'border-gray-300 focus:ring-2 focus:ring-[#C9876C] focus:border-transparent'
                  ] : 'bg-gray-100 cursor-not-allowed text-gray-600 border-gray-300'"
                  placeholder="••••••••"
                />
                <button
                  v-if="isEditingSecurity"
                  type="button"
                  @click="showCurrentPassword = !showCurrentPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                >
                  <svg v-if="!showCurrentPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                </button>
              </div>
              <p v-if="currentPasswordStatus === 'incorrect' && isEditingSecurity" class="text-red-500 text-xs mt-1">
                Incorrect Password
              </p>
              <p v-else-if="isValidatingPassword && isEditingSecurity" class="text-[#A06F50] text-xs mt-1">
                Checking password...
              </p>
            </div>

            <!-- New Password -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
              <div class="relative">
                <input
                  v-model="securityInfo.newPassword"
                  @input="validatePasswordMatch"
                  :disabled="!isEditingSecurity || currentPasswordStatus !== 'correct'"
                  :type="showNewPassword ? 'text' : 'password'"
                  class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg outline-none transition-colors"
                  :class="isEditingSecurity && currentPasswordStatus === 'correct'
                    ? 'focus:ring-2 focus:ring-[#C9876C] focus:border-transparent cursor-text' 
                    : 'bg-gray-100 cursor-not-allowed text-gray-600'"
                  placeholder="••••••••"
                />
                <button
                  v-if="isEditingSecurity && currentPasswordStatus === 'correct'"
                  type="button"
                  @click="showNewPassword = !showNewPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                >
                  <svg v-if="!showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Confirm New Password -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
              <div class="relative">
                <input
                  v-model="securityInfo.confirmPassword"
                  @input="validatePasswordMatch"
                  :disabled="!isEditingSecurity || currentPasswordStatus !== 'correct'"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  class="w-full px-4 py-3 pr-12 border rounded-lg outline-none transition-colors"
                  :class="isEditingSecurity && currentPasswordStatus === 'correct' ? [
                    passwordsMatch === false ? 'border-red-500 focus:ring-2 focus:ring-red-500' :
                    'border-gray-300 focus:ring-2 focus:ring-[#C9876C] focus:border-transparent'
                  ] : 'bg-gray-100 cursor-not-allowed text-gray-600 border-gray-300'"
                  placeholder="••••••••"
                />
                <button
                  v-if="isEditingSecurity && currentPasswordStatus === 'correct'"
                  type="button"
                  @click="showConfirmPassword = !showConfirmPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                >
                  <svg v-if="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                </button>
              </div>
              <p v-if="passwordsMatch === false && isEditingSecurity && securityInfo.confirmPassword" class="text-red-500 text-xs mt-1">
                Passwords do not match
              </p>
            </div>

            <!-- Password Strength Indicator -->
            <div v-if="securityInfo.newPassword && isEditingSecurity && currentPasswordStatus === 'correct'" class="space-y-2">
              <div class="flex gap-1">
                <div 
                  v-for="i in 4" 
                  :key="i"
                  class="h-1 flex-1 rounded-full transition-colors"
                  :class="i <= passwordStrength ? 'bg-[#C9876C]' : 'bg-gray-200'"
                ></div>
              </div>
              <p class="text-sm text-gray-600">
                Password strength: 
                <span class="font-medium" :class="{
                  'text-red-500': passwordStrength <= 1,
                  'text-yellow-500': passwordStrength === 2,
                  'text-green-500': passwordStrength >= 3
                }">
                  {{ passwordStrengthText }}
                </span>
              </p>
            </div>

            <!-- Error/Success Message -->
            <div v-if="securityError" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
              {{ securityError }}
            </div>
            <div v-if="securitySuccess" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
              {{ securitySuccess }}
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3">
              <button
                v-if="isEditingSecurity"
                type="button"
                @click="cancelEditingSecurity"
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-8 py-3 rounded-lg transition-colors shadow-md"
              >
                Cancel
              </button>
              <button
                v-if="!isEditingSecurity"
                type="button"
                @click="isEditingSecurity = true"
                class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-semibold px-8 py-3 rounded-lg transition-colors shadow-md"
              >
                Edit
              </button>
              <button
                v-if="isEditingSecurity"
                type="submit"
                :disabled="isSavingSecurity || currentPasswordStatus !== 'correct' || passwordsMatch === false"
                class="bg-[#C9876C] hover:bg-[#B8765B] disabled:bg-gray-400 text-white font-semibold px-8 py-3 rounded-lg transition-colors shadow-md disabled:cursor-not-allowed"
              >
                {{ isSavingSecurity ? 'Updating...' : 'Update Password' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Preferences Tab -->
        <div v-if="activeTab === 'preferences'" class="bg-white rounded-2xl p-8 shadow-md">
          <h3 class="text-2xl font-bold text-gray-800 mb-6">Preferences</h3>
          <p class="text-gray-600">Preference settings coming soon...</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'
import api from '../../services/api'

const PASSWORD_VERIFY_DEBOUNCE_MS = 350

const props = defineProps({
  user: {
    type: Object,
    default: () => ({
      name: 'Jason Jay Recto',
      email: 'recto_jasonjay@plpasig.edu.ph',
      avatar: 'JR'
    })
  },
  tab: {
    type: String,
    default: 'personal'
  }
})

const activeTab = ref(props.tab)

const personalInfo = ref({
  fullName: 'Jason Jay Recto',
  phoneNumber: '09391654377',
  deliveryAddress: '123 Sourdough Street, La Vista Ville, Pinagbuhatan, Pasig'
})

// Personal Info Edit State
const isEditingPersonal = ref(false)
const isSavingPersonal = ref(false)
const personalInfoError = ref('')
const personalInfoSuccess = ref('')
const originalPersonalInfo = ref({ ...personalInfo.value })

const securityInfo = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

// Security Edit State
const isEditingSecurity = ref(false)
const isSavingSecurity = ref(false)
const securityError = ref('')
const securitySuccess = ref('')
const currentPasswordStatus = ref('') // 'correct', 'incorrect', or ''
const passwordsMatch = ref(null) // true, false, or null
const isValidatingPassword = ref(false)
const passwordVerificationCache = new Map()
let passwordVerificationTimer = null
let passwordVerificationRequestId = 0

const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const passwordStrength = computed(() => {
  const password = securityInfo.value.newPassword
  if (!password) return 0
  
  let strength = 0
  if (password.length >= 8) strength++
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++
  if (/\d/.test(password)) strength++
  if (/[^a-zA-Z\d]/.test(password)) strength++
  
  return strength
})

const passwordStrengthText = computed(() => {
  const strength = passwordStrength.value
  if (strength <= 1) return 'Weak'
  if (strength === 2) return 'Fair'
  if (strength === 3) return 'Good'
  return 'Strong'
})

const cancelEditingPersonal = () => {
  isEditingPersonal.value = false
  personalInfo.value = { ...originalPersonalInfo.value }
  personalInfoError.value = ''
  personalInfoSuccess.value = ''
}

const savePersonalInfo = async () => {
  personalInfoError.value = ''
  personalInfoSuccess.value = ''
  isSavingPersonal.value = true

  try {
    const response = await api.updateProfile({
      full_name: personalInfo.value.fullName,
      phone_number: personalInfo.value.phoneNumber,
      delivery_address: personalInfo.value.deliveryAddress,
    })

    if (response.data.success) {
      originalPersonalInfo.value = { ...personalInfo.value }
      personalInfoSuccess.value = response.data.message || 'Profile updated successfully'
      isEditingPersonal.value = false
      
      // Clear success message after 3 seconds
      setTimeout(() => {
        personalInfoSuccess.value = ''
      }, 3000)
    } else {
      personalInfoError.value = response.data.message || 'Failed to update profile'
    }
  } catch (error) {
    personalInfoError.value = error.response?.data?.message || 'An error occurred while saving profile'
    console.error('Profile update error:', error)
  } finally {
    isSavingPersonal.value = false
  }
}

const clearPasswordVerificationTimer = () => {
  if (!passwordVerificationTimer) {
    return
  }

  clearTimeout(passwordVerificationTimer)
  passwordVerificationTimer = null
}

const validateCurrentPassword = () => {
  const password = securityInfo.value.currentPassword

  clearPasswordVerificationTimer()
  passwordVerificationRequestId += 1

  if (!password) {
    currentPasswordStatus.value = ''
    isValidatingPassword.value = false
    return
  }

  if (passwordVerificationCache.has(password)) {
    currentPasswordStatus.value = passwordVerificationCache.get(password)
    isValidatingPassword.value = false
    validatePasswordMatch()
    return
  }

  currentPasswordStatus.value = ''
  isValidatingPassword.value = true
  const requestId = passwordVerificationRequestId

  passwordVerificationTimer = setTimeout(() => {
    verifyCurrentPassword(password, requestId)
  }, PASSWORD_VERIFY_DEBOUNCE_MS)
}

const verifyCurrentPassword = async (password, requestId) => {
  try {
    const response = await api.verifyCurrentPassword({
      current_password: password
    })

    if (requestId !== passwordVerificationRequestId || password !== securityInfo.value.currentPassword) {
      return
    }

    const nextStatus = response.data.success ? 'correct' : 'incorrect'
    passwordVerificationCache.set(password, nextStatus)
    currentPasswordStatus.value = nextStatus
    validatePasswordMatch()
  } catch (error) {
    if (requestId !== passwordVerificationRequestId || password !== securityInfo.value.currentPassword) {
      return
    }

    passwordVerificationCache.set(password, 'incorrect')
    currentPasswordStatus.value = 'incorrect'
  } finally {
    if (requestId === passwordVerificationRequestId) {
      isValidatingPassword.value = false
    }
  }
}

const validatePasswordMatch = () => {
  if (!securityInfo.value.newPassword || !securityInfo.value.confirmPassword) {
    passwordsMatch.value = null
    return
  }

  passwordsMatch.value = securityInfo.value.newPassword === securityInfo.value.confirmPassword
}

const cancelEditingSecurity = () => {
  clearPasswordVerificationTimer()
  passwordVerificationRequestId += 1
  isEditingSecurity.value = false
  securityInfo.value = {
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  }
  currentPasswordStatus.value = ''
  passwordsMatch.value = null
  securityError.value = ''
  securitySuccess.value = ''
  isValidatingPassword.value = false
}

const saveSecuritySettings = async () => {
  if (currentPasswordStatus.value !== 'correct') {
    securityError.value = 'Please verify your current password first'
    return
  }

  if (passwordsMatch.value !== true) {
    securityError.value = 'Passwords do not match'
    return
  }

  securityError.value = ''
  securitySuccess.value = ''
  isSavingSecurity.value = true

  try {
    const response = await api.updatePassword({
      current_password: securityInfo.value.currentPassword,
      new_password: securityInfo.value.newPassword,
      new_password_confirmation: securityInfo.value.confirmPassword,
    })

    if (response.data.success) {
      securitySuccess.value = response.data.message || 'Password updated successfully'
      isEditingSecurity.value = false
      securityInfo.value = {
        currentPassword: '',
        newPassword: '',
        confirmPassword: ''
      }
      passwordVerificationCache.clear()
      currentPasswordStatus.value = ''
      passwordsMatch.value = null

      // Clear success message after 3 seconds
      setTimeout(() => {
        securitySuccess.value = ''
      }, 3000)
    } else {
      securityError.value = response.data.message || 'Failed to update password'
    }
  } catch (error) {
    securityError.value = error.response?.data?.message || 'An error occurred while updating password'
    console.error('Password update error:', error)
  } finally {
    isSavingSecurity.value = false
  }
}

onBeforeUnmount(() => {
  clearPasswordVerificationTimer()
  passwordVerificationRequestId += 1
})
</script>
