<!--
/**
 * Shortcut menu item editor
 *
 * @description Used in popup window to add shortcut item to menu.
 * @license YetiForce Public License 3.0
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */
-->
<template>
  <hook-wrapper>
    <icon-selector v-model="icon" @change="changed" />
  </hook-wrapper>
</template>

<script>
import IconSelector from '/Core/modules/Icons/components/IconSelector.vue.js'
const moduleName = 'Settings.Menu.Components.Shortcut'
export default {
  name: moduleName,
  components: {
    IconSelector
  },
  data() {
    return {
      icon: null
    }
  },
  methods: {
    changed(icon) {
      console.log(icon.name, this.icon)
    }
  }
}
</script>

<style>

</style>
