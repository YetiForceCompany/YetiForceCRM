
<template>
  <div>
    <q-carousel
      ref="carousel"
      v-model="slide"
      :class="['quasar-reset shadow-1 rounded-borders', !fullscreen ? 'carousel-height' : '']"
      :fullscreen.sync="fullscreen"
      transition-prev="scale"
      transition-next="scale"
      swipeable
      animated
      control-color="black"
      navigation
      padding
      arrows
      @transition="onTransition"
    >
      <q-carousel-slide
        v-for="(slide, index) in record.content"
        :key="index"
        class="column no-wrap flex-center"
        :name="index"
        :fullscreen.sync="fullscreen"
      >
        <div
          class="full-height"
          v-html="slide"
        ></div>
      </q-carousel-slide>
      <template #control>
        <q-carousel-control
          position="bottom-right"
          :offset="[18, 18]"
        >
          <q-btn
            :icon="fullscreen ? 'mdi-fullscreen-exit' : 'mdi-fullscreen'"
            push
            round
            dense
            color="white"
            text-color="primary"
            @click="fullscreen = !fullscreen"
          />
        </q-carousel-control>
      </template>
    </q-carousel>
  </div>
</template>

<script>
export default {
  name: 'Carousel',
  data() {
    return {
      slide: 0,
      height: '90vh',
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
    },
    '$q.fullscreen.isActive'(val) {
      this.fullscreen = val
    }
  },
  methods: {
    onTransition(size) {
      const scrollbarWidth = 17
      $(this.$refs.carousel.$el)
        .find('img')
        .css('max-width', $(this.$refs.carousel.$el).width() - scrollbarWidth)
    }
  }
}
</script>

<style scoped>
.carousel-height {
  height: max-content;
  min-height: calc(100vh - 31.14px);
}
</style>
