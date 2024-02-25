<script>
export default {
  name: 'Container',
};
</script>

<template>
  <div class="relative mx-auto max-w-7xl px-6 lg:px-8">
    <div class="mx-auto max-w-2xl lg:max-w-none">
      <slot></slot>
    </div>
  </div>
</template>

<style scoped></style>
