<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <hook-wrapper class="Core-Component-YfHeader">
    <q-header elevated class="bg-white text-muted">
      <q-toolbar>
        <q-toolbar-title class="col-auto">
          <q-avatar>
            <img :src="env.publicDir + '/statics/Logo/blue_yetiforce_logo.png'" />
          </q-avatar>
        </q-toolbar-title>
        <yf-global-search />
        <yf-global-actions class="q-ml-auto" />
      </q-toolbar>
    </q-header>
  </hook-wrapper>
</template>
<script>
import YfGlobalSearch from 'Core/components/YfGlobalSearch.vue'
import YfGlobalActions from 'Core/components/YfGlobalActions.vue'
import { mapGetters } from 'vuex'
import getters from 'store/getters.js'
export default {
  name: 'YfHeader',
  components: { YfGlobalSearch, YfGlobalActions },
  data() {
    return {
      iconSize: '.75rem'
    }
  },
  computed: {
    ...mapGetters({
      env: getters.Core.Env.all
    })
  }
}
</script>

<style module lang="stylus"></style>
