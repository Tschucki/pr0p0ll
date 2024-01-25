<template>
  <svg ref="ref" aria-hidden="true">
    <rect width="100%" height="100%" fill="url(#:rd:)" strokeWidth="0" />
    <svg x="50%" :y="200" strokeWidth="0" class="overflow-visible">
      <Block v-for="block in staticBlocks" :key="block.toString()" :x="block[0]" :y="block[1]" />
      <Block v-for="block in hoveredBlocks" :key="block[2]" :x="block[0]" :y="block[1]"/>
    </svg>
    <defs>
      <pattern  width="96" height="480" x="50%" patternUnits="userSpaceOnUse" :patternTransform="`translate(0 ${200})`" fill="none">
        <path d="M128 0 98.572 147.138A16 16 0 0 1 82.883 160H13.117a16 16 0 0 0-15.69 12.862l-26.855 134.276A16 16 0 0 1-45.117 320H-116M64-160 34.572-12.862A16 16 0 0 1 18.883 0h-69.766a16 16 0 0 0-15.69 12.862l-26.855 134.276A16 16 0 0 1-109.117 160H-180M192 160l-29.428 147.138A15.999 15.999 0 0 1 146.883 320H77.117a16 16 0 0 0-15.69 12.862L34.573 467.138A16 16 0 0 1 18.883 480H-52M-136 480h58.883a16 16 0 0 0 15.69-12.862l26.855-134.276A16 16 0 0 1-18.883 320h69.766a16 16 0 0 0 15.69-12.862l26.855-134.276A16 16 0 0 1 109.117 160H192M-72 640h58.883a16 16 0 0 0 15.69-12.862l26.855-134.276A16 16 0 0 1 45.117 480h69.766a15.999 15.999 0 0 0 15.689-12.862l26.856-134.276A15.999 15.999 0 0 1 173.117 320H256M-200 320h58.883a15.999 15.999 0 0 0 15.689-12.862l26.856-134.276A16 16 0 0 1-82.883 160h69.766a16 16 0 0 0 15.69-12.862L29.427 12.862A16 16 0 0 1 45.117 0H128" />
      </pattern>
    </defs>
  </svg>
</template>

<script>
import Block from './Block.vue';

export default {
  props: {
    yOffset: {
      type: Number,
      default: 0
    },
    interactive: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      ref: null,
      currentBlock: null,
      counter: 0,
      hoveredBlocks: [],
      staticBlocks: [
        [1, 1],
        [2, 2],
        [4, 3],
        [6, 2],
        [7, 4],
        [5, 5],
      ]
    };
  },
  components: {
    Block
  }
};
</script>
