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
        <q-list v-if="roomGroup.length" dense class="q-mb-none">
          <q-item-label header class="flex items-center text-bold">
            <q-item-section avatar>
              <q-icon :name="getGroupIcon(roomType)" :size="fontSize" />
            </q-item-section>
            {{ translate(`JS_CHAT_ROOM_${roomType.toUpperCase()}`) }}
            <q-icon :size="fontSize" name="mdi-information" class="q-ml-auto">
              <q-tooltip> {{ translate(`JS_CHAT_ROOM_DESCRIPTION_${roomType.toUpperCase()}`) }}</q-tooltip>
            </q-icon>
          </q-item-label>
          <template v-for="room of roomGroup">
            <q-item
              v-show="roomType === 'group' ? room.isPinned || showAllGroups || filterRooms.length : true"
              clickable
              v-ripple
              :key="room.name"
              class="q-pl-sm hover-visibility"
              :active="data.currentRoom.recordId === room.recordid"
              active-class="bg-teal-1 text-grey-8"
              @click="fetchRoom({ id: room.recordid, roomType: roomType })"
            >
              <div class="full-width flex items-center justify-between no-wrap">
                <div>
                  {{ room.name }}
                </div>
                <div class="flex items-center justify-end no-wrap">
                  <q-badge
                    v-if="room.cnt_new_message !== undefined && room.cnt_new_message > 0"
                    color="blue"
                    class="q-mx-xs"
                    :label="room.cnt_new_message"
                  />
                  <div class="visible-on-hover">
                    <q-icon v-if="roomType === 'crm'" name="mdi-link-variant" />
                    <q-icon
                      v-if="roomType === 'group' || roomType === 'crm'"
                      @click.stop="togglePinned({ roomType, room })"
                      :name="room.isPinned || roomType === 'crm' ? 'mdi-pin-off' : 'mdi-pin'"
                    />
                  </div>
                </div>
              </div>
            </q-item>
          </template>
        </q-list>
        <div class="full-width flex justify-end">
          <q-btn
            v-if="roomType === 'group'"
            v-show="areUnpinned && !filterRooms.length"
            dense
            flat
            no-caps
            color="info"
            :icon="showAllGroups ? 'mdi-chevron-up' : 'mdi-chevron-down'"
            :label="showAllGroups ? translate('JS_CHAT_HIDE') : translate('JS_CHAT_MORE')"
            @click="showAllGroups = !showAllGroups"
          />
        </div>
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
    ...mapGetters(['leftPanel', 'data', 'tab']),
    areUnpinned() {
      return this.data.roomList.group.some(element => {
        return !element.isPinned
      })
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
    ...mapActions(['fetchRoom', 'togglePinned']),
    getGroupIcon,
    filterRoomByName(room) {
      return room.name.toLowerCase().includes(this.filterRooms.toLowerCase())
    }
  }
}
</script>
<style lang="sass">

.visible-on-hover
	visibility: hidden

.hover-visibility:hover
	.visible-on-hover
		visibility: visible

</style>
