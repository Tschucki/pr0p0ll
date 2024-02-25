<script setup>
import pr0p0llImage from '../../assets/imgs/pr0p0ll.png';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <footer
    v-motion
    class="mx-auto mt-24 w-full max-w-7xl px-6 sm:pt-32 lg:px-8 lg:pt-40"
    :initial="{ opacity: 0, y: 24 }"
    :visibleOnce="{ opacity: 1, y: 0 }"
    :delay="100">
    <div class="mx-auto max-w-2xl lg:max-w-none">
      <div style="opacity: 1; transform: none">
        <div class="grid grid-cols-1 gap-x-8 gap-y-16 lg:grid-cols-2">
          <nav>
            <ul role="list" class="grid grid-cols-2 gap-8 sm:grid-cols-3">
              <li>
                <div
                  class="font-display text-sm font-semibold tracking-wider text-[#f2f5f4]">
                  Rechtliches
                </div>
                <ul role="list" class="mt-4 text-sm text-[#f2f5f4]/80">
                  <li class="mt-4">
                    <Link
                      class="transition hover:text-[#f2f5f4]"
                      href="/impressum"
                      >Impressum
                    </Link>
                  </li>
                  <li class="mt-4">
                    <Link
                      class="transition hover:text-[#f2f5f4]"
                      href="/datenschutz"
                      >Datenschutz</Link
                    >
                  </li>
                  <li class="mt-4">
                    <a
                      class="transition hover:text-[#f2f5f4]"
                      href="/nutzungsbedingungen"
                      >Nutzungsbedingungen</a
                    >
                  </li>
                </ul>
              </li>
              <li>
                <div
                  class="font-display text-sm font-semibold tracking-wider text-[#f2f5f4]">
                  Hilfe
                </div>
                <ul role="list" class="mt-4 text-sm text-[#f2f5f4]/80">
                  <li class="mt-4">
                    <a class="transition hover:text-[#f2f5f4]" href="/">FAQ</a>
                  </li>
                  <li class="mt-4">
                    <a class="transition hover:text-[#f2f5f4]" href="/"
                      >Diskussionen</a
                    >
                  </li>
                  <li class="mt-4">
                    <a class="transition hover:text-[#f2f5f4]" href="/"
                      >Auswertungen</a
                    >
                  </li>
                </ul>
              </li>
              <li>
                <div
                  class="font-display text-sm font-semibold tracking-wider text-[#f2f5f4]">
                  Kontakt
                </div>
                <ul role="list" class="mt-4 text-sm text-[#f2f5f4]/80">
                  <li class="mt-4">
                    <a
                      class="transition hover:text-[#f2f5f4]"
                      href="https://pr0gramm.com/inbox/messages/PimmelmannJones"
                      >PimmelmannJones (Pr0gramm)</a
                    >
                  </li>
                  <li class="mt-4">
                    <a
                      class="transition hover:text-[#f2f5f4]"
                      href="mailto:info@pr0p0ll.com"
                      >E-Mail</a
                    >
                  </li>
                </ul>
              </li>
            </ul>
          </nav>
          <div class="flex lg:justify-end">
            <form class="max-w-sm">
              <h2
                class="font-display text-sm font-semibold tracking-wider text-[#f2f5f4]">
                Lass einen Stern auf GitHub da
              </h2>
              <p class="mt-4 text-sm text-[#f2f5f4]/80">
                Zeige, dass dir das Projekt gefällt und gib dem Repo einen Stern
              </p>
              <a
                target="_blank"
                href="https://github.com/Tschucki/pr0p0ll"
                class="mt-6 flex aspect-video h-12 w-full items-center justify-center gap-4 rounded-xl bg-pr0gramm-500 font-semibold text-white transition hover:bg-neutral-800 hover:bg-pr0gramm-500/80">
                <span class="tracking-wide">Liebe teilen</span>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="h-6 w-6">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg>
              </a>
            </form>
          </div>
        </div>
        <div
          class="mb-20 mt-24 flex flex-wrap items-end justify-between gap-x-6 gap-y-4 border-t border-neutral-950/10 pt-12">
          <a
            aria-label="Home"
            href="/"
            class="flex items-center gap-2 font-medium text-[#f2f5f4]">
            <img
              alt="Pr0p0ll Logo"
              title="Pr0p0ll Logo"
              :src="pr0p0llImage"
              class="aspect-square h-4 w-auto sm:h-8" />
            Pr0p0ll
          </a>
          <p class="text-sm text-[#f2f5f4]">ԅ(≖‿≖ԅ)</p>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped></style>
