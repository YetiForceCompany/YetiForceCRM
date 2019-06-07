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
    v-model="previewDialog"
    :maximized="previewMaximized"
    transition-show="slide-up"
    transition-hide="slide-down"
    content-class="quasar-reset"
  >
    <drag-resize
      v-if="isDragResize"
      :coordinates="coordinates"
      v-on:onChangeCoordinates="onChangeCoordinates"
      :maximized="previewMaximized"
    >
      <record-preview-content :height="coordinates.height" />
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
  data() {
    return {
      coordinates: {
        width: Quasar.plugins.Screen.width - 100,
        height: Quasar.plugins.Screen.height - 100,
        top: 0,
        left: Quasar.plugins.Screen.width - (Quasar.plugins.Screen.width - 100 / 2)
      }
    }
  },
  computed: {
    ...mapGetters(['previewMaximized']),
    previewDialog: {
      set(val) {
        this.$store.commit('KnowledgeBase/setPreviewDialog', val)
      },
      get() {
        return this.$store.getters['KnowledgeBase/previewDialog']
      }
    }
  },
  methods: {
    onChangeCoordinates: function(coordinates) {
      this.coordinates = coordinates
    }
  }
}
</script>
<style>
</style>
