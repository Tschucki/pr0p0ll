<script setup>
import Container from './Container.vue';
import Pr0Button from '../components/Pr0Button.vue';
import { Link } from '@inertiajs/vue3';
import pr0p0llImage from '../../assets/imgs/pr0p0ll.png';
</script>

<template>
  <header>
    <div class="absolute left-0 right-0 top-2 z-40 pt-14">
      <Container>
        <div class="flex items-center justify-between">
          <Link
            href="/"
            class="flex items-center gap-2 text-xl font-medium text-[#f2f5f4] sm:text-2xl">
            <img
              alt="pr0p0ll Logo"
              title="pr0p0ll Logo"
              :src="pr0p0llImage"
              class="aspect-square h-10 w-auto sm:h-12" />
            pr0p0ll
          </Link>
          <div class="flex items-center gap-x-8">
            <a v-if="!$page.props.auth.user?.id" href="/login">
              <Pr0Button> Login mit pr0gramm </Pr0Button>
            </a>
            <a v-if="$page.props.auth.user?.id" href="/login">
              <Pr0Button> Zu den Umfragen </Pr0Button>
            </a>
          </div>
        </div>
      </Container>
    </div>
  </header>
</template>
