<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-footer elevated class="bg-blue-grey-10 text-grey-12">
    <q-toolbar class="q-px-none">
      <q-toolbar-title :class="[$style.footerLeft, 'flex flex-center q-px-none']">
        <q-avatar>
          <img :src="env.publicDir + '/statics/Logo/white_logo_yetiforce.png'" alt>
        </q-avatar>
      </q-toolbar-title>
      <q-separator class="q-my-xs" dark vertical/>
      <q-toolbar>
        <q-breadcrumbs class>
          <q-breadcrumbs-el icon="home" to="/"/>
          <q-breadcrumbs-el label="Docs" icon="widgets" to="/docs"/>
          <q-breadcrumbs-el label="Breadcrumbs" icon="navigation" to="/vue-components/breadcrumbs"/>
          <q-breadcrumbs-el label="Build" icon="build"/>
        </q-breadcrumbs>
      </q-toolbar>
      <div :class="[$style.footerRight, 'q-pl-auto q-pr-xl text-blue-grey-11']">
        <div>&copy; YetiForce.com All rights reserved.</div>
        <q-separator dark/>
        <div class="flex flex no-wrap q-py-xs">
          <a
            class="q-link"
            href="https://www.linkedin.com/groups/8177576"
            rel="noreferrer noopener"
          >
            <q-icon size="16px" name="mdi-linkedin" title="Linkedin"></q-icon>
          </a>
          <q-separator class="q-mx-md" dark vertical/>
          <a class="q-link" href="https://twitter.com/YetiForceEN" rel="noreferrer noopener">
            <q-icon size="16px" name="mdi-twitter" title="Twitter"></q-icon>
          </a>
          <q-separator class="q-mx-md" dark vertical/>
          <a
            class="q-link"
            href="https://www.facebook.com/YetiForce-CRM-158646854306054/"
            rel="noreferrer noopener"
          >
            <q-icon size="16px" name="mdi-facebook" title="Facebook"></q-icon>
          </a>
          <q-separator class="q-mx-md" dark vertical/>
          <a
            class="q-link"
            href="https://github.com/YetiForceCompany/YetiForceCRM"
            rel="noreferrer noopener"
          >
            <q-icon size="16px" name="mdi-github-circle" title="Github"></q-icon>
          </a>
          <q-separator class="q-mx-md" dark vertical/>

          <a class="q-link" href="https://yetiforce.shop" rel="noreferrer noopener">
            <q-icon size="18px" name="mdi-cart-outline" title="yetiforce.shop"></q-icon>
          </a>
          <q-separator class="q-mx-md" dark vertical/>
          <a class="q-link" href="#" role="button">
            <q-icon size="18px" name="mdi-information-outline" title="YetiForceCRM"></q-icon>
          </a>
        </div>
      </div>
    </q-toolbar>
  </q-footer>
</template>

<script>
import getters from 'src/store/getters.js'
import { mapGetters } from 'vuex'
export default {
  name: 'YFooter',
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
