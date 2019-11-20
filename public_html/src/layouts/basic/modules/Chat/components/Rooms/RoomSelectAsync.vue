<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <RoomSelect
    class="q-pb-xs"
    :options="asyncOptions"
    :filter="asyncFilter"
    :isVisible.sync="getIsVisible"
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
    roomType: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      asyncOptions: []
    }
  },
  computed: {
    getIsVisible: {
      get() {
        return this.isVisible
      },
      set(isVisible) {
        this.$emit('update:isVisible', isVisible)
      }
    }
  },
  created() {
    console.log(this.roomType)
  },
  methods: {
    ...mapActions(['fetchRoomsUnpinned']),
    asyncFilter(val, update) {
      this.fetchRoomsUnpinned().then(data => {
        console.log(data)
              update(() => {
        if (val === '') {
          this.asyncOptions = []
        } else {
          this.asyncOptions = []
        }
      })
      })
    }
  }
}
</script>
<style lang="sass" scoped></style>
