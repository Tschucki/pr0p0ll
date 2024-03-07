<script>
import Layout from '../../Layouts/Layout.vue';
import FeatureBlock from '../../components/FeatureBlock.vue';

export default {
  name: 'Landing',
  components: { FeatureBlock },
  layout: (h, page) =>
    h(
      Layout,
      {
        title: 'pr0p0ll - Umfragen für pr0gramm Nutzer',
        description:
          'Ein Reboot der alten pr0p0ll-Plattform für pr0gramm-Nutzer, um Umfragen zu erstellen und zu beantworten. Das Projekt ist Open-Source und wird von Tschucki maintaint',
      },
      () => page
    ),
  props: {
    userCount: String,
    pollCount: String,
  },
  methods: {
    scrollToFeatures: () => {
      document
        .getElementById('aktuelle-features')
        .scrollIntoView({ behavior: 'smooth' });
    },
  },
};
</script>

<template>
  <div class="mx-auto mt-24 max-w-7xl px-6 sm:mt-32 md:mt-56 lg:px-8">
    <div class="mx-auto max-w-2xl lg:max-w-none">
      <div
        v-motion
        class="max-w-3xl"
        :initial="{ opacity: 0, y: 100 }"
        :enter="{ opacity: 1, y: 0, scale: 1 }"
        :delay="150">
        <h1
          class="font-display text-5xl font-medium tracking-tight text-[#f2f5f4] [text-wrap:balance] sm:text-7xl">
          Coming Soon!
        </h1>
        <p class="mt-6 text-xl text-[#f2f5f4]/80">
          Ein Reboot der alten pr0p0ll-Plattform für
          <a href="https://pr0gramm.com">pr0gramm</a>-Nutzer, um Umfragen zu
          erstellen und zu beantworten. Das Projekt ist komplett Open-Source und
          ist auf
          <a href="https://github.com/tschucki/pr0p0ll" target="_blank"
            >GitHub</a
          >
          einsehbar
        </p>
      </div>
    </div>

    <div class="mx-auto mt-16 max-w-7xl lg:px-8">
      <div class="mx-auto max-w-2xl lg:max-w-none">
        <div>
          <dl
            class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:auto-cols-fr lg:grid-flow-col lg:grid-cols-none">
            <a
              v-motion
              href="https://github.com/Tschucki"
              target="_blank"
              :initial="{ opacity: 0, y: 100 }"
              :enter="{ opacity: 1, y: 0, scale: 1 }"
              :delay="300">
              <div
                class="relative flex flex-col-reverse pl-8 before:absolute before:left-0 before:top-0 before:h-6 before:w-px before:bg-[#f2f5f4] after:absolute after:bottom-0 after:left-0 after:top-8 after:w-px after:bg-[#f2f5f4]/20"
                style="opacity: 1; transform: none">
                <dt class="mt-2 text-base text-[#f2f5f4]/80">Maintainer</dt>
                <dd
                  class="font-display text-3xl font-semibold text-[#f2f5f4] sm:text-4xl">
                  Tschucki
                </dd>
              </div>
            </a>
            <div
              v-motion
              :initial="{ opacity: 0, y: 100 }"
              :enter="{ opacity: 1, y: 0, scale: 1 }"
              :delay="500"
              class="relative flex flex-col-reverse pl-8 before:absolute before:left-0 before:top-0 before:h-6 before:w-px before:bg-[#f2f5f4] after:absolute after:bottom-0 after:left-0 after:top-8 after:w-px after:bg-[#f2f5f4]/20"
              style="opacity: 1; transform: none">
              <dt class="mt-2 text-base text-[#f2f5f4]/80">
                Bisher erstelle Umfragen
              </dt>
              <dd
                class="font-display text-3xl font-semibold text-[#f2f5f4] sm:text-4xl">
                {{ pollCount }}
              </dd>
            </div>
            <div
              v-motion
              :initial="{ opacity: 0, y: 100 }"
              :enter="{ opacity: 1, y: 0, scale: 1 }"
              :delay="700"
              class="relative flex flex-col-reverse pl-8 before:absolute before:left-0 before:top-0 before:h-6 before:w-px before:bg-[#f2f5f4] after:absolute after:bottom-0 after:left-0 after:top-8 after:w-px after:bg-[#f2f5f4]/20"
              style="opacity: 1; transform: none">
              <dt class="mt-2 text-base text-[#f2f5f4]/80">Aktive Nutzer</dt>
              <dd
                class="font-display text-3xl font-semibold text-[#f2f5f4] sm:text-4xl">
                {{ userCount }}
              </dd>
            </div>
          </dl>
        </div>
      </div>
    </div>

    <div
      v-motion
      class="mt-12 hidden cursor-pointer flex-col items-center gap-2 sm:flex"
      :initial="{ opacity: 0, y: 100 }"
      :enter="{ opacity: 1, y: 0, scale: 1 }"
      :delay="900"
      @click="scrollToFeatures">
      <p class="text-base text-[#f2f5f4]/80">Weiter</p>
      <svg
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
        class="h-8 w-8 text-[#f2f5f4]">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="m9 12.75 3 3m0 0 3-3m-3 3v-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
      </svg>
    </div>
  </div>
  <div
    id="aktuelle-features"
    v-motion
    class="lg:mt-42 mx-auto max-w-7xl px-6 pt-20 sm:mt-32 sm:py-32 lg:px-8"
    :initial="{ opacity: 0, y: 24 }"
    :visibleOnce="{ opacity: 1, y: 0, scale: 1 }"
    :delay="300">
    <div class="mx-auto max-w-2xl lg:max-w-none">
      <div class="max-w-2xl" style="opacity: 1; transform: none">
        <h2>
          <span
            class="font-display block text-4xl font-medium tracking-tight text-[#f2f5f4] [text-wrap:balance] sm:text-5xl"
            >Aktuelle Features und Neuerungen</span
          >
        </h2>
        <div class="mt-6 text-xl text-[#f2f5f4]/80">
          <p>
            Hier eine kurze Liste an Neuerungen, die das neue pr0p0ll mitbringt.
            Das Projekt befindet sich in stetiger Entwicklung und ist offen für
            jegliche Kritik und Hilfe
          </p>
        </div>
      </div>
    </div>
  </div>
  <div class="mx-auto mt-16 max-w-7xl px-6 lg:px-8">
    <div class="mx-auto max-w-2xl lg:max-w-none">
      <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <FeatureBlock
          v-motion
          :initial="{ opacity: 0, x: 24 }"
          :visibleOnce="{ opacity: 1, x: 0 }"
          :delay="200">
          <template #image>UI/UX</template>
          <template #feature>Form-Builder</template>
          <template #description>
            Der Form-Builder ermöglicht es euch, Umfragen mit verschiedenen
            Fragetypen und Antworttypen zusammenzustellen.<br /><br />So könnt
            ihr euch eure Umfragen auch schon vor Veröffentlichung anschauen.
          </template>
        </FeatureBlock>
        <FeatureBlock
          v-motion
          :initial="{ opacity: 0, x: 24 }"
          :visibleOnce="{ opacity: 1, x: 0 }"
          :delay="300">
          <template #image>UI/UX</template>
          <template #feature>Zielgruppen definieren</template>
          <template #description>
            Ihr könnt eure Umfragen nun auch für noch spezifischere Zielgruppen
            freigeben. Ihr könnt das Geschlecht, das Alter und die Region des
            pr0gramm-Nutzers definieren, der an eure Umfrage teilnehmen darf.
            <br /><br />
            Man sieht auch wie viele Nutzer in eure Zielgruppe fallen.
          </template>
        </FeatureBlock>
        <FeatureBlock
          v-motion
          :initial="{ opacity: 0, x: 24 }"
          :visibleOnce="{ opacity: 1, x: 0 }"
          :delay="400">
          <template #image>UX</template>
          <template #feature>O-Auth</template>
          <template #description>
            Ihr könnt euch nun direkt mit eurem pr0gramm-Account auf pr0p0ll
            anmelden. Ihr braucht euch also kein neues Konto bei pr0p0ll
            anzulegen.
          </template>
        </FeatureBlock>
        <FeatureBlock
          v-motion
          :initial="{ opacity: 0, x: 24 }"
          :visibleOnce="{ opacity: 1, x: 0 }"
          :delay="450">
          <template #image>UX</template>
          <template #feature>Benachrichtigungen</template>
          <template #description>
            Alle Nutzer haben die Möglichkeit Benachrichtigungen für relevante
            Ereignisse zu aktivieren. So werdet ihr über neue Umfragen,
            abgeschlossene Umfragen und Antworten auf eure Umfragen
            benachrichtigt.
          </template>
        </FeatureBlock>
        <FeatureBlock
          v-motion
          :initial="{ opacity: 0, x: 24 }"
          :visibleOnce="{ opacity: 1, x: 0 }"
          :delay="500">
          <template #image>Nice-To-Haves</template>
          <template #feature>Bewertung von Umfragen</template>
          <template #description>
            Am Ende einer Umfrage könnt ihr diese bewerten. So können andere
            Nutzer sehen, wie gut eure Umfrage war und ihr könnt sehen, wie gut
            eure Umfragen im Vergleich zu anderen Umfragen abgeschnitten haben.
          </template>
        </FeatureBlock>
        <FeatureBlock
          v-motion
          :initial="{ opacity: 0, x: 24 }"
          :visibleOnce="{ opacity: 1, x: 0 }"
          :delay="550">
          <template #image>Mehr</template>
          <template #feature>Alle Features</template>
          <template #description>
            Hier findet ihr eine Liste aller Features, die das neue pr0p0ll
            mitbringt.
          </template>
        </FeatureBlock>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
