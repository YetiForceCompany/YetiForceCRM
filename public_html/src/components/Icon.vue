
<template>
  <div>
    <q-icon v-if="/^mdi|^fa/.test(icon)" :name="icon" :size="size" dense />
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
