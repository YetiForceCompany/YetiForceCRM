<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-tabs v-model="tabHistory" align="left" dense shrink inline-label narrow-indicator class="text-teal">
    <q-tab v-for="(room, roomType) of data.roomList" :key="roomType" :name="roomType" :label="translate(`JS_CHAT_ROOM_${roomType.toUpperCase()}`)" />
  </q-tabs>
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')

export default {
  name: 'History',
  components: {},
  data() {
    return {
      userId: CONFIG.userId,
      tabHistory: 'ulubiony'
    }
	},
	computed: {
		    ...mapGetters(['data', 'tab']),
	},
  methods: {
    ...mapActions(['fetchHistory'])
  }
}
</script>
<style lang="sass">
</style>
