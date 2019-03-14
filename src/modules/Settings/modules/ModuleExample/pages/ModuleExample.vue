<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    <hook name="before"/>Layout -> Settings -> ModuleExample page
    <hook name="after"/>
  </div>
</template>
<script>
import Hook from 'modules/Base/modules/Hooks/components/Hook.vue'

const moduleName = 'Settings.ModuleExample.Pages.ModuleExample'
export default {
  name: moduleName,
  components: { Hook }
}
</script>
