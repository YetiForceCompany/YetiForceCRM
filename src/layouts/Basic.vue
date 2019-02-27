<template>
  <q-layout view="lHr Lpr lff">
    <q-header elevated class="bg-white text-black">
      <q-toolbar>
        <q-btn dense flat round icon="menu" @click="leftDrawerOpen = !leftDrawerOpen" />
        <q-toolbar-title>YetiForceCRM</q-toolbar-title>
      </q-toolbar>
    </q-header>
    <q-drawer
      v-model="leftDrawerOpen"
      content-class="bg-blue-grey-10 text-white"
      :mini="$q.platform.is.mobile ? !leftDrawerOpen : miniState"
      @mouseover="miniState = false"
      @mouseout="miniState = true"
      :width="200"
      :breakpoint="500"
      show-if-above
    >
      <left-menu />
    </q-drawer>
    <q-page-container>
      <router-view />
    </q-page-container>
    <q-footer class="bg-grey-12 text-black">
      <q-toolbar>
        <div class="q-px-md q-gutter-sm">
          <q-breadcrumbs>
            <q-breadcrumbs-el icon="home" to="/" />
            <q-breadcrumbs-el label="Docs" icon="widgets" to="/docs" />
            <q-breadcrumbs-el label="Breadcrumbs" icon="navigation" to="/vue-components/breadcrumbs" />
            <q-breadcrumbs-el label="Build" icon="build" />
          </q-breadcrumbs>
        </div>
      </q-toolbar>
    </q-footer>
  </q-layout>
</template>

<script>
import { openURL } from 'quasar'
import LeftMenu from 'components/Base/Menu/LeftMenu.vue'

export default {
  name: 'Basic',
  components: {
    LeftMenu
  },
  data() {
    return {
      leftDrawerOpen: this.$q.platform.is.desktop,
      miniState: true
    }
  },
  methods: {
    openURL
  }
}
</script>

<style></style>
