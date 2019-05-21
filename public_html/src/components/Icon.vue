
<template>
  <div>
    <q-icon v-if="/^mdi|^fa/.test(icon)" :name="icon" />
    <q-icon v-else :class="[icon, 'q-icon']" />
  </div>
</template>

<script>
export default {
  name: 'Icon',
  props: {
    icon: {
      type: String,
      required: true
    }
  }
}
</script>

<style scoped>
</style>
