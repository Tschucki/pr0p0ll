<script setup>
import Header from "../components/Header.vue";
</script>

<template>
  <div>
    <Header/>
    <div class="relative flex flex-auto overflow-hidden bg-white pt-14 rounded-t-[40px]">
      <div class="relative isolate flex w-full flex-col pt-9">
        <svg aria-hidden="true"
             class="absolute inset-x-0 -top-14 -z-10 h-[1000px] w-full fill-pr0gramm-50 stroke-pr0gramm-500/10 [mask-image:linear-gradient(to_bottom_left,white_40%,transparent_50%)]">
          <rect width="100%" height="100%" fill="url(#:rd:)" stroke-width="0"></rect>
          <svg x="50%" y="-96" stroke-width="0" class="overflow-visible">
            <path transform="translate(64 160)"
                  d="M45.119 4.5a11.5 11.5 0 0 0-11.277 9.245l-25.6 128C6.82 148.861 12.262 155.5 19.52 155.5h63.366a11.5 11.5 0 0 0 11.277-9.245l25.6-128c1.423-7.116-4.02-13.755-11.277-13.755H45.119Z"></path>
            <path transform="translate(128 320)"
                  d="M45.119 4.5a11.5 11.5 0 0 0-11.277 9.245l-25.6 128C6.82 148.861 12.262 155.5 19.52 155.5h63.366a11.5 11.5 0 0 0 11.277-9.245l25.6-128c1.423-7.116-4.02-13.755-11.277-13.755H45.119Z"></path>
            <path transform="translate(288 480)"
                  d="M45.119 4.5a11.5 11.5 0 0 0-11.277 9.245l-25.6 128C6.82 148.861 12.262 155.5 19.52 155.5h63.366a11.5 11.5 0 0 0 11.277-9.245l25.6-128c1.423-7.116-4.02-13.755-11.277-13.755H45.119Z"></path>
            <path transform="translate(512 320)"
                  d="M45.119 4.5a11.5 11.5 0 0 0-11.277 9.245l-25.6 128C6.82 148.861 12.262 155.5 19.52 155.5h63.366a11.5 11.5 0 0 0 11.277-9.245l25.6-128c1.423-7.116-4.02-13.755-11.277-13.755H45.119Z"></path>
            <path transform="translate(544 640)"
                  d="M45.119 4.5a11.5 11.5 0 0 0-11.277 9.245l-25.6 128C6.82 148.861 12.262 155.5 19.52 155.5h63.366a11.5 11.5 0 0 0 11.277-9.245l25.6-128c1.423-7.116-4.02-13.755-11.277-13.755H45.119Z"></path>
            <path transform="translate(320 800)"
                  d="M45.119 4.5a11.5 11.5 0 0 0-11.277 9.245l-25.6 128C6.82 148.861 12.262 155.5 19.52 155.5h63.366a11.5 11.5 0 0 0 11.277-9.245l25.6-128c1.423-7.116-4.02-13.755-11.277-13.755H45.119Z"></path>
          </svg>
          <defs>
            <pattern id=":rd:" width="96" height="480" x="50%" patternUnits="userSpaceOnUse"
                     patternTransform="translate(0 -96)" fill="none">
              <path
                  d="M128 0 98.572 147.138A16 16 0 0 1 82.883 160H13.117a16 16 0 0 0-15.69 12.862l-26.855 134.276A16 16 0 0 1-45.117 320H-116M64-160 34.572-12.862A16 16 0 0 1 18.883 0h-69.766a16 16 0 0 0-15.69 12.862l-26.855 134.276A16 16 0 0 1-109.117 160H-180M192 160l-29.428 147.138A15.999 15.999 0 0 1 146.883 320H77.117a16 16 0 0 0-15.69 12.862L34.573 467.138A16 16 0 0 1 18.883 480H-52M-136 480h58.883a16 16 0 0 0 15.69-12.862l26.855-134.276A16 16 0 0 1-18.883 320h69.766a16 16 0 0 0 15.69-12.862l26.855-134.276A16 16 0 0 1 109.117 160H192M-72 640h58.883a16 16 0 0 0 15.69-12.862l26.855-134.276A16 16 0 0 1 45.117 480h69.766a15.999 15.999 0 0 0 15.689-12.862l26.856-134.276A15.999 15.999 0 0 1 173.117 320H256M-200 320h58.883a15.999 15.999 0 0 0 15.689-12.862l26.856-134.276A16 16 0 0 1-82.883 160h69.766a16 16 0 0 0 15.69-12.862L29.427 12.862A16 16 0 0 1 45.117 0H128"></path>
            </pattern>
          </defs>
        </svg>
        <main class="w-full flex-auto">
          <slot/>
        </main>
      </div>
    </div>
    <!-- TODO: Create Component -->
    <footer class="mx-auto max-w-7xl px-6 lg:px-8 mt-24 w-full sm:mt-32 lg:mt-40">
      <div class="mx-auto max-w-2xl lg:max-w-none">
        <div style="opacity: 1; transform: none;">
          <div class="grid grid-cols-1 gap-x-8 gap-y-16 lg:grid-cols-2">
            <nav>
              <ul role="list" class="grid grid-cols-2 gap-8 sm:grid-cols-3">
                <li>
                  <div class="font-display text-sm font-semibold tracking-wider text-neutral-950">Rechtliches</div>
                  <ul role="list" class="mt-4 text-sm text-neutral-700">
                    <li class="mt-4"><a class="transition hover:text-neutral-950"
                                        href="/work/family-fund">Impressum</a></li>
                    <li class="mt-4"><a class="transition hover:text-neutral-950" href="/work/unseal">Datenschutz</a>
                    </li>
                    <li class="mt-4"><a class="transition hover:text-neutral-950" href="/work/phobia">Nutzungsbedingungen</a>
                    </li>
                  </ul>
                </li>
                <li>
                  <div class="font-display text-sm font-semibold tracking-wider text-neutral-950">Hilfe</div>
                  <ul role="list" class="mt-4 text-sm text-neutral-700">
                    <li class="mt-4"><a class="transition hover:text-neutral-950" href="/">FAQ</a></li>
                    <li class="mt-4"><a class="transition hover:text-neutral-950" href="/">Diskussionen</a></li>
                    <li class="mt-4"><a class="transition hover:text-neutral-950" href="/">Auswertungen</a></li>
                  </ul>
                </li>
                <li>
                  <div class="font-display text-sm font-semibold tracking-wider text-neutral-950">Kontakt</div>
                  <ul role="list" class="mt-4 text-sm text-neutral-700">
                    <li class="mt-4">
                      <a class="transition hover:text-neutral-950"
                         href="https://pr0gramm.com/inbox/messages/PimmelmannJones">PimmelmannJones (Pr0gramm)</a>
                    </li>
                  </ul>
                </li>
              </ul>
            </nav>
            <div class="flex lg:justify-end">
              <form class="max-w-sm"><h2 class="font-display text-sm font-semibold tracking-wider text-neutral-950">Lass
                einen Stern auf Github da</h2>
                <p class="mt-4 text-sm text-neutral-700">Zeige, dass dir das Projekt gefällt und gib dem Repo einen
                  Stern</p>
                <a target="_blank" href="https://github.com/pr0p0ll/pr0p0ll"
                   class="flex aspect-video h-12 gap-4 w-full mt-6 items-center justify-center rounded-xl bg-neutral-950 text-white transition hover:bg-neutral-800">
                  <span class="tracking-wide">Liebe teilen</span>
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                       stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/>
                  </svg>
                </a>
              </form>
            </div>
          </div>
          <div
              class="mb-20 mt-24 flex flex-wrap items-end justify-between gap-x-6 gap-y-4 border-t border-neutral-950/10 pt-12">
            <a aria-label="Home" href="/">
              Pr0p0ll
            </a>
            <p class="text-sm text-neutral-700">ԅ(≖‿≖ԅ)</p></div>
        </div>
      </div>
    </footer>

  </div>
</template>

<style scoped>

</style>
