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
      class="block font-display tracking-tight [text-wrap:balance] text-3xl font-medium sm:text-4xl text-[#f2f5f4]">
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
