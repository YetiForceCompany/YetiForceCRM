<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-layout view="hHh LpR fFf" container class="bg-white">
    <chat-header @visibleInputSearch="inputSearchVisible = $event" @showTabHistory="tabHistoryShow = $event" />
    <left-panel @footerGroup="groupFooter = $event" @footerRoom="roomFooter = $event" />
    <right-panel />
    <messages />
    <chat-footer :groupFooter="groupFooter" :roomFooter="roomFooter" />
  </q-layout>
</template>
<script>
import LeftPanel from './LeftPanel.vue.js'
import RightPanel from './RightPanel.vue.js'
import Messages from './Messages.vue.js'
import ChatHeader from './ChatHeader.vue.js'
import ChatFooter from './ChatFooter.vue.js'
import getters from '/store/getters.js'

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
  ...Vuex.mapGetters({
    leftPanel: getters.Base.Chat.leftPanel,
    rightPanel: getters.Base.Chat.rightPanel
  })
}
</script>
<style module lang="stylus"></style>
