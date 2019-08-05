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
      :label="translate('JS_CHAT_ADD_FAVORITE_ROOM_FROM_MODULE')"
      @filter="filter"
      @input="showRecordsModal"
      popup-content-class="quasar-reset"
      class="full-width"
      ref="selectModule"
    >
      <template v-slot:no-option>
        <q-item>
          <q-item-section class="text-grey"> {{ translate('JS_NO_RESULTS_FOUND') }} </q-item-section>
        </q-item>
      </template>
      <template v-slot:append>
        <q-icon name="mdi-close" @click.stop="$emit('update:isVisible', false)" class="cursor-pointer" />
      </template>
    </q-select>
  </div>
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')

export default {
  name: 'SelectModules',
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
    filter(val, update) {
      if (val === '') {
        update(() => {
          this.searchModules = this.modules
        })
        return
      }
      update(() => {
        const needle = val.toLowerCase()
        this.searchModules = this.modules.filter(v => v.toLowerCase().indexOf(needle) > -1)
      })
    },
    showRecordsModal(val) {
      app.showRecordsList({ module: val }, (modal, instance) => {
        instance.setSelectEvent((responseData, e) => {
          AppConnector.request({
            module: 'Chat',
            action: 'Room',
            mode: 'addToFavorites',
            roomType: 'crm',
            recordId: responseData.id
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
<style lang="sass">
</style>
