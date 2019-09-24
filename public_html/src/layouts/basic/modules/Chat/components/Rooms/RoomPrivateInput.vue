<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="full-width">
    <q-input
      v-model="addRoom"
      @keydown.enter="validateRoomName()"
      dense
      :placeholder="translate('JS_CHAT_ADD_PRIVATE_ROOM')"
      :error="!isValid"
      @input="isValid = true"
			:loading="isValidating"
      ref="addRoomInput"
    >
      <template v-slot:prepend>
        <q-btn color="primary" flat dense icon="mdi-plus" @click="validateRoomName()">
          <q-tooltip anchor="top middle">{{ translate('JS_ADD') }}</q-tooltip>
        </q-btn>
      </template>
      <template v-slot:append>
        <q-icon name="mdi-close" @click="$emit('update:showAddPrivateRoom', false)" class="cursor-pointer" />
        <q-tooltip anchor="top middle">{{ translate('JS_CHAT_HIDE_ADD_PANEL') }}</q-tooltip>
      </template>
      <template v-slot:error>
        {{ errorMessage }}
      </template>
    </q-input>
  </div>
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')

export default {
  name: 'RoomPrivateInput',
  props: {
    showAddPrivateRoom: {
      type: Boolean
    }
  },
  data() {
    return {
      addRoom: '',
      isValid: true,
			errorMessage: '',
			isValidating: false
    }
  },
  watch: {
    showAddPrivateRoom() {
      if (this.showAddPrivateRoom) {
        this.$refs.addRoomInput.focus()
      }
    }
  },
  computed: {
    ...mapGetters(['data'])
  },
  methods: {
    ...mapActions(['addPrivateRoom']),
    ...mapMutations(['updateRooms']),
    validateRoomName() {
      if (this.addRoom.length && !this.isValidating) {
				this.isValidating = true
        let roomExist = false
        for (let room in this.data.roomList.private) {
          if (this.data.roomList.private[room].name === this.addRoom) {
            roomExist = true
            break
          }
        }
        if (!roomExist) {
          this.addPrivateRoom({ name: this.addRoom }).then(({ result }) => {
            this.addRoom = ''
						this.updateRooms(result)
						this.isValidating = false
            this.$q.notify({
              position: 'top',
              color: 'success',
              message: this.translate('JS_CHAT_ROOM_ADDED'),
              icon: 'mdi-check'
            })
          })
        } else {
          this.errorMessage = this.translate('JS_CHAT_ROOM_EXISTS')
					this.isValid = false
					this.isValidating = false
        }
      } else {
        this.errorMessage = this.translate('JS_CHAT_ROOM_NAME_EMPTY')
        this.isValid = false
      }
    }
  },
  created() {}
}
</script>
<style lang="sass">
</style>
