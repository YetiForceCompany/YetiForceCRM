<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <RoomList isVisible :roomData="roomData" :roomType="roomType">
		<template #labelRight>
		<q-btn
			v-if="roomType === 'private'"
			dense
			flat
			round
			size="sm"
			color="primary"
			icon="mdi-plus"
			@click="showAddPrivateRoom = !showAddPrivateRoom"
		>
			<q-tooltip>{{ translate('JS_CHAT_ADD_PRIVATE_ROOM') }}</q-tooltip>
		</q-btn>
		</template>
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
    <template #aboveItems>
      <q-item v-show="showAddPrivateRoom">
        <RoomPrivateInput :showAddPrivateRoom.sync="showAddPrivateRoom" />
      </q-item>
    </template>
		<template #belowItems>
    <q-dialog v-if="arePrivateRooms" v-model="confirm" persistent content-class="quasar-reset">
      <q-card>
        <q-card-section class="row items-center">
          <q-avatar icon="mdi-alert-circle-outline" text-color="negative" />
          <span class="q-ml-sm">{{
            translate('JS_CHAT_ROOM_ARCHIVE_MESSAGE').replace('${roomToArchive}', roomToArchive.name)
          }}</span>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat :label="translate('JS_CANCEL')" color="black" v-close-popup />
          <q-btn
            @click="archivePrivateRoom(roomToArchive)"
            flat
            :label="translate('JS_ARCHIVE')"
            color="negative"
            v-close-popup
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </RoomList>
</template>
<script>
import RoomPrivateInput from './RoomPrivateInput.vue'
import RoomList from './RoomList.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'RoomPrivate',
  components: { RoomPrivateInput, RoomList },
  props: {
    roomData: {
      type: Array,
      required: true
    },
    roomType: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      filterRooms: '',
      showAll: {
        group: false,
        global: false,
        private: false
      },
      showRoomPrivateInputPanel: false,
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
    showArchiveDialog(room) {
      this.confirm = true
      this.roomToArchive = room
    }
  }
}
</script>
<style lang="sass" scoped>
</style>
