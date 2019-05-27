/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

<template>
  <div>
    <q-resize-observer @resize="onResize" />
    <q-carousel
      v-if="record.knowledgebase_view === 'PLL_PRESENTATION'"
      v-model="slide"
      transition-prev="scale"
      transition-next="scale"
      swipeable
      animated
      control-color="black"
      navigation
      padding
      arrows
      :style="{ height: height }"
      class="bg-white text-black shadow-1 rounded-borders"
      ref="carousel"
    >
      <q-carousel-slide
        v-for="(slide, index) in record.content"
        :name="index"
        :key="index"
        class="column no-wrap flex-center"
        :fullscreen.sync="fullscreen"
      >
        <div v-html="slide"></div>
      </q-carousel-slide>
      <template v-slot:control>
        <q-carousel-control position="bottom-right" :offset="[18, 18]">
          <q-btn
            push
            round
            dense
            color="white"
            text-color="primary"
            :icon="fullscreen ? 'mdi-fullscreen-exit' : 'mdi-fullscreen'"
            @click="fullscreen = !fullscreen"
          />
        </q-carousel-control>
      </template>
    </q-carousel>
  </div>
</template>

<script>
import { dom } from 'quasar/src/utils.js'
const { offset, ready, height } = dom
export default {
  name: 'Carousel',
  data() {
    return {
      slide: 0,
      height: '300px',
      report: 0,
      fullscreen: false
    }
  },
  props: {
    record: {
      type: Object,
      required: true
    }
  },
  watch: {
    fullscreen: function(val) {
      if (val) {
        this.$q.fullscreen.request()
      } else {
        this.$q.fullscreen.exit()
      }
    }
  },
  methods: {
    setHeight() {},
    onResize(size) {
      this.height = `${this.$q.screen.height - offset(this.$refs.carousel.$el).top}px`
    }
  }
}
</script>

<style scoped>
</style>
