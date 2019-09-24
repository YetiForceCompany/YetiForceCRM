<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="fit">
    <q-layout view="hHh lpR fFf" container class="bg-white">
      <q-page-container>
        <q-page>
          <TabChat @onContentLoaded="isLoading = false" :roomData="roomData || {}" :recordRoom="true" />
        </q-page>
      </q-page-container>
      <q-drawer :value="true" side="right" bordered>
        <ChatRightPanel :participants="participants" />
      </q-drawer>
    </q-layout>
  </div>
</template>
<script>
import TabChat from '../components/Tabs/TabChat.vue'
import ChatRightPanel from '../components/ChatRightPanel.vue'

import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'RecordRoom',
  components: { TabChat, ChatRightPanel },
  data() {
    return {
      isLoading: true
    }
  },
  computed: {
    ...mapGetters(['data']),
    roomData() {
      if (this.data.roomList.crm !== undefined) {
        return this.data.roomList.crm[this.$parent.$options.recordId]
      }
      return {}
    },
    participants() {
      return this.roomData ? this.roomData.participants || [] : []
    }
  },
  methods: {
    ...mapActions(['removeActiveRoom'])
  },
  beforeDestroy() {
    this.removeActiveRoom({ recordId: this.roomData.recordid, roomType: this.roomData.roomType })
  }
}
</script>
<style scoped>
</style>
