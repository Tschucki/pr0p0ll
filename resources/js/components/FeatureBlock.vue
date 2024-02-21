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
      <div class="pointer-events-none text-[#f2f5f4] font-medium">
        <slot name="image" />
      </div>
      <h2
        class="mt-6 font-display text-2xl font-semibold pointer-events-none text-[#f2f5f4]">
        <slot name="feature"></slot>
      </h2>
      <p class="mt-4 text-base pointer-events-none text-[#f2f5f4]/80">
        <slot name="description"></slot>
      </p>
    </div>
  </div>
</template>

<style scoped></style>
