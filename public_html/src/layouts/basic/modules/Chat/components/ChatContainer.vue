<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-layout view="hHh LpR fFf" container :class="['bg-white', miniMode ? 'chat-mini' : '']">
    <ChatHeader @visibleInputSearch="inputSearchVisible = $event" @showTabHistory="tabHistoryShow = $event" />
    <ChatLeftPanel>
      <template #top>
        <YfBackdrop v-show="tab !== 'chat'" />
      </template>
    </ChatLeftPanel>
    <q-drawer
      v-model="computedModel"
      :class="{ 'backdrop-fix': mobileMode && !computedModel }"
      :breakpoint="layout.drawer.breakpoint"
      no-swipe-close
      no-swipe-open
      bordered
      :show-if-above="false"
      side="right"
    >
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
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'ChatContainer',
  components: { ChatLeftPanel, ChatRightPanel, ChatMainPanel, ChatHeader, ChatFooter, YfBackdrop },
  props: {
    parentRefs: { type: Object, required: true }
  },
  computed: {
    ...mapGetters([
      'data',
      'miniMode',
      'mobileMode',
      'tab',
      'currentRoomData',
      'layout',
      'rightPanel',
      'rightPanelMobile'
    ]),
    computedModel: {
      get() {
        return this.mobileMode ? this.rightPanelMobile : this.rightPanel
      },
      set(isOpen) {
        if (this.mobileMode) {
          this.setRightPanelMobile(isOpen)
        }
      }
    }
  },
  methods: {
    ...mapMutations(['setRightPanelMobile']),
  }
}
</script>
<style>
</style>
