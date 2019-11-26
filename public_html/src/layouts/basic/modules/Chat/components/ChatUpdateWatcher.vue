<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import difference from 'lodash.difference'
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
    this.startTimer()
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
          !this.allRooms.filter(el => el.active).length &&
          this.tab !== 'chat'
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
     * Start timer, when component is created.
     */
    startTimer() {
      if (this.activeRooms.length) {
        this.fetchNewMessages({ firstFetch: true })
      } else {
        this.fetchAmountOfNewMessages({ firstFetch: true })
      }
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
    fetchNewMessages({ firstFetch } = { firstFetch: false }) {
      let currentActiveRooms = [...this.activeRooms]
      AppConnector.request({
        module: 'Chat',
        action: 'ChatAjax',
        mode: 'getRoomsMessages',
        rooms: this.activeRooms
      }).done(({ result }) => {
        this.addLackingRooms(result.roomList)
        this.notifyAboutNewMessages({
          ...result.amountOfNewMessages,
          firstFetch
        })
        if (result.areNewEntries) {
          this.updateChatData({
            roomsToUpdate: currentActiveRooms,
            newData: result
          })
        }
        if (this.timerMessage || firstFetch) {
          this.initMessageTimer()
        }
      })
    },
    addLackingRooms(roomList) {
      this.updateRooms(roomList.user, 'user')
      this.updateRooms(roomList.private, 'private')
    },
    updateRooms(rooms, roomType) {
      if (
        typeof rooms === 'object' &&
        difference(
          Object.keys(rooms),
          Object.keys(this.data.roomList[roomType])
        ).length
      ) {
        if (
          this.data.currentRoom.roomType === roomType &&
          !rooms[this.data.currentRoom.recordId]
        ) {
          this.fetchRoomList().then(_ => {
            this.setPinnedRooms({ rooms, roomType })
          })
        } else {
          this.setPinnedRooms({ rooms, roomType })
        }
      }
    },
    updateRoomsUser(rooms) {
      if (typeof rooms === 'object' && Object.keys(rooms).length) {
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
    fetchAmountOfNewMessages({ firstFetch } = { firstFetch: false }) {
      AppConnector.request({
        module: 'Chat',
        action: 'ChatAjax',
        mode: 'trackNewMessages'
      }).done(({ result }) => {
        this.addLackingRooms(result.roomList)
        this.notifyAboutNewMessages({ ...result, firstFetch })
        if (this.timerAmount || firstFetch) {
          this.initAmountTimer()
        }
      })
    }
  }
}
</script>
<style>
</style>
