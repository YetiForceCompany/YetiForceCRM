/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

<template>
  <q-dialog
    v-model="dialog"
    :maximized="maximized"
    transition-show="slide-up"
    transition-hide="slide-down"
    content-class="quasar-reset"
  >
    <drag-resize v-if="isDragResize && !$q.platform.is.mobile">
      <template v-slot:default="slotProps">
        <record-preview-content :height="slotProps.height" />
      </template>
    </drag-resize>
    <record-preview-content v-else />
  </q-dialog>
</template>
<script>
import DragResize from './DragResize.vue'
import RecordPreviewContent from './RecordPreviewContent.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters } = createNamespacedHelpers('KnowledgeBase')
export default {
  name: 'RecordPreview',
  components: { RecordPreviewContent, DragResize },
  props: {
    isDragResize: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    ...mapGetters(['maximized']),
    dialog: {
      set(val) {
        this.$store.commit('KnowledgeBase/setDialog', val)
      },
      get() {
        return this.$store.getters['KnowledgeBase/dialog']
      }
    }
  }
}
</script>
<style>
</style>
