<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <RoomList
    :isVisible="config.dynamicAddingRooms"
    :roomData="roomData"
    :roomType="roomType"
    :hideUnpinned="false"
    :addRoomComponent="config.dynamicAddingRooms"
  >
    <template #itemRight="{ room }">
      <q-btn
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
    </template>
    <template #addRoomComponent>
      <q-item v-if="config.dynamicAddingRooms" v-show="showAddRoomPanel">
        <RoomRecordSelect :modules="config.chatModules" :isVisible.sync="showAddRoomPanel" class="q-pb-xs" />
      </q-item>
    </template>
  </RoomList>
</template>
<script>
import RoomRecordSelect from './RoomRecordSelect.vue'
import RoomList from './RoomList.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'RoomRecord',
  components: { RoomRecordSelect, RoomList },
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
      showAddRoomPanel: false,
      showAddPrivateRoom: false,
      confirm: false,
      roomToArchive: {}
    }
  },
  computed: {
    ...mapGetters(['leftPanel', 'data', 'config', 'isSoundNotification', 'roomSoundNotificationsOff', 'layout'])
  },
  methods: {
    ...mapMutations(['setLeftPanel']),
    ...mapActions(['fetchRoom', 'togglePinned', 'toggleRoomSoundNotification', 'archivePrivateRoom'])
  }
}
</script>
<style lang="sass" scoped>
</style>
