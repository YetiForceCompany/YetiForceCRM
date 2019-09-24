<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<RoomList>

	</RoomList>
</template>
<script>
import SelectModules from './SelectModules.vue'
import RoomList from '../RoomList.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'RoomGlobal',
  components: { SelectModules, RoomList },
  data() {
    return {
      showAddRoomPanel: false,
      showAddPrivateRoom: false,
      confirm: false,
      roomToArchive: {}
    }
  },
  computed: {
    ...mapGetters(['leftPanel', 'data', 'config', 'isSoundNotification', 'roomSoundNotificationsOff', 'layout']),
  },
  methods: {
    ...mapMutations(['setLeftPanel']),
    ...mapActions(['fetchRoom', 'togglePinned', 'toggleRoomSoundNotification', 'archivePrivateRoom']),
  }
}
</script>
<style lang="sass" scoped>
</style>
