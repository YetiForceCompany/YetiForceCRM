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
  name: 'ChatUpdateWatcher',
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
  /**
   * Init component event listener and timeout
   */
  created() {
    this.adjustUpdateRequestToChatState()
    this.activeRooms = this.allRooms.filter(el => el.active)
    if (this.activeRooms.length) {
      this.initMessageTimer()
    } else {
      this.initAmountTimer()
    }
  },
  methods: {
    ...mapActions(['notifyAboutNewMessages', 'fetchRoom']),
    ...mapMutations(['updateChatData', 'setPinnedRooms']),
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
            this.initMessageTimer()
          }
        } else if (
          mutation.type === 'Chat/unsetActiveRoom' &&
          !this.allRooms.filter(el => el.active).length
        ) {
          if (!this.timerAmount) {
            clearInterval(this.timerMessage)
            this.timerMessage = false
            this.initAmountTimer()
          }
        }
      })
    },
    /**
     * Init amount timer
     */
    initMessageTimer() {
      this.timerMessage = setTimeout(
        this.fetchNewMessages,
        this.config.refreshMessageTime
      )
    },
    /**
     * Fetch new messages timeout function
     */
    fetchNewMessages() {
      let currentActiveRooms = [...this.activeRooms]
      AppConnector.request({
        module: 'Chat',
        action: 'ChatAjax',
        mode: 'getRoomsMessages',
        rooms: this.activeRooms
      }).done(({ result }) => {
        this.updateRoomsUser(result.roomList.user)
        this.notifyAboutNewMessages(result.amountOfNewMessages)
        this.updateRoomsPrivate(result.roomList.private)
        if (result.areNewEntries) {
          this.updateChatData({
            roomsToUpdate: currentActiveRooms,
            newData: result
          })
        }
        if (this.timerMessage) {
          this.initMessageTimer()
        }
      })
    },
    updateRoomsPrivate(rooms) {
      if (
        typeof rooms === 'object' &&
        Object.keys(rooms).length !==
          Object.keys(this.data.roomList.private).length
      ) {
        if (
          this.data.currentRoom.roomType === 'private' &&
          !rooms[this.data.currentRoom.recordId]
        ) {
          this.fetchRoom({
            id: this.config.defaultRoom.recordId,
            roomType: this.config.defaultRoom.roomType
          }).then(_ => {
            this.setPinnedRooms({ rooms, roomType: 'private' })
          })
        } else {
          this.setPinnedRooms({ rooms, roomType: 'private' })
        }
      }
    },
    updateRoomsUser(rooms) {
      if (
        typeof rooms === 'object' &&
        Object.keys(rooms).length !==
          Object.keys(this.data.roomList.user).length
      ) {
        this.setPinnedRooms({ rooms, roomType: 'user' })
      }
    },

    /**
     * Init amount timer
     */
    initAmountTimer() {
      this.timerAmount = setTimeout(
        this.fetchAmountOfNewMessages,
        this.config.refreshTimeGlobal
      )
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
        this.notifyAboutNewMessages(result)
        if (this.timerAmount) {
          this.initAmountTimer()
        }
      })
    }
  }
}
</script>
<style>
</style>
