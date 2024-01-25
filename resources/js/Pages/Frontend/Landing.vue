<script>
import Layout from "../../Layouts/Layout.vue";
import FeatureBlock from "../../components/FeatureBlock.vue";
import Footer from "../../components/Footer.vue";

export default {
  name: "Landing",
  props: {
    userCount: Number,
    pollCount: Number,
  },
  components: {Footer, FeatureBlock, Layout}
}
</script>

<template>
  <Layout>
    <div class="mx-auto max-w-7xl px-6 lg:px-8 mt-24 sm:mt-32 md:mt-56">
      <div class="mx-auto max-w-2xl lg:max-w-none">
        <div class="max-w-3xl" v-motion
             :initial="{ opacity: 0, y: 100 }"
             :enter="{ opacity: 1, y: 0, scale: 1 }"
             :delay="150">
          <h1 class="font-display text-5xl font-medium tracking-tight text-[#f2f5f4] [text-wrap:balance] sm:text-7xl">
            Pr0p0ll ist endlich zurück!
          </h1>
          <p class="mt-6 text-xl text-[#f2f5f4]/80">
            Ein Reboot der alten Pr0p0ll-Plattform für <a href="https://pr0gramm.com">Pr0gramm</a>-Nutzer, um Umfragen
            zu erstellen und zu beantworten. Das Projekt ist komplett Open-Source und ist auf <a
              href="https://github.com/pr0p0ll/pr0p0ll" target="_blank">Github</a> einsehbar
          </p>
        </div>
      </div>

      <div class="mx-auto max-w-7xl lg:px-8 mt-16">
        <div class="mx-auto max-w-2xl lg:max-w-none">
          <div>
            <dl class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:auto-cols-fr lg:grid-flow-col lg:grid-cols-none">
              <a href="https://github.com/Tschucki" target="_blank" v-motion
                 :initial="{ opacity: 0, y: 100 }"
                 :enter="{ opacity: 1, y: 0, scale: 1 }"
                 :delay="300">
                <div
                    class="flex flex-col-reverse pl-8 relative before:absolute after:absolute before:bg-[#f2f5f4] after:bg-[#f2f5f4]/20 before:left-0 before:top-0 before:h-6 before:w-px after:bottom-0 after:left-0 after:top-8 after:w-px"
                    style="opacity: 1; transform: none;">
                  <dt class="mt-2 text-base text-[#f2f5f4]/80">Maintainer</dt>
                  <dd class="font-display text-3xl font-semibold text-[#f2f5f4] sm:text-4xl">Tschucki</dd>
                </div>
              </a>
              <div
                  v-motion
                  :initial="{ opacity: 0, y: 100 }"
                  :enter="{ opacity: 1, y: 0, scale: 1 }"
                  :delay="500"
                  class="flex flex-col-reverse pl-8 relative before:absolute after:absolute before:bg-[#f2f5f4] after:bg-[#f2f5f4]/20 before:left-0 before:top-0 before:h-6 before:w-px after:bottom-0 after:left-0 after:top-8 after:w-px"
                  style="opacity: 1; transform: none;">
                <dt class="mt-2 text-base text-[#f2f5f4]/80">Bisher erstelle Umfragen</dt>
                <dd class="font-display text-3xl font-semibold text-[#f2f5f4] sm:text-4xl">{{ pollCount }}</dd>
              </div>
              <div
                  v-motion
                  :initial="{ opacity: 0, y: 100 }"
                  :enter="{ opacity: 1, y: 0, scale: 1 }"
                  :delay="700"
                  class="flex flex-col-reverse pl-8 relative before:absolute after:absolute before:bg-[#f2f5f4] after:bg-[#f2f5f4]/20 before:left-0 before:top-0 before:h-6 before:w-px after:bottom-0 after:left-0 after:top-8 after:w-px"
                  style="opacity: 1; transform: none;">
                <dt class="mt-2 text-base text-[#f2f5f4]/80">Aktive Nutzer</dt>
                <dd class="font-display text-3xl font-semibold text-[#f2f5f4] sm:text-4xl">{{ userCount }}</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </div>
    <div class="mx-auto max-w-7xl px-6 lg:px-8 pt-20 sm:mt-32 sm:py-32 lg:mt-56" v-motion
         :initial="{ opacity: 0, y: 24 }"
         :visibleOnce="{ opacity: 1, y: 0, scale: 1 }"
         :delay="300">
      <div class="mx-auto max-w-2xl lg:max-w-none">
        <div class="max-w-2xl" style="opacity: 1; transform: none;"><h2><span
            class="block font-display tracking-tight [text-wrap:balance] text-4xl font-medium sm:text-5xl text-[#f2f5f4]">Aktuelle Features und Neuerungen</span>
        </h2>
          <div class="mt-6 text-xl text-[#f2f5f4]/80"><p>Hier eine kurze Liste an Neuerungen, die das neue Pr0p0ll
            mitbringt. Das Projekt befindet sich in stetiger Entwicklung und ist offen für jegliche Kritik und Hilfe</p>
          </div>
        </div>
      </div>
    </div>
    <div class="mx-auto max-w-7xl px-6 lg:px-8 mt-16">
      <div class="mx-auto max-w-2xl lg:max-w-none">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
          <FeatureBlock v-motion
                        :initial="{ opacity: 0, x: 24 }"
                        :visibleOnce="{ opacity: 1, x: 0 }"
                        :delay="200">
            <template #image>
              UI/UX
            </template>
            <template #feature>
              Form-Builder
            </template>
            <template #description>
              Der Form-Builder ermöglicht es euch, Umfragen mit verschiedenen Fragetypen und Antworttypen
              zusammenzustellen.<br><br>So könnt ihr euch eure Umfragen auch schon vor Veröffentlichung anschauen.
            </template>
          </FeatureBlock>
          <FeatureBlock v-motion
                        :initial="{ opacity: 0, x: 24 }"
                        :visibleOnce="{ opacity: 1, x: 0 }"
                        :delay="300">
            <template #image>
              UI/UX
            </template>
            <template #feature>
              Zielgruppen definieren
            </template>
            <template #description>
              Ihr könnt eure Umfragen nun auch für noch spezifischere Zielgruppen freigeben. Ihr könnt das Geschlecht,
              das Alter und die Region des Pr0gramm-Nutzers definieren, der an eure Umfrage teilnehmen darf.
              <br><br>
              Man sieht auch wie viele Nutzer in eure Zielgruppe fallen.
            </template>
          </FeatureBlock>
          <FeatureBlock v-motion
                        :initial="{ opacity: 0, x: 24 }"
                        :visibleOnce="{ opacity: 1, x: 0 }"
                        :delay="400">
            <template #image>
              UX
            </template>
            <template #feature>
              O-Auth
            </template>
            <template #description>
              Ihr könnt euch nun direkt mit eurem Pr0gramm-Account auf Pr0p0ll anmelden. Ihr braucht euch also kein
              neues Konto bei Pr0p0ll anzulegen.
            </template>
          </FeatureBlock>
          <FeatureBlock v-motion
                        :initial="{ opacity: 0, x: 24 }"
                        :visibleOnce="{ opacity: 1, x: 0 }"
                        :delay="450">
            <template #image>
              UX
            </template>
            <template #feature>
              Benachrichtigungssystem
            </template>
            <template #description>
              Alle Nutzer haben die Möglichkeit Benachrichtigungen für relevante Ereignisse zu aktivieren. So werdet ihr
              über neue Umfragen, abgeschlossene Umfragen und Antworten auf eure Umfragen benachrichtigt.
            </template>
          </FeatureBlock>
          <FeatureBlock v-motion
                        :initial="{ opacity: 0, x: 24 }"
                        :visibleOnce="{ opacity: 1, x: 0 }"
                        :delay="500">
            <template #image>
              Nice-To-Haves
            </template>
            <template #feature>
              Bewertung von Umfragen
            </template>
            <template #description>
              Am Ende einer Umfrage könnt ihr diese bewerten. So können andere Nutzer sehen, wie gut eure Umfrage war
              und ihr könnt sehen, wie gut eure Umfragen im Vergleich zu anderen Umfragen abgeschnitten hat.
            </template>
          </FeatureBlock>
          <FeatureBlock v-motion
                        :initial="{ opacity: 0, x: 24 }"
                        :visibleOnce="{ opacity: 1, x: 0 }"
                        :delay="550">
            <template #image>
              Mehr
            </template>
            <template #feature>
              Alle Features
            </template>
            <template #description>
              Hier findet ihr eine Liste aller Features, die das neue Pr0p0ll mitbringt.
            </template>
          </FeatureBlock>
        </div>
      </div>
    </div>
  </Layout>
</template>

<style scoped>

</style>