<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-drawer
    :breakpoint="drawerBreakpoint"
    no-swipe-close
    no-swipe-open
    :show-if-above="false"
    v-model="leftPanel"
    side="left"
    bordered
    @hide="setLeftPanel(false)"
    @input="onDrawerClose"
  >
    <div class="fit bg-grey-11">
      <slot name="top"></slot>
      <div class="bg-grey-11">
        <q-input dense v-model="filterRooms" :placeholder="translate('JS_CHAT_FILTER_ROOMS')" class="q-px-sm">
          <template #prepend>
            <q-icon name="mdi-magnify" />
          </template>
          <template #append>
            <q-icon v-show="filterRooms.length > 0" name="mdi-close" @click="filterRooms = ''" class="cursor-pointer" />
          </template>
        </q-input>
        <div v-for="(roomGroup, roomType) of roomList" :key="roomType" :style="{ fontSize: layout.drawer.fs }">
          <component
            :is="roomsMap[roomType]"
            :roomType="roomType"
            :roomData="roomList[roomType]"
            :filterRooms="filterRooms"
          />
        </div>
      </div>
    </div>
  </q-drawer>
</template>
<script>
import RoomPrivate from './Rooms/RoomPrivate.vue'
import RoomGroup from './Rooms/RoomGroup.vue'
import RoomGlobal from './Rooms/RoomGlobal.vue'
import RoomRecord from './Rooms/RoomRecord.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')
export default {
  name: 'ChatLeftPanel',
  components: { RoomPrivate, RoomGroup, RoomGlobal, RoomRecord },
  props: {
    drawerBreakpoint: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      filterRooms: '',
      roomsMap: {
        private: 'RoomPrivate',
        group: 'RoomGroup',
        global: 'RoomGlobal',
        crm: 'RoomRecord'
      }
    }
  },
  computed: {
    ...mapGetters(['data', 'layout']),
    leftPanel: {
      get() {
        return this.$store.getters['Chat/leftPanel']
      },
      set(isOpen) {}
    },
    roomList() {
      if (this.filterRooms === '') {
        return {
          private: Object.values(this.data.roomList.private).sort(this.sortByRoomName),
          group: Object.values(this.data.roomList.group).sort(this.sortByRoomName),
          global: Object.values(this.data.roomList.global).sort(this.sortByRoomName),
          crm: Object.values(this.data.roomList.crm).sort(this.sortByRoomName)
        }
      } else {
        return {
          private: Object.values(this.data.roomList.private).filter(this.filterRoomByName),
          group: Object.values(this.data.roomList.group).filter(this.filterRoomByName),
          global: Object.values(this.data.roomList.global).filter(this.filterRoomByName),
          crm: Object.values(this.data.roomList.crm).filter(this.filterRoomByName)
        }
      }
    }
  },
  methods: {
    ...mapMutations(['setLeftPanel']),
    filterRoomByName(room) {
      return room.name.toLowerCase().includes(this.filterRooms.toLowerCase())
    },
    sortByRoomName(a, b) {
      return a.name > b.name ? 1 : -1
    },
    onDrawerClose(ev) {
      if (!ev) {
        this.setLeftPanel(false)
      }
    }
  }
}
</script>
<style lang="sass" scoped>
</style>
