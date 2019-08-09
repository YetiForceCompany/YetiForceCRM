<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'UpdateWatcher',
  props: {},
  data() {
    return {
      timerGlobal: null,
      timerMessage: null
    }
  },
  computed: {
    ...mapGetters(['data', 'config', 'tab', 'currentRoom', 'activeRooms'])
  },
  watch: {
    activeRooms() {
      if (this.activeRooms.length) {
        clearInterval(this.timerGlobal)
        this.fetchNewMessages()
      } else {
        clearInterval(this.timerMessage)
        this.trackNewMessages()
      }
    }
  },
  methods: {
		...mapActions(['updateAmountOfNewMessages']),
    fetchNewMessages() {
      this.timerMessage = setTimeout(() => {
        AppConnector.request({
          module: 'Chat',
          action: 'ChatAjax',
          mode: 'getRoomsMessages',
          rooms: this.activeRooms
        }).done(({ result }) => {
          this.updateAmountOfNewMessages(result.amountOfNewMessages)
          const areNewEntries = result.roomList[this.roomData.roomType][this.roomData.recordid].chatEntries.length
          if (areNewEntries || !isEqual(this.data.roomList, result.roomList)) {
            // this.updateChat(result)
          }
          this.fetchNewMessages()
        })
      }, this.config.refreshMessageTime)
    },
    initTimer() {
      this.timerGlobal = setTimeout(this.trackNewMessages, this.config.refreshTimeGlobal)
    },
    trackNewMessages() {
      AppConnector.request({
        module: 'Chat',
        action: 'ChatAjax',
        mode: 'trackNewMessages'
      }).done(({ result }) => {
        this.updateAmountOfNewMessages(result)
        this.initTimer()
      })
    }
  },
  created() {
		console.log('crea')
    if (this.activeRooms.length) {
      this.fetchNewMessages()
    } else {
      this.trackNewMessages()
    }
  }
}
</script>
<style>
</style>
