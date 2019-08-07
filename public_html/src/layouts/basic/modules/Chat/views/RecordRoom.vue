<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-layout v-if="config.isChatAllowed" view="hHh lpR fFf" container class="bg-white">
    <chat-tab @onContentLoaded="isLoading = false" />
		<right-panel />
  </q-layout>
</template>
<script>
import ChatTab from '../components/ChatTab.vue'
import RightPanel from '../components/RightPanel.vue'

import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'Dialog',
  components: { ChatTab, RightPanel },
  data() {
    return {
      isLoading: true
    }
  },
  computed: {
    ...mapGetters(['miniMode', 'data', 'config']),
  },
  watch: {
  },
  methods: {
    ...mapActions(['fetchChatConfig', 'updateAmountOfNewMessages']),
    ...mapMutations(['setDialog', 'setCoordinates', 'setButtonCoordinates']),
    initTimer() {
      this.timerGlobal = setTimeout(this.trackNewMessages, this.config.refreshTimeGlobal)
    },
    trackNewMessages() {
      AppConnector.request({
        module: 'Chat',
        action: 'ChatAjax',
        mode: 'trackNewMessages'
      }).done(({ result }) => {
        this.updateAmountOfNewMessages(result)
        this.initTimer()
      })
    },
  },
  created() {
    // this.fetchChatConfig().then(result => {
    //   if (result.config.isChatAllowed && !this.dialog) this.trackNewMessages()
    // })
  }
}
</script>
<style scoped>
</style>
