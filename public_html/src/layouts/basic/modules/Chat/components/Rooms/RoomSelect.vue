<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="full-width">
    <q-select
      ref="selectedOption"
      v-model="selectedOption"
      class="full-width"
      :hint="translate('JS_CHAT_ADD_FAVORITE_ROOM_FROM_MODULE')"
      :options="options"
      dense
      use-input
      fill-input
      hide-selected
      input-debounce="0"
      option-value="id"
      option-label="label"
      emit-value
      map-options
      hide-bottom-space
      popup-content-class="quasar-reset"
      @input="callbackInput($event)"
      @filter="filter"
    >
      <template #no-option>
        <q-item>
          <q-item-section class="text-grey"> {{ translate('JS_NO_RESULTS_FOUND') }} </q-item-section>
        </q-item>
      </template>
      <template #prepend>
        <slot
          name="prepend"
          :selected="selectedOption"
        ></slot>
      </template>
      <template #append>
        <q-icon
          class="cursor-pointer"
          name="mdi-close"
          @click.prevent="$emit('update:isVisible', false)"
        />
        <q-tooltip anchor="top middle">{{ translate('JS_CHAT_HIDE_ADD_PANEL') }}</q-tooltip>
      </template>
      <template
        v-if="hasOptionSlot"
        #option="scope"
      >
        <slot
          name="option"
          :scope="scope"
        ></slot>
      </template>
    </q-select>
  </div>
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')

export default {
  name: 'RoomSelect',
  props: {
    isVisible: {
      type: Boolean
    },
    options: {
      type: Array
    },
    filter: {
      type: Function,
      required: false
    }
  },
  data() {
    return {
      selectedOption: null,
      computedOptions: []
    }
  },
  computed: {
    hasOptionSlot() {
      return !!this.$scopedSlots.option
    }
  },
  watch: {
    isVisible(val) {
      if (val) {
        setTimeout(() => {
          this.$refs.selectedOption.showPopup()
        }, 100)
      } else {
        this.selectedOption = null
        this.$refs.selectedOption.hidePopup()
      }
    }
  },
  methods: {
    callbackInput(e) {
      this.$emit('input', e)
    }
  }
}
</script>
<style lang="sass" scoped>
.select-dense
	.q-item
		min-height: 32px
		padding: 2px 16px
</style>
