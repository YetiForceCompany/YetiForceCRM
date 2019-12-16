/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
<template>
  <div>
    <Photoshop v-model="color"></Photoshop>
  </div>
</template>

<script>
import VueColor from 'vue-color'

export default {
  components: { Photoshop: VueColor.Photoshop },
  props: {
    currentColor: {
      type: String
    }
  },
  data() {
    return {
      newColor: null
    }
  },
  computed: {
    color: {
      get() {
        return this.currentColor
      },
      set(val) {
        this.newColor = val
      }
    }
  },
  methods: {
    getColor() {
      return this.newColor
    }
  },
  mounted() {
    this.newColor = this.currentColor
  }
}
</script>

<style lang="scss" scoped>
</style>
