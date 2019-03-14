<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-footer elevated class="bg-blue-grey-10 text-blue-grey-11">
    <q-toolbar class="q-pl-none">
      <q-toolbar-title :class="[$style.footerLeft, 'flex flex-center q-px-none']">
        <q-avatar>
          <img :src="env.publicDir + '/statics/Logo/white_logo_yetiforce.png'" alt>
        </q-avatar>
      </q-toolbar-title>
      <q-separator class="q-my-xs" dark vertical />
      <y-breadcrumbs />
      <y-yeti-info :class="[$style.footerRight, 'q-pl-auto']" />
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
      env: getters.Base.Env.all
    })
  }
}
</script>

<style module lang="stylus">
@import '~quasar-variables';

.footerLeft {
  min-width: 57px;
}

.footerRight {
  a {
    color: $blue-grey-11;
  }
}
</style>
