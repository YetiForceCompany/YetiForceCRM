<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <hook-wrapper>
    <q-header elevated class="bg-white text-muted">
      <q-toolbar>
        <q-toolbar-title class="col-auto">
          <q-avatar>
            <img src="https://cdn.quasar-framework.org/logo/svg/quasar-logo.svg" />
          </q-avatar>
        </q-toolbar-title>
        <yf-global-search />
        <yf-global-actions class="q-ml-auto" />
      </q-toolbar>
    </q-header>
  </hook-wrapper>
</template>
<script>
import YfGlobalSearch from 'components/Base/YfGlobalSearch.vue'
import YfGlobalActions from 'components/Base/YfGlobalActions.vue'
export default {
  name: 'YfHeader',
  components: { YfGlobalSearch, YfGlobalActions },
  data() {
    return {
      iconSize: '.75rem'
    }
  }
}
</script>

<style module lang="stylus"></style>
