<script>
import FaqElement from './FaqElement.vue';

export default {
  name: 'FAQ',
  components: { FaqElement },
};
</script>

<template>
  <div
    id="faq"
    v-motion
    class="lg:mt-42 mx-auto max-w-7xl px-6 pt-20 sm:mt-32 sm:py-32 lg:px-8"
    :initial="{ opacity: 0, y: 24 }"
    :visibleOnce="{ opacity: 1, y: 0, scale: 1 }"
    :delay="300">
    <div class="mx-auto max-w-2xl lg:max-w-none">
      <div class="max-w-2xl" style="opacity: 1; transform: none">
        <h2
          class="font-display block text-4xl font-medium tracking-tight text-[#f2f5f4] [text-wrap:balance] sm:text-5xl">
          FAQ
        </h2>
      </div>
    </div>
    <div class="mx-auto mt-8 max-w-7xl lg:px-8">
      <div class="mx-auto max-w-2xl lg:max-w-none">
        <div class="grid grid-cols-1 gap-2">
          <FaqElement :question="'Wie erstelle ich eine Umfrage?'">
            <p>
              Um eine Umfrage zu erstellen benötigst du einen Account bei
              <a href="https://pr0gramm.com">pr0gramm</a>.
            </p>
          </FaqElement>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
