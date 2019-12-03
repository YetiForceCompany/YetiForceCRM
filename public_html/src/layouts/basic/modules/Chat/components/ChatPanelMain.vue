<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-page-container>
    <q-page>
      <q-tab-panels
        v-model="tab"
        class="chat-panels"
        style="min-height: inherit;"
        animated
      >
        <q-tab-panel
          name="chat"
          style="min-height: inherit;"
        >
          <TabChat
            :roomData="currentRoomData"
            @onContentLoaded="isLoading = false"
          >
            <template #searchPrepend>
              <q-btn
                dense
                flat
                round
                :color="leftPanel ? 'info' : 'grey'"
                @click="toggleLeftPanel()"
              >
                <YfIcon icon="yfi-menu-group-room" />
                <q-tooltip>{{ translate('JS_CHAT_ROOMS_MENU') }}</q-tooltip>
              </q-btn>
            </template>
            <template #searchAppend>
              <q-btn
                :color="rightPanel ? 'info' : 'grey'"
                dense
                flat
                round
                @click="toggleRightPanel()"
              >
                <YfIcon icon="yfi-menu-entrant" />
                <q-tooltip>{{ translate('JS_CHAT_PARTICIPANTS_MENU') }}</q-tooltip>
              </q-btn>
            </template>
          </TabChat>
        </q-tab-panel>
        <q-tab-panel name="unread">
          <TabUnread
            class="q-pa-md"
            @onContentLoaded="isLoading = false"
          />
        </q-tab-panel>
        <q-tab-panel name="history">
          <TabHistory @onContentLoaded="isLoading = false" />
        </q-tab-panel>
      </q-tab-panels>
      <q-inner-loading :showing="isLoading">
        <q-spinner-cube
          color="primary"
          size="50px"
        />
      </q-inner-loading>
    </q-page>
  </q-page-container>
</template>
<script>
import TabChat from './Tabs/TabChat.vue'
import TabUnread from './Tabs/TabUnread.vue'
import TabHistory from './Tabs/TabHistory.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('Chat')

export default {
  name: 'ChatPanelMain',
  components: { TabUnread, TabHistory, TabChat },
  data() {
    return {
      isLoading: true
    }
  },
  computed: {
    ...mapGetters(['tab', 'currentRoomData', 'leftPanel', 'rightPanel'])
  },
  watch: {
    tab() {
      this.isLoading = true
    }
  },
  methods: {
    ...mapActions(['toggleLeftPanel', 'toggleRightPanel'])
  }
}
</script>
<style lang="sass">
.chat-panels.q-tab-panels.q-panel-parent
	.q-panel.scroll
		min-height: inherit
		overflow: hidden
</style>
