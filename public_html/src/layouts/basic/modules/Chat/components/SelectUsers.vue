<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="full-width">
    <q-select
      dense
      v-model="selectUser"
      use-input
      fill-input
      hide-selected
      input-debounce="0"
      :options="searchUsers"
      option-value="id"
      option-label="label"
      option-img="img"
      emit-value
      map-options
      :hint="translate('JS_CHAT_ADD_FAVORITE_ROOM_FROM_MODULE')"
      @filter="filter"
      @input="validateParticipant"
      :error="!isValid"
      hide-bottom-space
      popup-content-class="quasar-reset"
      class="full-width"
      ref="selectUser"
    >
      <template v-slot:no-option>
        <q-item>
          <q-item-section class="text-grey"> {{ translate('JS_NO_RESULTS_FOUND') }} </q-item-section>
        </q-item>
      </template>
      <template v-slot:append>
        <q-icon
          name="mdi-close"
          @click.prevent="$emit('update:isVisible', false), (isValid = true)"
          class="cursor-pointer"
        />
        <q-tooltip anchor="top middle">{{ translate('JS_CHAT_HIDE_ADD_PANEL') }}</q-tooltip>
      </template>
      <template v-slot:option="scope">
        <q-item dense v-bind="scope.itemProps" v-on="scope.itemEvents">
          <q-item-section avatar>
            <img v-if="scope.opt.img" :src="scope.opt.img" :alt="scope.opt.label" style="height: 1.7rem;" />
            <q-icon v-else name="mdi-account" />
          </q-item-section>
          <q-item-section>
            {{ scope.opt.label }}
          </q-item-section>
        </q-item>
      </template>
      <template v-slot:error>
        {{ errorMessage }}
      </template>
    </q-select>
  </div>
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')

export default {
  name: 'selectUsers',
  props: {
    isVisible: {
      type: Boolean
    }
  },
  data() {
    return {
      selectUser: null,
      users: [],
      searchUsers: [],
      isValid: true,
      errorMessage: ''
    }
  },
  watch: {
    isVisible(val) {
      if (val) {
        setTimeout(() => {
          this.$refs.selectUser.showPopup()
        }, 100)
      } else {
        this.selectUser = null
        this.$refs.selectUser.hidePopup()
      }
    }
  },
  computed: {
    ...mapGetters(['currentRoomData', 'layout'])
  },
  methods: {
    ...mapActions(['fetchChatUsers', 'addParticipant']),
    ...mapMutations(['updateParticipants']),
    validateParticipant(val) {
      if (val) {
        let userExists = false
        for (let participant of this.currentRoomData.participants) {
          if (participant.user_id === val && participant.active) {
            userExists = true
            break
          }
        }
        if (!userExists) {
          this.addParticipant({ recordId: this.currentRoomData.recordid, userId: val }).then(({ result }) => {
            if (result.message) {
              this.errorMessage = this.translate(result.message)
              this.isValid = false
            } else {
              this.selectUser = null
              this.isValid = true
              this.updateParticipants({
                recordId: this.currentRoomData.recordid,
                roomType: this.currentRoomData.roomType,
                data: result
              })
              this.$q.notify({
                position: 'top',
                color: 'success',
                message: this.translate('JS_CHAT_PARTICIPANT_ADDED'),
                icon: 'mdi-check'
              })
            }
          })
        } else {
          this.errorMessage = this.translate('JS_CHAT_PARTICIPANT_EXISTS')
          this.isValid = false
        }
      } else {
        this.errorMessage = this.translate('JS_CHAT_PARTICIPANT_NAME_EMPTY')
        this.isValid = false
      }
    },
    filter(val, update) {
      if (val === '') {
        update(() => {
          this.searchUsers = this.users
        })
        return
      }
      update(() => {
        const needle = val.toLowerCase()
        this.searchUsers = this.users.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    }
  },
  created() {
    this.fetchChatUsers().then(users => {
      this.users = users
      this.searchUsers = this.users
    })
  }
}
</script>
<style lang="sass" scoped>
.select-dense
	.q-item
		min-height: 32px
		padding: 2px 16px
</style>
