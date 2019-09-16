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
      @input=""
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
        <q-icon name="mdi-close" @click.prevent="$emit('update:isVisible', false)" class="cursor-pointer" />
        <q-tooltip anchor="top middle">{{ translate('JS_CHAT_HIDE_SEARCH_FIELD') }}</q-tooltip>
      </template>
      <template v-slot:option="scope">
        <q-item dense v-bind="scope.itemProps" v-on="scope.itemEvents">
          <q-item-section avatar> </q-item-section>
          <q-item-section>
            {{ scope.opt.label }}
          </q-item-section>
        </q-item>
      </template>
    </q-select>
  </div>
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')

export default {
  name: 'selectUsers',
  props: {
    isVisible: {
      type: Boolean
    },
    users: {
      type: Array
    }
  },
  data() {
    return {
      selectUser: null,
      searchUsers: []
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
  computed: {},
  methods: {
    ...mapMutations(['updateRooms']),
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
    this.searchUsers = this.users
  }
}
</script>
<style lang="sass">
.select-dense
	.q-item
		min-height: 32px
		padding: 2px 16px
		font-size: 0.88rem
</style>
