<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="full-width">
    <q-input dense v-model="addRoom" :placeholder="translate('JS_CHAT_ADD_PRIVARE_ROOM')">
      <template v-slot:prepend>
        <q-btn color="primary" flat dense icon="mdi-plus" @click="validateRoomName()">
          <q-tooltip anchor="top middle">{{ translate('JS_ADD') }}</q-tooltip>
        </q-btn>
      </template>
      <template v-slot:append>
        <q-icon name="mdi-close" @click="$emit('update:showAddPrivateRoom', false)" class="cursor-pointer" />
        <q-tooltip anchor="top middle">{{ translate('JS_CHAT_HIDE_ADD_PANEL') }}</q-tooltip>
      </template>
    </q-input>
  </div>
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('Chat')

export default {
  name: 'AddRoom',
  props: {
    showAddPrivateRoom: {
      type: Boolean
    }
  },
  data() {
    return {
      addRoom: ''
    }
  },
  watch: {},
  computed: {},
  methods: {
		...mapActions(['addPrivateRoom']),
    validateRoomName() {
			this.addPrivateRoom(this.addRoom).then(result => {
				console.log(result)
			})
		}
  },
  created() {}
}
</script>
<style lang="sass">
</style>
