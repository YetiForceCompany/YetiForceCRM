<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-footer class="bg-blue-grey-10 text-white">
    <q-bar class="q-bar--fit justify-between">
      <q-btn dense flat :color="leftPanel ? 'info' : ''" @click="toggleLeftPanel()">
        <YfIcon icon="yfi-menu-group-room" />
        <q-icon :name="`mdi-chevron-${leftPanel ? 'left' : 'right'}`" />
        <q-tooltip>{{ translate('JS_CHAT_ROOMS_MENU') }}</q-tooltip>
      </q-btn>
      <q-breadcrumbs gutter="none">
        <q-breadcrumbs-el class="text-white">
          <YfIcon
            class="q-breadcrumbs__el-icon q-breadcrumbs__el-icon--with-label q-icon"
            :icon="currentTab.icon"
            :size="currentTab.icon.startsWith('yfi') ? '16px' : ''"
          />
          {{ currentTab.label }}
        </q-breadcrumbs-el>
        <q-breadcrumbs-el v-if="tab !== 'unread'" class="text-white">
          <YfIcon
            class="q-breadcrumbs__el-icon q-breadcrumbs__el-icon--with-label q-icon"
            :icon="roomType.icon"
            size="16px"
          />
          {{ roomType.label }}
        </q-breadcrumbs-el>
        <q-breadcrumbs-el
          v-if="tab === 'chat'"
          class="text-white text-cyan-9 text-bold u-ellipsis-2-lines"
          :label="roomName"
        />
        <template #separator>
          <div class="q-breadcrumbs__separator q-mx-sm">
            /
          </div>
        </template>
      </q-breadcrumbs>
      <div>
        <q-btn dense flat :color="rightPanel ? 'info' : ''" @click="toggleRightPanel()">
          <q-icon :name="`mdi-chevron-${rightPanel ? 'right' : 'left'}`" />
          <YfIcon icon="yfi-menu-entrant" />
          <q-tooltip>{{ translate('JS_CHAT_PARTICIPANTS_MENU') }}</q-tooltip>
        </q-btn>
      </div>
    </q-bar>
  </q-footer>
</template>
<script>
import { getGroupIcon } from '../utils/utils.js'
import { createNamespacedHelpers } from 'vuex'
const { mapActions, mapGetters } = createNamespacedHelpers('Chat')
export default {
  name: 'ChatFooter',
  computed: {
    ...mapGetters(['data', 'tab', 'historyTab', 'leftPanel', 'rightPanel']),
    currentTab() {
      switch (this.tab) {
        case 'chat':
          return {
            label: this.translate('JS_CHAT'),
            icon: 'yfi-branding-chat'
          }
        case 'unread':
          return {
            label: this.translate('JS_CHAT_UNREAD'),
            icon: 'yfi-unread-messages'
          }
        case 'history':
          return {
            label: this.translate('JS_CHAT_HISTORY'),
            icon: 'mdi-history'
          }
      }
    },
    roomType() {
      if (
        this.tab !== 'unread' &&
        this.data.currentRoom !== undefined &&
        this.data.currentRoom.roomType !== undefined
      ) {
        const roomType = this.tab === 'chat' ? this.data.currentRoom.roomType : this.historyTab
        return {
          label: this.translate(`JS_CHAT_ROOM_${roomType.toUpperCase()}`),
          icon: this.getGroupIcon(roomType)
        }
      } else {
        return { label: '', icon: '' }
      }
    },
    roomName() {
      let roomName = ''
      const currentRoom = this.data.currentRoom
      if (this.tab === 'chat' && currentRoom !== undefined && currentRoom.roomType !== undefined) {
        roomName = this.data.roomList[currentRoom.roomType][currentRoom.recordId].name
      }
      return roomName
    }
  },
  methods: {
    ...mapActions(['toggleLeftPanel', 'toggleRightPanel']),
    getGroupIcon
  }
}
</script>
<style scoped>
.q-bar--fit {
  height: auto;
  min-height: 32px;
}
</style>
