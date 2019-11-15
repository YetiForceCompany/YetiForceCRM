<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-list
    v-if="isVisible"
    class="q-mb-none"
    dense
  >
    <q-item-label class="flex items-center text-bold text-muted q-py-sm q-px-md">
      <q-item-section avatar>
        <YfIcon
          :icon="getGroupIcon(roomType)"
          :size="layout.drawer.fs"
        />
      </q-item-section>
      {{ translate(`JS_CHAT_ROOM_${roomType.toUpperCase()}`) }}
      <div class="q-ml-auto">
        <q-btn
          v-show="roomData.length"
          :icon="showAllRooms ? 'mdi-chevron-up' : 'mdi-chevron-down'"
          dense
          flat
          round
          color="primary"
          @click="showAllRooms = !showAllRooms"
        >
          <q-tooltip>{{ translate(showAllRooms ? 'JS_CHAT_HIDE_ROOMS' : 'JS_CHAT_SHOW_ROOMS') }}</q-tooltip>
        </q-btn>
        <slot name="labelRight"></slot>
        <q-btn
          dense
          flat
          round
          size="sm"
          color="primary"
          icon="mdi-magnify"
          @click="showSearchRoom = !showSearchRoom"
        >
          <q-tooltip>{{ translate('JS_CHAT_ADD_PRIVATE_ROOM') }}</q-tooltip>
        </q-btn>
        <q-icon
          class="q-pr-xs"
          :size="layout.drawer.fs"
          name="mdi-information"
        >
          <q-tooltip>{{ translate(`JS_CHAT_ROOM_DESCRIPTION_${roomType.toUpperCase()}`) }}</q-tooltip>
        </q-icon>
      </div>
    </q-item-label>
    <slot name="aboveItems"></slot>
    <q-item v-show="showSearchRoom">
      <RoomListSelect
        :options="config.chatModules"
        :isVisible.sync="showSearchRoom"
        class="q-pb-xs"
      />
    </q-item>
    <template v-for="(room, roomId) of roomData">
      <q-item
        v-if="!room.isHidden"
        v-show="showAllRooms"
        class="q-pl-sm u-hover-container"
        clickable
        v-ripple
        :key="roomId"
        :active="data.currentRoom.recordId === room.recordid && data.currentRoom.roomType === roomType"
        active-class="bg-teal-1 text-grey-8"
        @click="onRoomClick({ id: room.recordid, roomType })"
      >
        <div class="full-width flex items-center justify-between no-wrap">
          <div class="ellipsis-2-lines">
            <YfIcon
              v-if="roomType === 'crm'"
              class="inline-block"
              :icon="'userIcon-' + room.moduleName"
              size="0.7rem"
            />
            {{ room.name }}
          </div>
          <div class="flex items-center justify-end no-wrap">
            <div class="text-no-wrap">
              <transition
                appear
                enter-active-class="animated flash"
                mode="out-in"
              >
                <q-badge
                  v-if="room.cnt_new_message !== undefined && room.cnt_new_message > 0"
                  color="danger"
                  :label="room.cnt_new_message"
                  :key="room.cnt_new_message"
                />
              </transition>
              <slot
                name="itemRight"
                :room="room"
              ></slot>
              <q-btn
                dense
                round
                flat
                size="xs"
                @click.stop="togglePinned({ roomType, room })"
                icon="mdi-pin-off"
              >
                <q-tooltip>{{ translate('JS_CHAT_UNPIN') }}</q-tooltip>
              </q-btn>
              <q-btn
                @click.stop="toggleRoomSoundNotification({ roomType, id: room.recordid })"
                dense
                round
                flat
                size="xs"
                :icon="isSoundActive(roomType, room.recordid) ? 'mdi-volume-high' : 'mdi-volume-off'"
                :color="isSoundActive(roomType, room.recordid) ? 'primary' : ''"
                :disable="!isSoundNotification"
              >
                <q-tooltip>{{
                  translate(isSoundActive(roomType, room.recordid) ? 'JS_CHAT_SOUND_ON' : 'JS_CHAT_SOUND_OFF')
                }}</q-tooltip>
              </q-btn>
            </div>
          </div>
        </div>
      </q-item>
    </template>
    <slot name="belowItems"></slot>
  </q-list>
</template>
<script>
import RoomListSelect from './RoomListSelect.vue'
import { getGroupIcon } from '../../utils/utils.js'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'RoomList',
  components: { RoomListSelect },
  props: {
    roomType: {
      type: String,
      required: true
    },
    roomData: {
      type: Array,
      required: true
    },
    isVisible: {
      type: Boolean,
      required: true
    },
    filterRooms: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      showAllRooms: false,
      showSearchRoom: false
    }
  },
  computed: {
    ...mapGetters([
      'data',
      'isSoundNotification',
      'roomSoundNotificationsOff',
      'layout'
    ])
  },
  methods: {
    ...mapActions([
      'fetchRoom',
      'togglePinned',
      'toggleRoomSoundNotification',
      'mobileMode'
    ]),
    ...mapMutations(['setLeftPanelMobile']),
    getGroupIcon,
    isSoundActive(roomType, id) {
      return (
        this.isSoundNotification &&
        !this.roomSoundNotificationsOff[roomType].includes(id)
      )
    },
    onRoomClick({ id, roomType }) {
      this.fetchRoom({ id, roomType })
      if (this.mobileMode) {
        this.setLeftPanelMobile(false)
      }
    }
  }
}
</script>
<style lang="sass" scoped></style>
