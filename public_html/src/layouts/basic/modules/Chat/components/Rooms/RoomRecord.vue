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
      <q-btn
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
    </template>
    <template #itemRight="{ room }">
      <q-btn
        size="xs"
        dense
        round
        flat
        color="primary"
        class="js-popover-tooltip--record ellipsis"
        @click.stop
        icon="mdi-link-variant"
        :href="`index.php?module=${room.moduleName}&view=Detail&record=${room.recordid}`"
      />
    </template>
    <template #aboveItems>
      <q-item
        v-if="config.dynamicAddingRooms"
        v-show="showAddRoomPanel"
      >
        <RoomListSelect
          class="q-pb-xs"
          :options="config.chatModules"
          :isVisible.sync="showAddRoomPanel"
          @input="showRecordsModal"
        >
          <template #prepend="{ selected }">
            <q-icon
              class="cursor-pointer"
              name="mdi-magnify"
              @click.prevent="showRecordsModal(selected)"
            />
            <q-tooltip anchor="top middle">{{ translate('JS_CHAT_SEARCH_RECORDS_OF_THE_SELECTED_MODULE') }}</q-tooltip>
          </template>
        </RoomListSelect>
      </q-item>
    </template>
  </RoomList>
</template>
<script>
import RoomListSelect from './RoomListSelect.vue'
import RoomList from './RoomList.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')
export default {
  name: 'RoomRecord',
  components: { RoomListSelect, RoomList },
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
  },
  methods: {
    ...mapMutations(['updateRooms']),
    showRecordsModal(val) {
      console.log(val)
      app.showRecordsList(
        { module: val, src_module: val },
        (modal, instance) => {
          instance.setSelectEvent((responseData, e) => {
            AppConnector.request({
              module: 'Chat',
              action: 'Room',
              mode: 'addToFavorites',
              roomType: 'crm',
              recordId: responseData.id
            }).done(({ result }) => {
              this.updateRooms(result)
            })
          })
        }
      )
    }
  }
}
</script>
<style lang="sass" scoped></style>
