<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-layout view="hHh LpR fFf" container :class="['bg-white', miniMode ? 'chat-mini' : '']">
    <ChatHeader @visibleInputSearch="inputSearchVisible = $event" @showTabHistory="tabHistoryShow = $event" />
    <ChatLeftPanel :drawerBreakpoint="drawerBreakpoint">
      <template #top>
        <YfBackdrop v-show="tab !== 'chat'" />
      </template>
    </ChatLeftPanel>
    <q-drawer :breakpoint="drawerBreakpoint" no-swipe-close no-swipe-open :show-if-above="false" v-model="rightPanel" side="right" bordered @input="onDrawerClose">
      <ChatRightPanel :participants="currentRoomData.participants || []">
        <template #top>
          <YfBackdrop v-show="tab !== 'chat'" />
        </template>
      </ChatRightPanel>
    </q-drawer>
    <ChatMainPanel />
    <ChatFooter />
  </q-layout>
</template>
<script>
import ChatLeftPanel from './ChatLeftPanel.vue'
import ChatRightPanel from './ChatRightPanel.vue'
import ChatMainPanel from './ChatMainPanel.vue'
import ChatHeader from './ChatHeader.vue'
import ChatFooter from './ChatFooter.vue'
import YfBackdrop from 'components/YfBackdrop.vue'

import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')
export default {
  name: 'ChatContainer',
  components: { ChatLeftPanel, ChatRightPanel, ChatMainPanel, ChatHeader, ChatFooter, YfBackdrop },
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
