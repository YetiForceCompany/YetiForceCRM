<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-item clickable @click="$router.push(path)">
    <q-item-section avatar>
      <q-icon :name="icon"/>
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
