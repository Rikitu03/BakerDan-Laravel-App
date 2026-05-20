<template>
  <div class="mx-auto w-full max-w-6xl p-4 sm:p-6 lg:p-8">
    <!-- User Profile Header -->
    <div class="mb-6 flex flex-col items-center gap-4 rounded-2xl bg-gray-100 p-5 text-center sm:flex-row sm:gap-6 sm:p-6 sm:text-left lg:mb-8 lg:p-8">
      <div class="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#C9876C] to-[#B8765B] text-2xl font-bold text-white shadow-lg sm:h-24 sm:w-24 sm:text-3xl">
        {{ user.avatar }}
      </div>
      <div class="min-w-0">
        <h2 class="mb-1 break-words text-2xl font-bold text-gray-800 sm:text-3xl" style="font-family: 'Urbanist', sans-serif;">
          {{ user.name }}
        </h2>
        <p class="break-all text-sm text-gray-600 sm:text-base">{{ user.email }}</p>
      </div>
    </div>

    <!-- Settings Container -->
    <div class="flex flex-col gap-4 sm:gap-6 lg:flex-row lg:gap-8">
      <!-- Sidebar Tabs -->
      <div class="w-full lg:w-64 lg:flex-shrink-0">
        <div class="grid grid-cols-3 gap-2 lg:block lg:space-y-2">
          <button
            @click="activeTab = 'personal'"
            class="flex min-h-[4rem] w-full min-w-0 flex-col items-center justify-center gap-1 rounded-lg px-2 py-3 text-center text-xs transition-all duration-200 sm:min-h-[3.25rem] sm:flex-row sm:gap-3 sm:px-4 sm:text-sm lg:justify-start lg:px-6 lg:text-left lg:text-base"
            :class="activeTab === 'personal' 
              ? 'bg-[#C9876C] text-white shadow-md' 
              : 'text-gray-700 hover:bg-gray-100'"
          >
            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span class="min-w-0 font-medium leading-tight">Personal Info</span>
          </button>

          <button
            @click="activeTab = 'security'"
            class="flex min-h-[4rem] w-full min-w-0 flex-col items-center justify-center gap-1 rounded-lg px-2 py-3 text-center text-xs transition-all duration-200 sm:min-h-[3.25rem] sm:flex-row sm:gap-3 sm:px-4 sm:text-sm lg:justify-start lg:px-6 lg:text-left lg:text-base"
            :class="activeTab === 'security' 
              ? 'bg-[#C9876C] text-white shadow-md' 
              : 'text-gray-700 hover:bg-gray-100'"
          >
            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <span class="min-w-0 font-medium leading-tight">Security</span>
          </button>

          <button
            @click="activeTab = 'preferences'"
            class="flex min-h-[4rem] w-full min-w-0 flex-col items-center justify-center gap-1 rounded-lg px-2 py-3 text-center text-xs transition-all duration-200 sm:min-h-[3.25rem] sm:flex-row sm:gap-3 sm:px-4 sm:text-sm lg:justify-start lg:px-6 lg:text-left lg:text-base"
            :class="activeTab === 'preferences' 
              ? 'bg-[#C9876C] text-white shadow-md' 
              : 'text-gray-700 hover:bg-gray-100'"
          >
            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="min-w-0 font-medium leading-tight">Preferences</span>
          </button>
        </div>
      </div>

      <!-- Content Area -->
      <div class="min-w-0 flex-1">
        <!-- Personal Info Tab -->
        <div v-if="activeTab === 'personal'" class="rounded-2xl bg-white p-5 shadow-md sm:p-6 lg:p-8">
          <h3 class="mb-6 text-2xl font-bold text-gray-800">Personal Info</h3>
          <p v-if="isLoadingPersonalInfo" class="mb-4 text-sm font-medium text-[#A06F50]">
            Loading saved profile details...
          </p>
          
          <form @submit.prevent="savePersonalInfo" class="space-y-6">
            <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
              <!-- Full Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full name</label>
                <input
                  v-model="personalInfo.fullName"
                  :disabled="!isEditingPersonal || isLoadingPersonalInfo"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none transition-colors"
                  :class="isEditingPersonal 
                    ? 'focus:ring-2 focus:ring-[#C9876C] focus:border-transparent cursor-text' 
                    : 'bg-gray-100 cursor-not-allowed text-gray-600'"
                  placeholder="Enter full name"
                />
              </div>

              <!-- Phone Number -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone number</label>
                <input
                  v-model="personalInfo.phoneNumber"
                  :disabled="!isEditingPersonal || isLoadingPersonalInfo"
                  type="tel"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none transition-colors"
                  :class="isEditingPersonal 
                    ? 'focus:ring-2 focus:ring-[#C9876C] focus:border-transparent cursor-text' 
                    : 'bg-gray-100 cursor-not-allowed text-gray-600'"
                  placeholder="Enter phone number"
                />
              </div>
            </div>

            <!-- Delivery Address -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Address</label>
              <input
                v-model="personalInfo.deliveryAddress"
                :disabled="!isEditingPersonal || isLoadingPersonalInfo"
                type="text"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none transition-colors"
                :class="isEditingPersonal 
                  ? 'focus:ring-2 focus:ring-[#C9876C] focus:border-transparent cursor-text' 
                  : 'bg-gray-100 cursor-not-allowed text-gray-600'"
                placeholder="Enter delivery address"
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
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
              <button
                v-if="isEditingPersonal"
                type="button"
                @click="cancelEditingPersonal"
                class="w-full rounded-lg bg-gray-200 px-8 py-3 font-semibold text-gray-800 shadow-md transition-colors hover:bg-gray-300 sm:w-auto"
              >
                Cancel
              </button>
              <button
                v-if="!isEditingPersonal"
                type="button"
                @click="isEditingPersonal = true"
                :disabled="isLoadingPersonalInfo"
                class="w-full rounded-lg border-2 border-gray-300 bg-white px-8 py-3 font-semibold text-gray-700 shadow-md transition-colors hover:border-gray-400 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
              >
                {{ isLoadingPersonalInfo ? 'Loading...' : 'Edit' }}
              </button>
              <button
                v-if="isEditingPersonal"
                type="submit"
                :disabled="isSavingPersonal || isLoadingPersonalInfo"
                class="w-full rounded-lg bg-[#C9876C] px-8 py-3 font-semibold text-white shadow-md transition-colors hover:bg-[#B8765B] disabled:cursor-not-allowed disabled:bg-gray-400 sm:w-auto"
              >
                {{ isSavingPersonal ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Security Tab -->
        <div v-if="activeTab === 'security'" class="rounded-2xl bg-white p-5 shadow-md sm:p-6 lg:p-8">
          <h3 class="mb-6 text-2xl font-bold text-gray-800">Security</h3>
          
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
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
              <button
                v-if="isEditingSecurity"
                type="button"
                @click="cancelEditingSecurity"
                class="w-full rounded-lg bg-gray-200 px-8 py-3 font-semibold text-gray-800 shadow-md transition-colors hover:bg-gray-300 sm:w-auto"
              >
                Cancel
              </button>
              <button
                v-if="!isEditingSecurity"
                type="button"
                @click="isEditingSecurity = true"
                class="w-full rounded-lg border-2 border-gray-300 bg-white px-8 py-3 font-semibold text-gray-700 shadow-md transition-colors hover:border-gray-400 sm:w-auto"
              >
                Edit
              </button>
              <button
                v-if="isEditingSecurity"
                type="submit"
                :disabled="isSavingSecurity || currentPasswordStatus !== 'correct' || passwordsMatch === false"
                class="w-full rounded-lg bg-[#C9876C] px-8 py-3 font-semibold text-white shadow-md transition-colors hover:bg-[#B8765B] disabled:cursor-not-allowed disabled:bg-gray-400 sm:w-auto"
              >
                {{ isSavingSecurity ? 'Updating...' : 'Update Password' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Preferences Tab -->
        <div v-if="activeTab === 'preferences'" class="rounded-2xl bg-white p-5 shadow-md sm:p-6 lg:p-8">
          <h3 class="mb-6 text-2xl font-bold text-gray-800">Preferences</h3>
          <p class="text-gray-600">Preference settings coming soon...</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onBeforeUnmount, onMounted, watch } from 'vue'
import api from '../../services/api'

const PASSWORD_VERIFY_DEBOUNCE_MS = 350

const props = defineProps({
  user: {
    type: Object,
    default: () => ({
      name: '',
      email: '',
      phone: '',
      address: '',
      avatar: ''
    })
  },
  tab: {
    type: String,
    default: 'personal'
  }
})

const profileFromUser = (user = {}) => ({
  fullName: user.name || user.full_name || '',
  phoneNumber: user.phone || user.phoneNumber || user.phone_number || '',
  deliveryAddress: user.address || user.deliveryAddress || user.delivery_address || '',
})

const activeTab = ref(props.tab)

const personalInfo = ref(profileFromUser(props.user))

// Personal Info Edit State
const isEditingPersonal = ref(false)
const isSavingPersonal = ref(false)
const isLoadingPersonalInfo = ref(false)
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

const applyPersonalInfo = (profile = {}, options = {}) => {
  const nextPersonalInfo = profileFromUser(profile)
  originalPersonalInfo.value = { ...nextPersonalInfo }

  if (options.force || !isEditingPersonal.value) {
    personalInfo.value = { ...nextPersonalInfo }
  }
}

const loadPersonalInfo = async () => {
  isLoadingPersonalInfo.value = true
  personalInfoError.value = ''

  try {
    const response = await api.getProfile()
    applyPersonalInfo(response.data?.data || props.user, { force: true })
  } catch (error) {
    applyPersonalInfo(props.user, { force: true })
    personalInfoError.value = error.response?.data?.message || 'Unable to load profile details right now.'
  } finally {
    isLoadingPersonalInfo.value = false
  }
}

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
      applyPersonalInfo(response.data.data || personalInfo.value, { force: true })
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

onMounted(() => {
  loadPersonalInfo()
})

watch(() => props.user, (user) => {
  applyPersonalInfo(user)
}, { deep: true })
</script>
