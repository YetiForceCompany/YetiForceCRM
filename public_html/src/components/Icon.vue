/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

<template>
  <div>
    <q-icon v-if="/^mdi|^fa/.test(icon)" :name="icon" :size="size" dense />
    <q-avatar v-else-if="icon.includes('/')" :size="size">
      <q-img :src="icon" />
    </q-avatar>
    <q-icon v-else :class="[icon, 'q-icon']" :style="{ 'font-size': size }" />
  </div>
</template>

<script>
export default {
  name: 'Icon',
  props: {
    icon: {
      type: String,
      required: true
    },
    size: {
      type: String,
      required: false
    }
  }
}
</script>

<style scoped>
</style>
