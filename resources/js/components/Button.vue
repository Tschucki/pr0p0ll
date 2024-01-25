<script>
export default {
  name: "Button"
}
</script>

<template>
  <button
      class="inline-flex rounded-full px-4 py-1.5 text-sm font-semibold transition bg-neutral-950 text-white hover:bg-neutral-800">
    <slot/>
  </button>
</template>

<style scoped>

</style>
