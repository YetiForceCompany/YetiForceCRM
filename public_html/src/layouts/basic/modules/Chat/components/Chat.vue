<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-layout view="hHh LpR fFf" container :class="['bg-white', miniMode ? 'chat-mini' : '']">
    <chat-header @visibleInputSearch="inputSearchVisible = $event" @showTabHistory="tabHistoryShow = $event" />
    <left-panel :drawerBreakpoint="drawerBreakpoint">
      <template v-slot:top>
        <backdrop v-show="tab !== 'chat'" />
      </template>
    </left-panel>
    <q-drawer :breakpoint="drawerBreakpoint" no-swipe-close no-swipe-open :show-if-above="false" v-model="rightPanel" side="right" bordered @input="onDrawerClose">
      <right-panel :participants="currentRoomData.participants || []">
        <template v-slot:top>
          <backdrop v-show="tab !== 'chat'" />
        </template>
      </right-panel>
    </q-drawer>
    <main-panel />
    <chat-footer />
  </q-layout>
</template>
<script>
import LeftPanel from './LeftPanel.vue'
import RightPanel from './RightPanel.vue'
import MainPanel from './MainPanel.vue'
import ChatHeader from './ChatHeader.vue'
import ChatFooter from './ChatFooter.vue'
import Backdrop from 'components/Backdrop.vue'

import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')
export default {
  name: 'Chat',
  components: { LeftPanel, RightPanel, MainPanel, ChatHeader, ChatFooter, Backdrop },
  props: {
    parentRefs: { type: Object, required: true }
  },
  data() {
    return {
			drawerBreakpoint: 1023
		}
  },
  computed: {
		...mapGetters(['data', 'miniMode', 'tab', 'currentRoomData']),
		rightPanel: {
      get() {
        return this.$store.getters['Chat/rightPanel']
      },
      set() {
      }
    },
  },
  methods: {
		...mapMutations(['setRightPanel']),
		onDrawerClose(ev) {
			if(!ev) {
				this.setRightPanel(false)
			}
		}
  }
}
</script>
<style>
</style>
