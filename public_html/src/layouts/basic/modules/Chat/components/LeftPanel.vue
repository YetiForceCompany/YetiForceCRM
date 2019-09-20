<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-drawer :value="leftPanel" side="left" bordered @hide="setLeftPanel(false)">
    <div class="fit bg-grey-11">
      <slot name="top"></slot>
      <div class="bg-grey-11">
        <q-input dense v-model="filterRooms" :placeholder="translate('JS_CHAT_FILTER_ROOMS')" class="q-px-sm">
          <template v-slot:prepend>
            <q-icon name="mdi-magnify" />
          </template>
          <template v-slot:append>
            <q-icon v-show="filterRooms.length > 0" name="mdi-close" @click="filterRooms = ''" class="cursor-pointer" />
          </template>
        </q-input>
        <div class="" v-for="(roomGroup, roomType) of roomList" :key="roomType" :style="{ fontSize: layout.drawer.fs }">
          <q-list
            v-if="
              Object.entries(roomGroup).length ||
                (roomType === 'crm' && config.dynamicAddingRooms) ||
                roomType === 'private'
            "
            dense
            class="q-mb-none"
          >
            <q-item-label class="flex items-center text-bold text-muted q-py-sm q-px-md">
              <q-item-section avatar>
                <icon :icon="getGroupIcon(roomType)" :size="layout.drawer.fs" />
              </q-item-section>
              {{ translate(`JS_CHAT_ROOM_${roomType.toUpperCase()}`) }}
              <div class="q-ml-auto">
                <q-btn
                  v-if="roomType !== 'crm'"
                  v-show="areUnpinned(roomType) && !filterRooms.length"
                  dense
                  flat
                  round
                  color="primary"
                  :icon="showAll[roomType] ? 'mdi-chevron-up' : 'mdi-chevron-down'"
                  @click="showAll[roomType] = !showAll[roomType]"
                >
                  <q-tooltip>{{
                    translate(showAll[roomType] ? 'JS_CHAT_HIDE_UNPINNED' : 'JS_CHAT_SHOW_UNPINNED')
                  }}</q-tooltip>
                </q-btn>
                <q-btn
                  v-if="roomType === 'crm' && config.dynamicAddingRooms"
                  dense
                  flat
                  round
                  size="sm"
                  color="primary"
                  icon="mdi-plus"
                  @click="showAddRoomPanel = !showAddRoomPanel"
                >
                  <q-tooltip>{{ translate('JS_CHAT_ADD_FAVORITE_ROOM_FROM_MODULE') }}</q-tooltip>
                </q-btn>
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
                <q-icon :size="layout.drawer.fs" name="mdi-information" class="q-pr-xs">
                  <q-tooltip>{{ translate(`JS_CHAT_ROOM_DESCRIPTION_${roomType.toUpperCase()}`) }}</q-tooltip>
                </q-icon>
              </div>
            </q-item-label>
            <q-item v-if="roomType === 'crm' && config.dynamicAddingRooms" v-show="showAddRoomPanel">
              <select-modules :modules="config.chatModules" :isVisible.sync="showAddRoomPanel" class="q-pb-xs" />
            </q-item>
            <q-item v-if="roomType === 'private'" v-show="showAddPrivateRoom">
              <add-room :showAddPrivateRoom.sync="showAddPrivateRoom" />
            </q-item>
            <template v-for="(room, roomId) of roomGroup">
              <q-item
                v-show="roomType !== 'crm' ? room.isPinned || showAll[roomType] || filterRooms.length : room.isPinned"
                clickable
                v-ripple
                :key="roomId"
                class="q-pl-sm hover-container"
                :active="data.currentRoom.recordId === room.recordid && data.currentRoom.roomType === roomType"
                active-class="bg-teal-1 text-grey-8"
                @click="fetchRoom({ id: room.recordid, roomType: roomType })"
              >
                <div class="full-width flex items-center justify-between no-wrap">
                  <div class="ellipsis-2-lines">
                    <icon
                      v-if="roomType === 'crm'"
                      class="inline-block"
                      :icon="'userIcon-' + room.moduleName"
                      size="0.7rem"
                    />
                    {{ room.name }}
                  </div>
                  <div class="flex items-center justify-end no-wrap">
                    <div class="text-no-wrap">
                      <transition appear enter-active-class="animated flash" mode="out-in">
                        <q-badge
                          v-if="room.cnt_new_message !== undefined && room.cnt_new_message > 0"
                          color="danger"
                          :label="room.cnt_new_message"
                          :key="room.cnt_new_message"
                        />
                      </transition>
                      <q-btn
                        v-if="roomType === 'crm'"
                        type="a"
                        size="xs"
                        dense
                        round
                        flat
                        color="primary"
                        class="js-popover-tooltip--record ellipsis"
                        @click.stop=""
                        icon="mdi-link-variant"
                        :href="`index.php?module=${room.moduleName}&view=Detail&record=${room.recordid}`"
                      />
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
                      <q-btn
                        dense
                        round
                        flat
                        size="xs"
                        @click.stop="togglePinned({ roomType, room })"
                        :color="room.isPinned || roomType === 'crm' ? 'primary' : ''"
                        :icon="room.isPinned || roomType === 'crm' ? 'mdi-pin' : 'mdi-pin-off'"
                      >
                        <q-tooltip>{{
                          translate(room.isPinned || roomType === 'crm' ? 'JS_CHAT_UNPIN' : 'JS_CHAT_PIN')
                        }}</q-tooltip>
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
          </q-list>
        </div>
      </div>
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
    </div>
  </q-drawer>
