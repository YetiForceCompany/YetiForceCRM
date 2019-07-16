<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-footer class="bg-blue-grey-10 text-white">
    <q-bar>
      <q-breadcrumbs>
        <q-breadcrumbs-el class="text-white" :label="currentTab.label" :icon="currentTab.icon" />
        <q-breadcrumbs-el v-if="tab !== 'unread'" class="text-white" :label="roomType.label" :icon="roomType.icon" />
        <q-breadcrumbs-el v-if="tab === 'chat'" class="text-white text-cyan-9 text-bold" :label="roomName" />
      </q-breadcrumbs>
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
    ...mapGetters(['data', 'tab', 'historyTab']),
    currentTab() {
      switch (this.tab) {
        case 'chat':
          return {
            label: this.translate('JS_CHAT'),
            icon: 'mdi-forum-outline'
          }
        case 'unread':
          return {
            label: this.translate('JS_CHAT_UNREAD'),
            icon: 'mdi-email-alert'
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
      if (this.tab === 'chat' && this.data.currentRoom !== undefined && this.data.currentRoom.roomType !== undefined) {
        this.data.roomList[this.data.currentRoom.roomType].forEach(room => {
          if (room.recordid === this.data.currentRoom.recordId) {
            roomName = room.name
          }
        })
      }
      return roomName
    }
  },
  methods: {
    getGroupIcon
  }
}
</script>
<style module lang="stylus"></style>
