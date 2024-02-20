<script setup>
import Layout from '../../Layouts/Layout.vue';

defineProps({
  privacy: String,
});
defineOptions({
  layout: (h, page) =>
    h(
      Layout,
      {
        title: 'Datenschutzerklärung - Pr0p0ll',
      },
      () => page
    ),
});
</script>

<template>
  <div class="mx-auto max-w-7xl px-6 lg:px-8 mt-24 sm:mt-32 md:mt-56">
    <div class="mx-auto max-w-2xl lg:max-w-none">
      <div
        v-motion
        class="max-w-3xl"
        :initial="{ opacity: 0, y: 100 }"
        :enter="{ opacity: 1, y: 0, scale: 1 }"
        :delay="150">
        <h1
          class="font-display text-5xl font-medium tracking-tight text-[#f2f5f4] [text-wrap:balance] sm:text-7xl">
          <span class="hidden sm:flex">Datenschutzerklärung</span>
          <span class="sm:hidden flex">Datenschutz-erklärung</span>
        </h1>
      </div>
    </div>
    <div
      class="prose prose-invert mt-16 prose-p:text-[#f2f5f4]/80"
      v-html="privacy"></div>
  </div>
</template>

<style scoped></style>
