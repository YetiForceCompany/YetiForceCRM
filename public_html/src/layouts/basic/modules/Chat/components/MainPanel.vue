<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-page-container>
    <q-page>
      <q-tab-panels v-model="tab" animated style="min-height: inherit;" class="chat-panels">
        <q-tab-panel name="chat" style="min-height: inherit;">
          <chat-tab />
        </q-tab-panel>
        <q-tab-panel name="unread">
          <unread class="q-pa-md" />
        </q-tab-panel>
        <q-tab-panel name="history">
          <history />
        </q-tab-panel>
      </q-tab-panels>
    </q-page>
  </q-page-container>
</template>
<script>
import ChatTab from './ChatTab.vue'
import Unread from './Unread.vue'
import History from './History.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters } = createNamespacedHelpers('Chat')

export default {
  name: 'MainPanel',
  components: { Unread, History, ChatTab },
  computed: {
    ...mapGetters(['tab'])
  }
}
</script>
<style lang="sass">
.chat-panels.q-tab-panels.q-panel-parent
	.q-panel.scroll
		min-height: inherit
		overflow: hidden
</style>
