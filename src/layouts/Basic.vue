<template>
  <div>
    <q-layout view="lHh lpR fFf">
      <y-header></y-header>
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
      <q-page-container>
        <router-view />
      </q-page-container>
      <y-footer></y-footer>
    </q-layout>
  </div>
</template>

<script>
import { openURL } from 'quasar'
import LeftMenu from 'App/modules/Core/modules/Menu/components/LeftMenu.vue'
import YHeader from 'components/Base/YHeader.vue'
import YFooter from 'components/Base/YFooter.vue'

export default {
  name: 'Basic',
  components: {
    LeftMenu,
    YHeader,
    YFooter
  },
  data() {
    return {
      leftDrawerOpen: this.$q.platform.is.desktop,
      miniState: true,
      menuEvents: true
    }
  },
  methods: {
    openURL
  }
}
</script>

<style module lang="stylus"></style>
