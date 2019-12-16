/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
<template>
  <div>
    <Photoshop v-model="colors"></Photoshop>
  </div>
</template>

<script>
import VueColor from 'vue-color'

var colors = {
  hex: '#194d33',
  hsl: { h: 150, s: 0.5, l: 0.2, a: 1 },
  hsv: { h: 150, s: 0.66, v: 0.3, a: 1 },
  rgba: { r: 25, g: 77, b: 51, a: 1 },
  a: 1
}

export default {
  components: { Photoshop: VueColor.Photoshop },
  data() {
    return { colors }
  },
  created() {
    console.log('asdfadsf')
  }
}
</script>

<style lang="scss" scoped>
</style>
