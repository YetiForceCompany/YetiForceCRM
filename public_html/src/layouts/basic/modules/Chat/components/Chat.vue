<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-layout view="hHh LpR fFf" container :class="['bg-white', maximizedDialog ? 'chat-mini' : '']">
    <chat-header @visibleInputSearch="inputSearchVisible = $event" @showTabHistory="tabHistoryShow = $event" />
    <left-panel @footerGroup="groupFooter = $event" @footerRoom="roomFooter = $event" />
    <right-panel />
    <messages />
    <chat-footer :groupFooter="groupFooter" :roomFooter="roomFooter" />
  </q-layout>
</template>
<script>
import LeftPanel from './LeftPanel.vue'
import RightPanel from './RightPanel.vue'
import Messages from './Messages.vue'
import ChatHeader from './ChatHeader.vue'
import ChatFooter from './ChatFooter.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters } = createNamespacedHelpers('Chat')
export default {
  name: 'Chat',
  components: { LeftPanel, RightPanel, Messages, ChatHeader, ChatFooter },
  data() {
    return {
      iconSize: '.75rem',
      placeholder: 'Wyszukaj wiadomość',
      visible: false,
      groupFooter: 'Grupa',
      roomFooter: 'Pokój',
      tabHistory: 'ulubiony',
      tabHistoryShow: false,
      submitting: false,
      moduleName: 'Chat',
      dense: false
    }
  },
  computed: {
    ...mapGetters(['leftPanel', 'rightPanel', 'maximizedDialog'])
  }
}
</script>
<style module lang="stylus"></style>
