<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-expansion-item :icon="icon" :label="label" :content-inset-level="0.5">
    <menu-item v-for="child in children" :key="child.id" :item="child" />
  </q-expansion-item>
</template>
<script>
const moduleName = 'Core.Menu.Items.Expander'
export default {
  name: moduleName,
  components: {
    MenuItem: () => import('./Item.vue')
  },
  props: ['icon', 'label', 'children']
}
</script>
