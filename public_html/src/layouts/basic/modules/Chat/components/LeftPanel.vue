<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-drawer :value="leftPanel" side="left" bordered @hide="setLeftPanel(false)">
    <backdrop v-show="tab !== 'chat'" />
    <div class="bg-grey-11 fit">
      <q-input dense v-model="filterRooms" :placeholder="translate('JS_CHAT_FILTER_ROOMS')" class="q-px-sm">
        <template v-slot:prepend>
          <q-icon name="mdi-magnify" />
        </template>
        <template v-slot:append>
          <q-icon v-show="filterRooms.length > 0" name="mdi-close" @click="filterRooms = ''" class="cursor-pointer" />
        </template>
      </q-input>
      <div class="" v-for="(roomGroup, roomType) of roomList" :key="roomType" :style="{ fontSize: fontSize }">
        <q-list v-if="Object.entries(roomGroup).length" dense class="q-mb-none">
          <q-item-label class="flex items-center text-bold text-muted q-py-sm q-px-md">
            <q-item-section avatar>
              <icon :icon="getGroupIcon(roomType)" :size="fontSize" />
            </q-item-section>
            {{ translate(`JS_CHAT_ROOM_${roomType.toUpperCase()}`) }}
            <div class="q-ml-auto ">
              <q-btn
                v-if="roomType === 'group'"
                v-show="areUnpinned && !filterRooms.length"
                dense
                flat
                round
                color="primary"
                :icon="showAllGroups ? 'mdi-chevron-up' : 'mdi-chevron-down'"
                @click="showAllGroups = !showAllGroups"
              >
                <q-tooltip>{{
                  translate(showAllGroups ? 'JS_CHAT_HIDE_UNPINNED' : 'JS_CHAT_SHOW_UNPINNED')
                }}</q-tooltip>
              </q-btn>
              <q-icon :size="fontSize" name="mdi-information" class="q-pr-xs">
                <q-tooltip>{{ translate(`JS_CHAT_ROOM_DESCRIPTION_${roomType.toUpperCase()}`) }}</q-tooltip>
              </q-icon>
            </div>
          </q-item-label>
          <template v-for="(room, roomId) of roomGroup">
            <q-item
              v-show="roomType === 'group' ? room.isPinned || showAllGroups || filterRooms.length : true"
              clickable
              v-ripple
              :key="roomId"
              class="q-pl-sm"
              :active="data.currentRoom.recordId === room.recordid"
              active-class="bg-teal-1 text-grey-8"
              @click="fetchRoom({ id: room.recordid, roomType: roomType })"
            >
              <div class="full-width flex items-center justify-between no-wrap">
                <div class="ellipsis-2-lines">
                  <transition appear enter-active-class="animated flash" mode="out-in">
                    <q-badge
                      v-if="room.cnt_new_message !== undefined && room.cnt_new_message > 0"
                      color="danger"
                      class="q-mr-xs"
                      :label="room.cnt_new_message"
                      :key="room.cnt_new_message"
                    />
                  </transition>
                  <icon
                    v-if="roomType === 'crm'"
                    class="inline-block"
                    :icon="'userIcon-' + room.moduleName"
                    size="0.7rem"
                  />
                  {{ room.name }}
                </div>
                <div class="flex items-center justify-end no-wrap">
                  <div>
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
                      v-if="roomType === 'group' || roomType === 'crm'"
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
											@click.stop="toggleSoundNotification({roomType, id: room.recordid})"
											dense
                      round
                      flat
                      size="xs"
											:icon="isSoundActive(roomType, room.recordid) ? 'mdi-volume-high' : 'mdi-volume-off'"
											:color="isSoundActive(roomType, room.recordid) ? 'primary' : ''"
											:disable="!isSoundNotification"
										>
											<q-tooltip>{{ translate(isSoundActive(roomType, room.recordid) ? 'JS_CHAT_SOUND_ON' : 'JS_CHAT_SOUND_OFF') }}</q-tooltip>
										</q-btn>
                  </div>
                </div>
              </div>
            </q-item>
          </template>
        </q-list>
      </div>
    </div>
  </q-drawer>
</template>
<script>
import Backdrop from 'components/Backdrop.vue'
import { getGroupIcon } from '../utils/utils.js'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'LeftPanel',
  components: { Backdrop },
  data() {
    return {
      filterRooms: '',
      fontSize: '0.88rem',
			showAllGroups: false
    }
  },
  computed: {
    ...mapGetters(['leftPanel', 'data', 'tab', 'isSoundNotification', 'roomSoundNotificationsOff']),
    areUnpinned() {
      for (let room in this.data.roomList.group) {
        if (!this.data.roomList.group[room].isPinned) {
          return true
        }
      }
      return false
    },
    roomList() {
      if (this.filterRooms === '') {
        return this.data.roomList
      } else {
        return {
          crm: this.data.roomList.crm.filter(this.filterRoomByName),
          global: this.data.roomList.global.filter(this.filterRoomByName),
          group: this.data.roomList.group.filter(this.filterRoomByName)
        }
      }
    }
  },
  methods: {
    ...mapMutations(['setLeftPanel']),
    ...mapActions(['fetchRoom', 'togglePinned', 'toggleSoundNotification']),
    getGroupIcon,
    filterRoomByName(room) {
      return room.name.toLowerCase().includes(this.filterRooms.toLowerCase())
		},
		isSoundActive(roomType, id) {
			return this.isSoundNotification && !this.roomSoundNotificationsOff[roomType].includes(id)
		}
  }
}
</script>
<style lang="sass">
</style>
