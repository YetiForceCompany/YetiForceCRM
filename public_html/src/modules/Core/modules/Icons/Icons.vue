<!--
/**
 * Icons module
 *
 * @description Icon related functionality, IconSelector component and list of all icons inside vuex.
 * @license YetiForce Public License 3.0
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */
-->
<template></template>

<script>
import ModuleLoader from '/src/ModuleLoader.js'
import moduleStore from './store/index.js'

const moduleName = 'Core.Icons'

export function initialize({ store }) {
  store.registerModule(moduleName.split('.'), ModuleLoader.prepareStoreNames(moduleName, moduleStore))
}

export default {
  name: moduleName
}
</script>

<style></style>
