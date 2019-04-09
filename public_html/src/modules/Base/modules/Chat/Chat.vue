<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-layout view="hHh LpR fFf" container class="bg-white">
    <chat-header
      @maximized="maximizedToggle = $event"
      @visibleInputSearch="inputSearchVisible = $event"
      @showTabHistory="tabHistoryShow = $event"
      :maximizedToggle="maximizedToggle"
      @leftPanel="left = $event"
      @rightPanel="right = $event"
      :right="right"
      :left="left"
    />
    <left-panel @footerGroup="groupFooter = $event" @footerRoom="roomFooter = $event" />
    <right-panel />
    <messages />
    <chat-footer :groupFooter="groupFooter" :roomFooter="roomFooter" />
  </q-layout>
</template>
<script>
import LeftPanel from './components/LeftPanel.vue.js'
import RightPanel from './components/RightPanel.vue.js'
import Messages from './components/Messages.vue.js'
import ChatHeader from './components/ChatHeader.vue.js'
import ChatFooter from './components/ChatFooter.vue.js'

import ModuleLoader from '/src/ModuleLoader.js'
import moduleStore from './store/index.js'

const moduleName = 'Base.Chat'

export function initialize({ store }) {
  store.registerModule(moduleName.split('.'), ModuleLoader.prepareStoreNames(moduleName, moduleStore))
}

export default {
  name: moduleName,
  components: { LeftPanel, RightPanel, Messages, ChatHeader, ChatFooter },
  data() {
    return {
      iconSize: '.75rem',
      maximizedToggle: true,
      placeholder: 'Wyszukaj wiadomość',
      visible: false,
      groupFooter: 'Grupa',
      roomFooter: 'Pokój',
      left: true,
      right: true,
      tabHistory: 'ulubiony',
      tabHistoryShow: false,
      submitting: false,
      moduleName: 'Chat',
      dense: false
    }
  }
}
</script>
<style module lang="stylus"></style>
