<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-tabs v-model="tabHistory"  @input="tabChange" align="left" dense shrink inline-label narrow-indicator class="text-teal">
    <q-tab
      v-for="(room, roomType) of data.roomList"
      :key="roomType"
      :name="roomType"
      :label="translate(`JS_CHAT_ROOM_${roomType.toUpperCase()}`)"
    />
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
      tabHistory: 'crm'
    }
  },
  computed: {
    ...mapGetters(['data', 'tab'])
  },
  methods: {
		...mapActions(['fetchHistory']),
		tabChange(val) {
			console.log(val)
			this.fetchHistory({ groupHistory: val, showMoreClicked: false })
		}
  },
  mounted() {
    console.log('moun')
    this.fetchHistory({ groupHistory: this.tabHistory, showMoreClicked: false })
  }
}
</script>
<style lang="sass">
</style>
