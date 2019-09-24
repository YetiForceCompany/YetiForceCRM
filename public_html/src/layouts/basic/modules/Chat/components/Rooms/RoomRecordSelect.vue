<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="full-width">
    <q-select
      dense
      v-model="selectModule"
      use-input
      fill-input
      hide-selected
      input-debounce="0"
      :options="searchModules"
      option-value="id"
      option-label="label"
      emit-value
      map-options
      :hint="translate('JS_CHAT_ADD_FAVORITE_ROOM_FROM_MODULE')"
      @filter="filter"
      @input="showRecordsModal"
      hide-bottom-space
      popup-content-class="quasar-reset"
      class="full-width"
      ref="selectModule"
    >
      <template v-slot:no-option>
        <q-item>
          <q-item-section class="text-grey"> {{ translate('JS_NO_RESULTS_FOUND') }} </q-item-section>
        </q-item>
      </template>
      <template v-slot:prepend>
        <q-icon @click.prevent="showRecordsModal(selectModule)" name="mdi-magnify" class="cursor-pointer" />
        <q-tooltip anchor="top middle">{{ translate('JS_CHAT_SEARCH_RECORDS_OF_THE_SELECTED_MODULE') }}</q-tooltip>
      </template>
      <template v-slot:append>
        <q-icon name="mdi-close" @click.prevent="$emit('update:isVisible', false)" class="cursor-pointer" />
        <q-tooltip anchor="top middle">{{ translate('JS_CHAT_HIDE_ADD_PANEL') }}</q-tooltip>
      </template>
      <template v-slot:option="scope">
        <q-item dense v-bind="scope.itemProps" v-on="scope.itemEvents">
          <q-item-section avatar>
            <YfIcon :icon="`userIcon-${scope.opt.id}`" />
          </q-item-section>
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
  name: 'RoomRecordSelect',
  props: {
    isVisible: {
      type: Boolean
    },
    modules: {
      type: Array
    }
  },
  data() {
    return {
      selectModule: null,
      searchModules: []
    }
  },
  watch: {
    isVisible(val) {
      if (val) {
        setTimeout(() => {
          this.$refs.selectModule.showPopup()
        }, 100)
      } else {
        this.selectModule = null
        this.$refs.selectModule.hidePopup()
      }
    }
  },
  computed: {
    ...mapGetters(['config'])
  },
  methods: {
    ...mapMutations(['updateRooms']),
    filter(val, update) {
      if (val === '') {
        update(() => {
          this.searchModules = this.modules
        })
        return
      }
      update(() => {
        const needle = val.toLowerCase()
        this.searchModules = this.modules.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    showRecordsModal(val) {
      if (!val) {
        this.$refs.selectModule.showPopup()
        return
      }
      app.showRecordsList({ module: val, src_module: val }, (modal, instance) => {
        instance.setSelectEvent((responseData, e) => {
          AppConnector.request({
            module: 'Chat',
            action: 'Room',
            mode: 'addToFavorites',
            roomType: 'crm',
            recordId: responseData.id
          }).done(({ result }) => {
            this.updateRooms(result)
          })
        })
      })
    }
  },
  created() {
    this.searchModules = this.modules
  }
}
</script>
<style lang="sass" scoped>
.select-dense
	.q-item
		min-height: 32px
		padding: 2px 16px
</style>
