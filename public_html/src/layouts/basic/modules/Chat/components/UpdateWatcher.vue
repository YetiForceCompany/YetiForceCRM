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
    ...mapGetters(['data', 'config', 'tab', 'currentRoom', 'allRooms'])
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
          if (result.areNewEntries) {
            this.updateChat(result)
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
				if (this.allRooms.filter(el => el.active).length) {
					clearInterval(this.timerGlobal)
					this.fetchNewMessages()
				} else {
					clearInterval(this.timerMessage)
					this.trackNewMessages()
				}
      }
    })
    if (this.allRooms.filter(el => el.active).length) {
      this.fetchNewMessages()
    } else {
      this.trackNewMessages()
    }
  }
}
</script>
<style>
</style>
