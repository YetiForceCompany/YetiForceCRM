<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-footer class="bg-blue-grey-10 text-white">
    <q-bar>
      <q-breadcrumbs>
        <q-breadcrumbs-el class="text-white" :label="roomType.label" :icon="roomType.icon" />
        <q-breadcrumbs-el class="text-white text-cyan-9 text-bold" :label="roomName" />
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
    ...mapGetters(['data']),
    roomType() {
      if (this.data.currentRoom.roomType !== undefined) {
        return {
          label: this.translate(`JS_CHAT_ROOM_${this.data.currentRoom.roomType.toUpperCase()}`),
          icon: this.getGroupIcon(this.data.currentRoom.roomType)
        }
      } else {
        return { label: '', icon: '' }
      }
    },
    roomName() {
      let roomName = ''
      if (this.data.currentRoom.roomType !== undefined) {
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
