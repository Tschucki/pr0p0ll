<script setup>
import Layout from '../../Layouts/Layout.vue';

defineProps({
  terms: String,
});
defineOptions({
  layout: (h, page) =>
    h(
      Layout,
      {
        title: 'Nutzungsbedingungen - pr0p0ll',
      },
      () => page
    ),
});
</script>

<template>
  <div class="mx-auto mt-24 max-w-7xl px-6 sm:mt-32 md:mt-56 lg:px-8">
    <div class="mx-auto max-w-2xl lg:max-w-none">
      <div
        v-motion
        class="max-w-3xl"
        :initial="{ opacity: 0, y: 100 }"
        :enter="{ opacity: 1, y: 0, scale: 1 }"
        :delay="150">
        <h1
          class="font-display text-5xl font-medium tracking-tight text-[#f2f5f4] [text-wrap:balance] sm:text-7xl">
          <span class="hidden sm:flex">Nutzungsbedingungen</span>
          <span class="flex sm:hidden">Nutzungs-bedingungen</span>
        </h1>
      </div>
    </div>
    <div
      class="prose prose-invert mt-16 prose-p:text-[#f2f5f4]/80"
      v-html="terms"></div>
  </div>
</template>

<style scoped></style>
