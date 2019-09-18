<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')
/**
 * @desc Updat watcher component for updating data in chat rooms
 * @vue-data {Mix} timerAmount - timer for fetchAmountOfNewMessages request
 * @vue-data {Mix} timerMessage - timer for fetchNewMessages request
 * @vue-data {Array} activeRooms - array of active room to update
 */
export default {
  name: 'UpdateWatcher',
  data() {
    return {
      timerAmount: false,
      timerMessage: false,
      activeRooms: []
    }
  },
  computed: {
    ...mapGetters(['data', 'config', 'tab', 'allRooms'])
  },
  methods: {
    ...mapActions(['updateAmountOfNewMessages', 'fetchRoom']),
    ...mapMutations(['updateChatData', 'setPrivateRooms']),
    /**
     * Init vuex event for adjusting request for updating chat rooms
     */
    adjustUpdateRequestToChatState() {
      this.$store.subscribe((mutation, state) => {
        if (mutation.type === 'Chat/setActiveRoom') {
          this.activeRooms = this.allRooms.filter(el => el.active)
          if (this.activeRooms.length && !this.timerMessage) {
            clearInterval(this.timerAmount)
            this.timerAmount = false
            this.fetchNewMessages()
          }
        } else if (mutation.type === 'Chat/unsetActiveRoom' && !this.allRooms.filter(el => el.active).length) {
          if (!this.timerAmount) {
            clearInterval(this.timerMessage)
            this.timerMessage = false
            this.fetchAmountOfNewMessages()
          }
        }
      })
    },
    /**
     * Fetch new messages timeout function
     */
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
          if (
            typeof result.roomList.private === 'object' &&
            Object.keys(result.roomList.private).length !== Object.keys(this.data.roomList.private).length
          ) {
            if (
              this.data.currentRoom.roomType === 'private' &&
              !result.roomList.private[this.data.currentRoom.recordId]
            ) {
              this.fetchRoom({ id: this.config.defaultRoom.recordId, roomType: this.config.defaultRoom.roomType })
            } else {
              this.setPrivateRooms(result.roomList.private)
            }
          }
          if (result.areNewEntries) {
            this.updateChatData({ roomsToUpdate: currentActiveRooms, newData: result })
          }
          this.fetchNewMessages()
        })
      }, this.config.refreshMessageTime)
    },
    /**
     * Init amount timer
     */
    initAmountTimer() {
      this.timerAmount = setTimeout(this.fetchAmountOfNewMessages, this.config.refreshTimeGlobal)
    },
    /**
     * Fetch new messages timeout function
     */
    fetchAmountOfNewMessages() {
      AppConnector.request({
        module: 'Chat',
        action: 'ChatAjax',
        mode: 'trackNewMessages'
      }).done(({ result }) => {
        this.updateAmountOfNewMessages(result)
        this.initAmountTimer()
      })
    }
  },
  /**
   * Init component event listener and timeout
   */
  created() {
    this.adjustUpdateRequestToChatState()
    this.activeRooms = this.allRooms.filter(el => el.active)
    if (this.activeRooms.length) {
      this.fetchNewMessages()
    } else {
      this.fetchAmountOfNewMessages()
    }
  }
}
</script>
<style>
</style>
