<template>
  <path :class="`block ${tailwindClasses}`" :style="{ transform: `translate(${-32 * y + 96 * x}px, ${160 * y}px)` }"
        d="M45.119 4.5a11.5 11.5 0 0 0-11.277 9.245l-25.6 128C6.82 148.861 12.262 155.5 19.52 155.5h63.366a11.5 11.5 0 0 0 11.277-9.245l25.6-128c1.423-7.116-4.02-13.755-11.277-13.755H45.119Z"
  ></path>
</template>

<script>
export default {
  props: {
    x: {
      type: Number,
      required: true
    },
    y: {
      type: Number,
      required: true
    },
  },
  computed: {
    tailwindClasses() {
      return 'bg-black w-10 h-10';
    }
  }
};
</script>
