<template>
  <q-item clickable @click="$router.push(path)">
    <q-item-section avatar>
      <q-icon :name="icon" />
    </q-item-section>
    <q-item-section>
      <q-item-label>{{ label }}</q-item-label>
    </q-item-section>
  </q-item>
</template>

<script>
export default {
  name: 'MenuPositionRoutePush',
  props: ['path', 'icon', 'label']
}
</script>
