<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-footer elevated class="bg-blue-grey-10 text-blue-grey-11 row no-wrap">
    <div :class="[$style.footerLeft, 'flex flex-center gt-xs q-px-none']">
      <q-avatar size="32px" class="q-mx-auto">
        <img :src="env.publicDir + '/statics/Logo/white_logo_yetiforce.png'" alt="yetiforce logo" />
      </q-avatar>
      <q-separator dark vertical class="q-my-xs" style="min-height: unset" />
    </div>
    <q-toolbar>
      <div class="flex wrap full-width items-center">
        <y-breadcrumbs />
        <y-yeti-info containerClass="q-ml-auto" />
      </div>
    </q-toolbar>
  </q-footer>
</template>

<script>
import getters from 'src/store/getters.js'
import { mapGetters } from 'vuex'
import YBreadcrumbs from 'components/Base/YBreadcrumbs'
import YYetiInfo from 'components/Base/YYetiInfo'

export default {
  name: 'YFooter',
  components: { YBreadcrumbs, YYetiInfo },
  data() {
    return {
      searchModule: 'All records',
      searchModules: ['All records', 'acc', 'con'],
      searchText: '',
      iconSize: '.75rem'
    }
  },
  computed: {
    ...mapGetters({
      env: getters.Env.all
    })
  }
}
</script>

<style module lang="stylus">
.footerLeft {
  min-width: 57px;
  max-width: 57px;
}
</style>
