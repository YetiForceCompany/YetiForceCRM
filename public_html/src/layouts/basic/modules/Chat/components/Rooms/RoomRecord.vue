<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <RoomList
    :isVisible="config.dynamicAddingRooms"
    :roomData="roomData"
    :roomType="roomType"
    :hideUnpinned="false"
    :filterRooms="filterRooms"
  >
    <template #labelRight>
      <q-btn dense flat round size="sm" color="primary" icon="mdi-plus" @click="showAddRoomPanel = !showAddRoomPanel">
        <q-tooltip>{{ translate('JS_CHAT_ADD_FAVORITE_ROOM_FROM_MODULE') }}</q-tooltip>
      </q-btn>
    </template>
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
    <template #aboveItems>
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
const { mapGetters } = createNamespacedHelpers('Chat')
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
    },
    filterRooms: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      showAddRoomPanel: false
    }
  },
  computed: {
    ...mapGetters(['config'])
  }
}
</script>
<style lang="sass" scoped>
</style>
