<script>
export default {
  name: 'FeatureBlock',
};
</script>

<template>
  <div class="flex" style="opacity: 1; transform: none">
    <div
      v-motion
      :enter="{ opacity: 1, y: 0, scale: 1 }"
      :hovered="{ scale: 1.1 }"
      :delay="100"
      class="relative flex w-full flex-col rounded-3xl p-6 ring-1 ring-[#f2f5f4] transition sm:p-8">
      <div class="pointer-events-none font-medium text-[#f2f5f4]">
        <slot name="image" />
      </div>
      <h2
        class="font-display pointer-events-none mt-6 text-2xl font-semibold text-[#f2f5f4]">
        <slot name="feature"></slot>
      </h2>
      <p class="pointer-events-none mt-4 text-base text-[#f2f5f4]/80">
        <slot name="description"></slot>
      </p>
    </div>
  </div>
</template>

<style scoped></style>
