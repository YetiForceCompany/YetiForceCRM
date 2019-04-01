<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    <q-layout view="hHh lpR fFf">
      <template v-if="isLoggedIn">
        <yf-header>
          <template slot="left">
            <q-btn
              dense
              flat
              round
              icon="mdi-menu"
              @click="leftDrawerOpen = !leftDrawerOpen"
              v-show="!$q.platform.is.desktop"
            />
          </template>
        </yf-header>
        <q-drawer
          v-model="leftDrawerOpen"
          content-class="bg-blue-grey-10 text-white"
          :mini="miniState ? miniState : false"
          @mouseover="miniState = false && menuEvents"
          @mouseout="miniState = true && menuEvents"
          :width="200"
          :breakpoint="500"
          :show-if-above="miniState"
        >
          <q-scroll-area class="menuItemsContainer" style="height: calc(100% - 56px)">
            <left-menu />
          </q-scroll-area>
          <q-separator dark />
          <q-item class="fixed-bottom">
            <q-item-section></q-item-section>
            <q-item-section class="justify-end">
              <q-toggle
                class="col-shrink"
                v-show="$q.platform.is.desktop"
                v-model="menuEvents"
                :true-value="false"
                :false-value="true"
                icon="mdi-pin"
              />
            </q-item-section>
          </q-item>
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
import getters from '/store/getters.js'
import LeftMenu from '/Core/modules/Menu/components/LeftMenu.vue.js'
import YfHeader from '/Core/components/YfHeader.vue.js'
import YfFooter from '/Core/components/YfFooter.vue.js'

export default {
  name: 'Basic',
  components: {
    LeftMenu,
    YfHeader,
    YfFooter
  },
  data() {
    return {
      leftDrawerOpen: false,
      miniState: this.$q.platform.is.desktop,
      menuEvents: true
    }
  },
  computed: {
    ...Vuex.mapGetters({
      isLoggedIn: getters.Core.Users.isLoggedIn
    })
  },
  methods: {
    openURL() {
      this.route.openURL
    }
  }
}
</script>

<style module lang="stylus">
.menuItemsContainer {
  height: calc(100% - 56px);
}
</style>
