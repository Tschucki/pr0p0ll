<script>
export default {
  name: 'FaqElement',
  props: {
    question: String,
  },
};
</script>

<template>
  <div :id="question">
    <h4
      class="font-display block text-3xl font-medium tracking-tight text-[#f2f5f4] [text-wrap:balance] sm:text-4xl">
      {{ question }}
    </h4>
    <div class="mx-auto max-w-7xl text-[#f2f5f4]">
      <div class="py-3">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
