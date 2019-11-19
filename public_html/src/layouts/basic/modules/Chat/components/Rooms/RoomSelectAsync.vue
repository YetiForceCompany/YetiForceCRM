<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <RoomSelect
    class="q-pb-xs"
    :options="customOptions"
    :filter="customFilter"
    :isVisible.sync="showSearchRoom"
  />
</template>
<script>
import RoomSelect from './RoomSelect.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'RoomSelectAsync',
  components: { RoomSelect },
  props: {
    isVisible: {
      type: Boolean
    },
    showSearchRoom: {
      type: Boolean
    },
    roomType: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      customOptions: []
    }
  },
  methods: {
    customFilter(val, update) {
      console.log(this.roomType)
      const stringOptions = ['Google', 'Facebook', 'Twitter', 'Apple', 'Oracle']
      setTimeout(() => {
        update(() => {
          console.log(val)
          if (val === '') {
            this.customOptions = stringOptions
          } else {
            const needle = val.toLowerCase()
            this.customOptions = stringOptions.filter(
              v => v.toLowerCase().indexOf(needle) > -1
            )
          }
        })
      }, 1500)
    }
  }
}
</script>
<style lang="sass" scoped></style>
