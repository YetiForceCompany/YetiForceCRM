<template>
  <div>
    <q-layout view="lHh lpR fFf">
      <template v-if="isLoggedIn">
        <yf-header></yf-header>
        <q-drawer
          v-model="leftDrawerOpen"
          content-class="bg-blue-grey-10 text-white"
          :mini="$q.platform.is.mobile ? !leftDrawerOpen : miniState"
          @mouseover="miniState = false && menuEvents"
          @mouseout="miniState = true && menuEvents"
          :width="200"
          :breakpoint="500"
          show-if-above
        >
          <q-btn dense flat round icon="mdi-menu" @click="menuEvents = !menuEvents" class="q-ml-sm" />
          <left-menu />
        </q-drawer>
        <yf-footer></yf-footer>
      </template>
      <q-page-container>
        <router-view />
      </q-page-container>
    </q-layout>
  </div>
</template>

<script>
import { openURL } from 'quasar'
import { mapGetters } from 'vuex'
import getters from 'store/getters.js'
import LeftMenu from 'Core/modules/Menu/components/LeftMenu.vue'
import YfHeader from 'components/Base/YfHeader.vue'
import YfFooter from 'components/Base/YfFooter.vue'

export default {
  name: 'Basic',
  components: {
    LeftMenu,
    YfHeader,
    YfFooter
  },
  data() {
    return {
      leftDrawerOpen: this.$q.platform.is.desktop,
      miniState: true,
      menuEvents: true
    }
  },
  computed: {
    ...mapGetters({
      isLoggedIn: getters.Core.Users.isLoggedIn
    })
  },
  methods: {
    openURL
  }
}
</script>

<style module lang="stylus"></style>
