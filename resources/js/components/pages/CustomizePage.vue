<template>
  <div class="px-6 py-8 md:px-8 xl:px-10">
    <div class="mx-auto max-w-6xl">
      <div class="mb-8">
        <p
          v-if="isCakeGuide"
          class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B47A52]"
        >
          Cake Ordering Guide
        </p>
        <h1 class="text-4xl font-black tracking-tight text-[#44413E]" style="font-family: 'Urbanist', sans-serif;">
          {{ isCakeGuide ? 'Made-to-order cakes' : 'Custom order' }}
        </h1>
        <p class="mt-3 max-w-3xl text-sm leading-6 text-[#766C65]">
          {{
            isCakeGuide
              ? 'Every cake request now starts from the guide. Pick a package, upload your peg, choose the flavor that fits, and send the bakery the full brief for review.'
              : 'Order products tailored to what you want. Upload a photo reference, choose your options, and tell us the details.'
          }}
        </p>
      </div>

      <div class="mb-8 border-t border-[#E4D8CD]"></div>

      <section
        v-if="isCakeGuide"
        class="rounded-[32px] border border-[#E8DDD3] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.25)] md:p-8"
      >
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
          <div class="max-w-3xl">
            <h2 class="text-2xl font-bold text-[#4B4743]">Guide collections</h2>
            <p class="mt-2 text-sm leading-6 text-[#766C65]">
              These are the guided cake and event-dessert options available for customer orders. Select a collection first, then choose the package that matches your event.
            </p>
          </div>
          <div class="rounded-[22px] border border-[#E7D9CB] bg-[#FFF8F1] px-4 py-3 text-sm text-[#7E6552]">
            Starting prices are used for checkout. Final adjustments may still depend on the approved design.
          </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <button
            v-for="sectionItem in cakeGuideSections"
            :key="sectionItem.id"
            type="button"
            @click="chooseCakeSection(sectionItem.id)"
            class="rounded-[26px] border px-5 py-5 text-left transition-all"
            :class="selectedCakeSectionId === sectionItem.id
              ? 'border-[#C9876C] bg-[#FFF4EB] shadow-[0_18px_35px_-28px_rgba(201,135,108,0.65)]'
              : 'border-[#E7D9CB] bg-[#FCFAF8] hover:border-[#D9B8A4]'"
          >
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#B47A52]">{{ sectionItem.eyebrow }}</p>
            <p class="mt-3 text-lg font-bold text-[#4B4743]">{{ sectionItem.title }}</p>
            <p class="mt-2 text-sm leading-6 text-[#766C65]">{{ sectionItem.description }}</p>
            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.16em] text-[#9B6A48]">{{ sectionItem.priceNote }}</p>
          </button>
        </div>
      </section>

      <div
        v-if="isCakeGuide && isTierCakeSection && tierGalleryNoticeVisible"
        class="fixed bottom-6 right-6 z-40 max-w-sm rounded-[24px] border border-[#E6D4C5] bg-white p-5 text-[#5F5147] shadow-[0_22px_55px_-28px_rgba(79,52,34,0.55)]"
      >
        <div class="flex items-start gap-3">
          <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#FFF0E6] text-[#B46E46]">
            <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.55-2.27A1 1 0 0121 8.62v6.76a1 1 0 01-1.45.89L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
          </div>
          <div class="min-w-0">
            <p class="text-sm font-bold text-[#453D37]">Tier cake gallery tip</p>
            <p class="mt-1 text-sm leading-6 text-[#74675E]">
              {{ activeCakeSection?.galleryNotice || 'Tap a reference photo to view previous cakes with their summary and prices.' }}
            </p>
          </div>
          <button
            type="button"
            @click="tierGalleryNoticeVisible = false"
            class="ml-auto rounded-full p-1 text-[#9A8B7F] transition hover:bg-[#F7EFE8] hover:text-[#5E5148]"
            aria-label="Close tier cake gallery notice"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <section
        v-if="isCakeGuide && activeGalleryImages.length"
        class="mt-7 rounded-[32px] border border-[#E8DDD3] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.25)] md:p-8"
      >
        <h2 class="text-2xl font-bold text-[#4B4743]">
          {{ activeCakeSection?.eyebrow }} - Reference Gallery
        </h2>
        <p class="mt-2 text-sm leading-6 text-[#766C65]">
          Browse past designs for inspiration. Tier cake photos include their previous summary and prices.
        </p>

        <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-5">
          <button
            v-for="(img, idx) in activeGalleryImages"
            :key="img.src || idx"
            type="button"
            @click="openGalleryImage(img)"
            class="group relative aspect-square overflow-hidden rounded-[20px] border border-[#E9DDD2] bg-[#F9F5F1] transition-all hover:shadow-md"
          >
            <img
              :src="img.src"
              :alt="img.label || `${activeCakeSection?.eyebrow} reference ${idx + 1}`"
              class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
            />
            <span
              v-if="img.label"
              class="absolute inset-x-2 bottom-2 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-[#5D5149] shadow-sm"
            >
              {{ img.label }}
            </span>
          </button>
        </div>

        <div
          v-if="galleryLightbox"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
          @click.self="galleryLightbox = null"
        >
          <div class="relative max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-[24px] bg-white">
            <div class="grid max-h-[90vh] overflow-y-auto lg:grid-cols-[1.15fr_0.85fr]">
              <div class="bg-black">
                <img :src="galleryLightbox.src" :alt="galleryLightbox.label || 'Gallery preview'" class="max-h-[85vh] w-full object-contain" />
              </div>
              <div v-if="galleryLightbox.label" class="p-6 md:p-7">
                <div class="rounded-[24px] border border-[#E7D8CB] bg-white p-5">
                  <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Guide summary</p>
                  <p class="mt-3 text-2xl font-bold text-[#4B4743]">{{ galleryLightbox.label }}</p>
                  <p class="mt-2 text-sm font-semibold text-[#8B5A3C]">{{ galleryLightbox.priceLabel }}</p>
                  <p class="mt-4 text-base leading-7 text-[#766C65]">{{ galleryLightbox.summary }}</p>
                  <p v-if="galleryLightbox.size" class="mt-4 text-sm text-[#685D55]">Guide size: {{ galleryLightbox.size }}</p>
                </div>
              </div>
            </div>
            <button
              type="button"
              @click="galleryLightbox = null"
              class="absolute right-3 top-3 rounded-full bg-white/90 p-2 shadow-md transition hover:bg-white"
            >
              <svg class="h-5 w-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </section>

      <section class="mt-7 rounded-[32px] border border-[#E8DDD3] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.35)] md:p-8">
        <h2 class="text-2xl font-bold text-[#4B4743]">
          {{ isCakeGuide ? 'Selected order details' : 'Product details' }}
        </h2>

        <div class="mt-6 grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
          <div
            @click="triggerFileUpload"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            class="flex min-h-[320px] cursor-pointer items-center justify-center rounded-[30px] border-2 border-dashed border-[#D8C7B8] bg-[#FCFAF8] p-6 transition-colors"
            :class="{ 'border-[#C9876C] bg-[#FBF1E8]': isDragging }"
          >
            <div v-if="!imagePreview" class="text-center">
              <svg class="mx-auto h-16 w-16 text-[#7A6A5F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
              <p class="mt-6 text-xl font-semibold tracking-wide text-[#4E4742]">UPLOAD IMAGE</p>
              <p class="text-xl font-semibold tracking-wide text-[#4E4742]">REFERENCE</p>
              <p class="mt-3 text-sm text-[#8B817A]">Drag and drop or click to upload</p>
            </div>

            <div v-else class="relative h-full w-full">
              <img :src="imagePreview" alt="Reference preview" class="h-full w-full rounded-[24px] object-cover" />
              <button
                type="button"
                @click.stop="removeImage"
                class="absolute right-4 top-4 rounded-full bg-red-500 p-2 text-white shadow-md transition hover:bg-red-600"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <input
              ref="fileInput"
              type="file"
              accept="image/*"
              class="hidden"
              @change="handleFileSelect"
            />
          </div>

          <div class="rounded-[30px] border border-[#E7D9CB] bg-[#FCFAF8] p-6">
            <div v-if="isCakeGuide" class="space-y-5">
              <div class="grid gap-2">
                <label class="text-sm font-medium text-[#66605B]">Guide section</label>
                <select
                  v-model="selectedCakeSectionId"
                  class="h-12 rounded-full border border-[#D8CCC2] bg-white px-5 outline-none focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
                >
                  <option v-for="sectionItem in cakeGuideSections" :key="sectionItem.id" :value="sectionItem.id">
                    {{ sectionItem.title }}
                  </option>
                </select>
              </div>

              <div class="grid gap-2">
                <label class="text-sm font-medium text-[#66605B]">Package</label>
                <select
                  v-model="selectedCakePackageId"
                  class="h-12 rounded-full border border-[#D8CCC2] bg-white px-5 outline-none focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
                >
                  <option v-for="packageItem in activeCakePackages" :key="packageItem.id" :value="packageItem.id">
                    {{ packageItem.label }} - {{ packageItem.priceLabel }}
                  </option>
                </select>
              </div>

              <div class="rounded-[24px] border border-[#E7D8CB] bg-white p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Guide summary</p>
                <p class="mt-3 text-xl font-bold text-[#4B4743]">{{ selectedCakePackage?.label || 'Select a package' }}</p>
                <p class="mt-2 text-sm font-semibold text-[#8B5A3C]">{{ selectedCakePackage?.priceLabel || 'Starting price to be discussed' }}</p>
                <p class="mt-2 text-sm leading-6 text-[#766C65]">
                  {{ selectedCakePackage?.summary || activeCakeSection?.description }}
                </p>
                <p v-if="selectedCakePackage?.size" class="mt-3 text-sm text-[#685D55]">Guide size: {{ selectedCakePackage.size }}</p>
              </div>

              <div v-if="activeCakeSection?.packageInclusions?.length" class="rounded-[24px] border border-[#E7D8CB] bg-[#FFF8F1] p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Package inclusions</p>
                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                  <span
                    v-for="inclusion in activeCakeSection.packageInclusions"
                    :key="inclusion"
                    class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-[#6D5545]"
                  >
                    {{ inclusion }}
                  </span>
                </div>
              </div>

              <div class="grid gap-2">
                <label class="text-sm font-medium text-[#66605B]">Quantity</label>
                <div class="flex items-center overflow-hidden rounded-full border border-[#D8CCC2] bg-white">
                  <button
                    type="button"
                    @click="decrementQuantity"
                    class="px-5 py-3 transition-colors hover:bg-[#F3ECE5]"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                  </button>
                  <input
                    v-model.number="formData.quantity"
                    type="number"
                    min="1"
                    class="flex-1 bg-transparent py-3 text-center outline-none"
                  />
                  <button
                    type="button"
                    @click="incrementQuantity"
                    class="px-5 py-3 transition-colors hover:bg-[#F3ECE5]"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </button>
                </div>
              </div>

              <div class="grid gap-2">
                <label class="text-sm font-medium text-[#66605B]">Flavor</label>
                <select
                  v-model="formData.flavor"
                  class="h-12 rounded-full border border-[#D8CCC2] bg-white px-5 outline-none focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
                >
                  <option v-for="flavorOption in selectedFlavorOptions" :key="flavorOption" :value="flavorOption">
                    {{ flavorOption }}
                  </option>
                </select>
              </div>

              <div class="rounded-[24px] border border-[#E7D8CB] bg-[#FFF8F1] p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B59884]">Guide notes</p>
                <ul class="mt-3 space-y-2 text-sm leading-6 text-[#766C65]">
                  <li v-for="note in activeCakeSection?.guideNotes || []" :key="note">{{ note }}</li>
                </ul>
              </div>
            </div>

            <div v-else class="space-y-5">
              <div class="grid gap-2">
                <label class="text-sm font-medium text-[#66605B]">Size</label>
                <select
                  v-model="formData.size"
                  class="h-12 rounded-full border border-[#D8CCC2] bg-white px-5 outline-none focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
                >
                  <option>Small</option>
                  <option>Medium</option>
                  <option>Large</option>
                </select>
              </div>

              <div class="grid gap-2">
                <label class="text-sm font-medium text-[#66605B]">Quantity</label>
                <div class="flex items-center overflow-hidden rounded-full border border-[#D8CCC2] bg-white">
                  <button
                    type="button"
                    @click="decrementQuantity"
                    class="px-5 py-3 transition-colors hover:bg-[#F3ECE5]"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                  </button>
                  <input
                    v-model.number="formData.quantity"
                    type="number"
                    min="1"
                    class="flex-1 bg-transparent py-3 text-center outline-none"
                  />
                  <button
                    type="button"
                    @click="incrementQuantity"
                    class="px-5 py-3 transition-colors hover:bg-[#F3ECE5]"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </button>
                </div>
              </div>

              <div class="grid gap-2">
                <label class="text-sm font-medium text-[#66605B]">Flavor</label>
                <select
                  v-model="formData.flavor"
                  class="h-12 rounded-full border border-[#D8CCC2] bg-white px-5 outline-none focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
                >
                  <option v-for="flavorOption in legacyFlavorOptions" :key="flavorOption" :value="flavorOption">
                    {{ flavorOption }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="mt-7 rounded-[32px] border border-[#E8DDD3] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.25)] md:p-8">
        <h2 class="text-2xl font-bold text-[#4B4743]">Design description</h2>
        <textarea
          v-model="formData.designDescription"
          :placeholder="isCakeGuide
            ? 'Describe the theme, colors, toppers, characters, event date, and any guide-specific notes for the bakery.'
            : 'Describe how you like it to be'"
          class="mt-5 min-h-[190px] w-full rounded-[26px] border border-[#DDD1C7] bg-[#FCFAF8] px-5 py-4 outline-none transition focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
          maxlength="600"
        ></textarea>
        <div class="mt-2 text-right text-sm text-gray-500">
          {{ formData.designDescription.length }}/600
        </div>
      </section>

      <section class="mt-7 rounded-[32px] border border-[#E8DDD3] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.25)] md:p-8">
        <h2 class="text-2xl font-bold text-[#4B4743]">Dedication message</h2>
        <textarea
          v-model="formData.dedicationMessage"
          placeholder="Write your message here"
          class="mt-5 min-h-[190px] w-full rounded-[26px] border border-[#DDD1C7] bg-[#FCFAF8] px-5 py-4 outline-none transition focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"
          maxlength="300"
        ></textarea>
        <div class="mt-2 text-right text-sm text-gray-500">
          {{ formData.dedicationMessage.length }}/300
        </div>
      </section>

      <section class="mt-7 rounded-[32px] border border-[#E8DDD3] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.25)] md:p-8">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
          <div>
            <h2 class="text-2xl font-bold text-[#4B4743]">Checkout flow</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#766C65]">
              {{
                isCakeGuide
                  ? 'Choose whether the bakery should save this guided cake brief in your cart first, or whether you want to go straight into a single-order PayMongo checkout.'
                  : 'Choose whether this custom order should stay in your cart for a combined checkout later, or go straight to payment by itself.'
              }}
            </p>
          </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
          <button
            v-for="option in checkoutFlowOptions"
            :key="option.value"
            type="button"
            @click="selectedCheckoutFlow = option.value"
            class="rounded-[26px] border px-5 py-5 text-left transition-all"
            :class="selectedCheckoutFlow === option.value
              ? 'border-[#C9876C] bg-[#FFF4EB] shadow-[0_18px_35px_-28px_rgba(201,135,108,0.65)]'
              : 'border-[#E7D9CB] bg-[#FCFAF8] hover:border-[#D9B8A4]'"
          >
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-lg font-bold text-[#4B4743]">{{ option.label }}</p>
                <p class="mt-1 text-sm text-[#766C65]">{{ option.description }}</p>
                <p class="mt-2 text-xs font-medium uppercase tracking-[0.14em] text-[#A07A62]">{{ option.helper }}</p>
              </div>
              <span
                class="mt-0.5 flex h-6 w-6 items-center justify-center rounded-full border"
                :class="selectedCheckoutFlow === option.value
                  ? 'border-[#C9876C] bg-[#C9876C] text-white'
                  : 'border-[#D8CCC2] bg-white text-transparent'"
              >
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 13l4 4L19 7" />
                </svg>
              </span>
            </div>
          </button>
        </div>
      </section>

      <section v-if="selectedCheckoutFlow === 'direct_checkout'" class="mt-7 rounded-[32px] border border-[#E8DDD3] bg-white p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.25)] md:p-8">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
          <div>
            <h2 class="text-2xl font-bold text-[#4B4743]">Payment method</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#766C65]">
              PayMongo will handle the secure checkout. Choose the wallet you want to use for this order only.
            </p>
            <p class="mt-2 max-w-2xl text-xs leading-5 text-[#8A7E76]">
              Existing products already in your cart will stay there and will not be included in this direct checkout.
            </p>
          </div>
          <span class="inline-flex w-fit rounded-full bg-[#F6EFE7] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#9B6A48]">
            PayMongo Checkout
          </span>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
          <button
            v-for="option in paymentMethodOptions"
            :key="option.value"
            type="button"
            @click="selectedPaymentMethod = option.value"
            class="rounded-[26px] border px-5 py-5 text-left transition-all"
            :class="selectedPaymentMethod === option.value
              ? 'border-[#C9876C] bg-[#FFF4EB] shadow-[0_18px_35px_-28px_rgba(201,135,108,0.65)]'
              : 'border-[#E7D9CB] bg-[#FCFAF8] hover:border-[#D9B8A4]'"
          >
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-lg font-bold text-[#4B4743]">{{ option.label }}</p>
                <p class="mt-1 text-sm text-[#766C65]">{{ option.description }}</p>
              </div>
              <span
                class="flex h-6 w-6 items-center justify-center rounded-full border"
                :class="selectedPaymentMethod === option.value
                  ? 'border-[#C9876C] bg-[#C9876C] text-white'
                  : 'border-[#D8CCC2] bg-white text-transparent'"
              >
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 13l4 4L19 7" />
                </svg>
              </span>
            </div>
          </button>
        </div>
      </section>

      <section v-else class="mt-7 rounded-[32px] border border-[#E8DDD3] bg-[#FFF9F4] p-6 shadow-[0_18px_40px_-34px_rgba(118,79,49,0.18)] md:p-8">
        <h2 class="text-2xl font-bold text-[#4B4743]">
          {{ isCakeGuide ? 'Save the cake brief in the cart first' : 'Checkout later with other products' }}
        </h2>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-[#766C65]">
          {{
            isCakeGuide
              ? 'The custom cake brief will be saved in your cart so you can review it together with breads, pastries, or other custom items before checkout.'
              : 'This custom order will be saved to your cart first. You can continue shopping, then choose this item together with breads, pastries, or other products when you are ready to checkout.'
          }}
        </p>
      </section>

      <div class="mt-8 flex flex-wrap gap-4">
        <button
          type="button"
          @click="submitCustomOrder"
          class="rounded-full bg-[#C9876C] px-8 py-3.5 font-semibold text-white shadow-md transition-colors hover:bg-[#B8765B]"
        >
          {{ primaryActionLabel }}
        </button>
        <button
          type="button"
          @click="viewCart"
          class="rounded-full border border-[#CFC5BC] bg-white px-8 py-3.5 font-semibold text-[#4E4742] transition-colors hover:bg-[#FAF6F1]"
        >
          View cart
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useSpaRouter } from '../../router';
import { cakeGuideSections, defaultCakeGuideSectionId, getCakeGuideSection } from '../../data/cakeGuides';
import { useCartStore } from '../../services/cartStore';

const props = defineProps({
  guide: {
    type: String,
    default: 'general',
  },
  section: {
    type: String,
    default: null,
  },
});

const { push } = useSpaRouter();
const { addCustomItem, checkout } = useCartStore();
const isAuthenticated = () => Boolean(window.Laravel?.auth?.check);
const legacyFlavorOptions = ['Chocolate', 'Vanilla', 'Strawberry', 'Mocha', 'Ube'];
const checkoutFlowOptions = [
  {
    value: 'cart_first',
    label: 'Add to Cart First',
    description: 'Save this custom order in your cart, then checkout it later together with other products.',
    helper: 'Best if you are still shopping',
  },
  {
    value: 'direct_checkout',
    label: 'Direct Checkout',
    description: 'Checkout only this custom order right away without including the products already in your cart.',
    helper: 'Best if this order is ready now',
  },
];
const paymentMethodOptions = [
  {
    value: 'gcash',
    label: 'GCash',
    description: 'Redirect to PayMongo and complete the payment in GCash.',
  },
  {
    value: 'maya',
    label: 'Maya',
    description: 'Redirect to PayMongo and continue the payment in Maya.',
  },
];

const normalizeCakeSectionId = (value) => getCakeGuideSection(value)?.id || defaultCakeGuideSectionId;

const isCakeGuide = computed(() => props.guide === 'cakes');
const selectedCakeSectionId = ref(normalizeCakeSectionId(props.section));
const selectedCakePackageId = ref(getCakeGuideSection(selectedCakeSectionId.value)?.packages?.[0]?.id || null);
const formData = ref({
  size: 'Medium',
  quantity: 1,
  flavor: 'Chocolate',
  designDescription: '',
  dedicationMessage: '',
  imageFile: null,
});
const imagePreview = ref(null);
const isDragging = ref(false);
const fileInput = ref(null);
const selectedCheckoutFlow = ref('cart_first');
const selectedPaymentMethod = ref('gcash');
const galleryLightbox = ref(null);
const tierGalleryNoticeVisible = ref(false);

const activeCakeSection = computed(() => (
  isCakeGuide.value ? getCakeGuideSection(selectedCakeSectionId.value) : null
));
const activeCakePackages = computed(() => activeCakeSection.value?.packages || []);
const selectedCakePackage = computed(() => (
  activeCakePackages.value.find((packageItem) => packageItem.id === selectedCakePackageId.value)
  || activeCakePackages.value[0]
  || null
));
const selectedFlavorOptions = computed(() => (
  isCakeGuide.value
    ? (activeCakeSection.value?.flavorOptions || ['To be discussed with Bakerdan'])
    : legacyFlavorOptions
));
const isTierCakeSection = computed(() => selectedCakeSectionId.value === 'tier-package');
const activeGalleryImages = computed(() => (
  activeCakeSection.value?.galleryImages || []
).map((image, index) => {
  if (typeof image === 'string') {
    return {
      src: image,
      label: '',
      priceLabel: '',
      summary: '',
      size: '',
      index,
    };
  }

  return {
    src: image.src,
    label: image.label || '',
    priceLabel: image.priceLabel || '',
    summary: image.summary || '',
    size: image.size || '',
    index,
  };
}));

const openGalleryImage = (image) => {
  galleryLightbox.value = image;
};

const primaryActionLabel = computed(() => {
  if (selectedCheckoutFlow.value === 'direct_checkout') {
    return isCakeGuide.value ? 'Direct Checkout This Cake Order' : 'Direct Checkout This Custom Order';
  }

  return isCakeGuide.value ? 'Add Cake Brief to Cart' : 'Add to Cart for Combined Checkout';
});

const syncCakeSelection = () => {
  if (!isCakeGuide.value) {
    return;
  }

  if (!activeCakePackages.value.some((packageItem) => packageItem.id === selectedCakePackageId.value)) {
    selectedCakePackageId.value = activeCakePackages.value[0]?.id || null;
  }

  if (selectedCakePackage.value?.size) {
    formData.value.size = selectedCakePackage.value.size;
  }

  if (!selectedFlavorOptions.value.includes(formData.value.flavor)) {
    formData.value.flavor = selectedFlavorOptions.value[0] || '';
  }

  if (formData.value.quantity < 1) {
    formData.value.quantity = 1;
  }
};

watch(selectedCakeSectionId, () => {
  syncCakeSelection();
});

watch(
  isTierCakeSection,
  (selected) => {
    if (isCakeGuide.value && selected) {
      tierGalleryNoticeVisible.value = true;
    }
  },
  { immediate: true },
);

watch(selectedCakePackageId, () => {
  syncCakeSelection();
});

watch(
  () => props.section,
  (value) => {
    if (!isCakeGuide.value) {
      return;
    }

    selectedCakeSectionId.value = normalizeCakeSectionId(value);
    syncCakeSelection();
  },
);

watch(
  () => props.guide,
  (guide) => {
    if (guide === 'cakes') {
      selectedCakeSectionId.value = normalizeCakeSectionId(props.section);
      syncCakeSelection();
      return;
    }

    formData.value.size = 'Medium';
    formData.value.flavor = legacyFlavorOptions[0];
    tierGalleryNoticeVisible.value = false;
  },
);

const chooseCakeSection = (sectionId) => {
  selectedCakeSectionId.value = sectionId;
};

const triggerFileUpload = () => {
  fileInput.value?.click();
};

const handleFileSelect = (event) => {
  const file = event.target.files?.[0];

  if (file) {
    processFile(file);
  }
};

const handleDrop = (event) => {
  isDragging.value = false;
  const file = event.dataTransfer.files?.[0];

  if (file && file.type.startsWith('image/')) {
    processFile(file);
  }
};

const processFile = (file) => {
  formData.value.imageFile = file;
  const reader = new FileReader();

  reader.onload = (loadEvent) => {
    imagePreview.value = loadEvent.target?.result || null;
  };

  reader.readAsDataURL(file);
};

const removeImage = () => {
  imagePreview.value = null;
  formData.value.imageFile = null;

  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const incrementQuantity = () => {
  formData.value.quantity += 1;
};

const decrementQuantity = () => {
  if (formData.value.quantity > 1) {
    formData.value.quantity -= 1;
  }
};

const buildCustomPayload = () => {
  if (isCakeGuide.value) {
    return {
      size: formData.value.size || selectedCakePackage.value?.size || activeCakeSection.value?.title,
      quantity: formData.value.quantity,
      flavor: formData.value.flavor || 'To be discussed with Bakerdan',
      designDescription: formData.value.designDescription,
      dedicationMessage: formData.value.dedicationMessage,
      imageFile: formData.value.imageFile,
      productName: selectedCakePackage.value?.productName || activeCakeSection.value?.title || 'Custom Cake Order',
      basePrice: selectedCakePackage.value?.price || 0,
      guideSection: activeCakeSection.value?.title || 'Cake Guide',
    };
  }

  return {
    size: formData.value.size,
    quantity: formData.value.quantity,
    flavor: formData.value.flavor,
    designDescription: formData.value.designDescription,
    dedicationMessage: formData.value.dedicationMessage,
    imageFile: formData.value.imageFile,
  };
};

const ensureCustomRequestIsReady = () => {
  if (isCakeGuide.value && !selectedCakePackage.value) {
    window.alert('Choose a cake guide package before submitting the order.');
    return false;
  }

  const hasDesignReference = Boolean(formData.value.imageFile) || formData.value.designDescription.trim().length > 0;

  if (!hasDesignReference) {
    window.alert('Add a reference image or describe the custom design before submitting your order.');
    return false;
  }

  return true;
};

const directCheckout = async () => {
  if (!ensureCustomRequestIsReady()) {
    return;
  }

  let addedItem = null;

  try {
    addedItem = await addCustomItem(buildCustomPayload());

    if (!addedItem?.id) {
      return;
    }

    if (!isAuthenticated()) {
      push(`/customer/checkout?items=${encodeURIComponent(String(addedItem.id))}`);
      return;
    }

    const order = await checkout([addedItem.id], {
      paymentMethod: selectedPaymentMethod.value,
    });

    if (order?.checkout_url) {
      window.location.href = order.checkout_url;
      return;
    }

    if (order?.id) {
      push(`/customer/orders?highlight=${order.id}`);
    }
  } catch (error) {
    const message = error.response?.data?.message || 'Unable to complete checkout right now.';

    if (addedItem?.id) {
      window.alert(`${message} Your custom order was saved in the cart so you can checkout it later with your other products.`);
      push(`/customer/cart?added=${addedItem.id}`);
      return;
    }

    window.alert(message);
  }
};

const addToCart = async () => {
  if (!ensureCustomRequestIsReady()) {
    return;
  }

  try {
    const addedItem = await addCustomItem(buildCustomPayload());

    if (addedItem?.id) {
      push(`/customer/cart?added=${addedItem.id}`);
    }
  } catch (error) {
    window.alert('Unable to add this custom item to the cart right now.');
  }
};

const submitCustomOrder = async () => {
  if (selectedCheckoutFlow.value === 'direct_checkout') {
    await directCheckout();
    return;
  }

  await addToCart();
};

const viewCart = () => {
  push('/customer/cart');
};

onMounted(() => {
  if (isCakeGuide.value) {
    selectedCakeSectionId.value = normalizeCakeSectionId(props.section);
    syncCakeSelection();
    return;
  }

  formData.value.flavor = legacyFlavorOptions[0];
});
</script>
