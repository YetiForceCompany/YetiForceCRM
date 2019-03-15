<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-header elevated class="bg-white text-muted">
    <q-toolbar>
      <y-global-search />
      <y-global-actions class="q-ml-auto" />
    </q-toolbar>
  </q-header>
</template>
<script>
import YGlobalSearch from 'components/Base/YGlobalSearch.vue'
import YGlobalActions from 'components/Base/YGlobalActions.vue'
export default {
  name: 'YHeader',
  components: { YGlobalSearch, YGlobalActions },
  data() {
    return {
      iconSize: '.75rem'
    }
  }
}
</script>

<style module lang="stylus"></style>
