<script setup>
import Container from "./Container.vue";
import Button from '../components/Button.vue';
import {Link} from "@inertiajs/vue3";
import route from 'ziggy-js';
</script>

<template>
  <header>
    <div
        class="absolute left-0 right-0 top-2 z-40 pt-14">
      <Container>
        <div class="flex items-center justify-between">
          <Link
              href="/"
          >
            Pr0p0ll
          </Link>
          <div class="flex items-center gap-x-8">
            <a :href="route('login')">
              <Button>
                Login
              </Button>
            </a>
          </div>
        </div>
      </Container>
    </div>
  </header>
</template>

<style scoped>

</style>
