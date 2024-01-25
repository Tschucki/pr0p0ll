<script>
export default {
  name: "FeatureBlock"
}
</script>

<template>
  <div class="flex" style="opacity: 1; transform: none;">
    <div
        class="relative flex w-full flex-col rounded-3xl p-6 ring-1 ring-neutral-950/5 transition hover:bg-neutral-50 sm:p-8">
      <div>
        <slot name="image"/>
      </div>
      <h2 class="mt-6 font-display text-2xl font-semibold text-neutral-950">
        <slot name="feature"></slot>
      </h2>
      <p class="mt-4 text-base text-neutral-600">
        <slot name="description"></slot>
      </p>
    </div>
  </div>
</template>

<style scoped>

</style>
