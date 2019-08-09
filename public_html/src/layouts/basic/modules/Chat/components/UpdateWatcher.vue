<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')
export default {
  name: 'UpdateWatcher',
  props: {},
  data() {
    return {
      timerGlobal: false,
      timerMessage: false,
      activeRooms: []
    }
  },
  computed: {
    ...mapGetters(['data', 'config', 'tab', 'currentRoom', 'allRooms'])
  },
  methods: {
    ...mapActions(['updateAmountOfNewMessages']),
    ...mapMutations(['updateChatData']),
    fetchNewMessages() {
      this.timerMessage = setTimeout(() => {
        let currentActiveRooms = [...this.activeRooms]
        AppConnector.request({
          module: 'Chat',
          action: 'ChatAjax',
          mode: 'getRoomsMessages',
          rooms: this.activeRooms
        }).done(({ result }) => {
          this.updateAmountOfNewMessages(result.amountOfNewMessages)
          console.log(result.areNewEntries)
          if (result.areNewEntries) {
            console.log(result)
            this.updateChatData({ roomsToUpdate: currentActiveRooms, newData: result })
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
    this.$store.subscribe((mutation, state) => {
      if (mutation.type === 'Chat/setActiveRoom' || mutation.type === 'Chat/unsetActiveRoom') {
        this.activeRooms = this.allRooms.filter(el => el.active)
        if (this.activeRooms.length && !this.timerMessage) {
          clearInterval(this.timerGlobal)
          this.timerGlobal = false
          this.fetchNewMessages()
        } else if (!this.timerGlobal) {
          clearInterval(this.timerMessage)
          this.timerMessage = false
          this.trackNewMessages()
        }
        console.log(this.timerGlobal)
      }
    })
    this.activeRooms = this.allRooms.filter(el => el.active)
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