</template>
<script>
import SelectModules from './SelectModules.vue'
import AddRoom from './AddRoom.vue'
import { getGroupIcon } from '../utils/utils.js'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'LeftPanel',
  components: { SelectModules, AddRoom },
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
    areUnpinned() {
      return roomType => {
        for (let room in this.data.roomList[roomType]) {
          if (!this.data.roomList[roomType][room].isPinned) {
            return true
          }
        }
        return false
      }
    },
    arePrivateRooms() {
      return Object.keys(this.data.roomList.private).length
    },
    isUserModerator() {
      return room => {
        return room.creatorid === CONFIG.userId || this.config.isAdmin
      }
    },
    roomList() {
      if (this.filterRooms === '') {
        return {
          crm: Object.values(this.data.roomList.crm).sort(this.sortByRoomName),
          global: Object.values(this.data.roomList.global).sort(this.sortByRoomName),
          group: Object.values(this.data.roomList.group).sort(this.sortByRoomName),
          private: Object.values(this.data.roomList.private).sort(this.sortByRoomName)
				}
				} else {
        return {
          crm: Object.values(this.data.roomList.crm).filter(this.filterRoomByName),
          global: Object.values(this.data.roomList.global).filter(this.filterRoomByName),
          group: Object.values(this.data.roomList.group).filter(this.filterRoomByName),
          private: Object.values(this.data.roomList.private).filter(this.filterRoomByName)
        }
      }
    }
  },
  methods: {
    ...mapMutations(['setLeftPanel']),
    ...mapActions(['fetchRoom', 'togglePinned', 'toggleRoomSoundNotification', 'archivePrivateRoom']),
    getGroupIcon,
    filterRoomByName(room) {
      return room.name.toLowerCase().includes(this.filterRooms.toLowerCase())
		},
		sortByRoomName(a, b) {
			return a.name > b.name ? 1 : -1
		},
    isSoundActive(roomType, id) {
      return this.isSoundNotification && !this.roomSoundNotificationsOff[roomType].includes(id)
    },
    showArchiveDialog(room) {
      this.confirm = true
      this.roomToArchive = room
    }
  }
}
</script>
<style lang="sass" scoped>
.hover-container .hover-display
	display: none
.hover-container:hover .hover-display
	display: inline
</style>
