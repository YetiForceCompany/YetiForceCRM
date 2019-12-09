<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <RoomList
    :filterRooms="filterRooms"
    :roomData="roomData"
    :roomType="roomType"
    :selectRoom="config.userRoomPin"
  >
  </RoomList>
</template>
<script>
import RoomList from './RoomList.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters} = createNamespacedHelpers('Chat')
export default {
  name: 'RoomUser',
  components: { RoomList },
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
  computed: {
    ...mapGetters(['config'])
  },
}
</script>
<style lang="sass" scoped></style>
