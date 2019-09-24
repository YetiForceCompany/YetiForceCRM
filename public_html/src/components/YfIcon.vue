<!--
/**
 * YfIcon component
 *
 * @description Global component
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
<template>
  <div class="flex">
    <q-icon v-if="/^mdi|^fa/.test(icon)" :name="icon" :size="size" dense />
    <q-avatar v-else-if="icon.includes('/')" :size="size">
      <q-img :src="icon" />
    </q-avatar>
    <q-icon v-else :class="[icon, 'q-icon']" :style="{ 'font-size': size ? size : '1.4em' }" />
  </div>
</template>

<script>
export default {
  name: 'YfIcon',
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
