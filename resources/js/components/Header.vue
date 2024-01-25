<script setup>
import Container from "./Container.vue";
import Button from '../components/Button.vue';
import {Link} from "@inertiajs/vue3";
import pr0p0llImage from '../../assets/imgs/pr0p0ll.png';
</script>

<template>
  <header>
    <div
        class="absolute left-0 right-0 top-2 z-40 pt-14">
      <Container>
        <div class="flex items-center justify-between">
          <Link
              href="/"
              class="sm:text-2xl text-xl font-medium flex items-center gap-2 text-[#f2f5f4]"
          >
            <img alt="Pr0p0ll Logo" title="Pr0p0ll Logo" :src="pr0p0llImage" class="w-auto sm:h-12 h-10 aspect-square">
            Pr0p0ll
          </Link>
          <div class="flex items-center gap-x-8">
            <a href="/login" v-if="!$page.props.auth.user.id">
              <Button>
                Login mit Pr0gramm
              </Button>
            </a>
            <a href="/login" v-if="$page.props.auth.user.id">
              <Button>
                Zu den Umfragen
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
