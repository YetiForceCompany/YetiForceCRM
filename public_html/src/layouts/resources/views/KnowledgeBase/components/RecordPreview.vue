<!--
/**
 * RecordPreview component
 *
 * @description Record preview parent component
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
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
    <record-preview-content v-else>
      <template slot="header-right">
        <slot name="header-right"></slot>
      </template>
    </record-preview-content>
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
