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
          <left-menu />
          <q-item>
            <q-item-section></q-item-section>
            <q-item-section>
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
      leftDrawerOpen: false,
      miniState: this.$q.platform.is.desktop,
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
