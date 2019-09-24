<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <RoomList>
    <template #itemRight>
      <q-btn
        v-if="roomType === 'private' && isUserModerator(room)"
        :class="{ 'hover-display': $q.platform.is.desktop }"
        dense
        round
        flat
        size="xs"
        @click.stop="showArchiveDialog(room)"
        color="negative"
        icon="mdi-delete"
      >
        <q-tooltip>{{ translate('JS_CHAT_ROOM_ARCHIVE') }}</q-tooltip>
      </q-btn>
    </template>
    <template #itemRight>
      <q-item v-show="showAddPrivateRoom">
        <AddRoom :showAddPrivateRoom.sync="showAddPrivateRoom" />
      </q-item>
    </template>
  </RoomList>
</template>
<script>
import AddRoom from './AddRoom.vue'
import RoomList from '../RoomList.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'RoomPrivate',
  components: { AddRoom, RoomList},
  data() {
    return {
      filterRooms: '',
      showAll: {
        group: false,
        global: false,
        private: false
      },
      showAddRoomPanel: false,
      showAddPrivateRoom: false,
      confirm: false,
      roomToArchive: {}
    }
  },
  computed: {
    ...mapGetters(['leftPanel', 'data', 'config', 'isSoundNotification', 'roomSoundNotificationsOff', 'layout']),
    arePrivateRooms() {
      return Object.keys(this.data.roomList.private).length
    },
    isUserModerator() {
      return room => {
        return room.creatorid === CONFIG.userId || this.config.isAdmin
      }
    }
  },
  methods: {
    ...mapMutations(['setLeftPanel']),
    ...mapActions(['fetchRoom', 'togglePinned', 'toggleRoomSoundNotification', 'archivePrivateRoom']),
    getGroupIcon,
    showArchiveDialog(room) {
      this.confirm = true
      this.roomToArchive = room
    }
  }
}
</script>
<style lang="sass" scoped>
</style>
