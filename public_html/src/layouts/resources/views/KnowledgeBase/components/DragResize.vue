<!--
/**
 * DragResize component
 *
 * @description use of vue-drag-resize
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
<template>
  <div>
    <vue-drag-resize
      @activated="onActivated"
      :isResizable="true"
      :isDraggable="!maximized"
      v-on:resizing="resize"
      v-on:dragging="resize"
      dragHandle=".js-drag"
      :sticks="['br']"
      :x="coordinates.left"
      :y="coordinates.top"
      :w="coordinates.width"
      :h="coordinates.height"
      :class="[maximized ? 'fit position-sticky' : 'modal-mini', 'overflow-hidden']"
      ref="resize"
    >
      <slot :height="coordinates.height"></slot>
    </vue-drag-resize>
  </div>
</template>

<script>
import VueDragResize from 'vue-drag-resize'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters } = createNamespacedHelpers('KnowledgeBase')
export default {
  name: 'DragResize',
  components: { VueDragResize },
  computed: {
    ...mapGetters(['maximized']),
    coordinates: {
      set(val) {
        this.$store.commit('KnowledgeBase/setCoordinates', val)
      },
      get() {
        return this.$store.getters['KnowledgeBase/coordinates']
      }
    }
  },
  methods: {
    resize(newRect) {
      this.coordinates = {
        width: newRect.width,
        height: newRect.height,
        top: newRect.top,
        left: newRect.left
      }
    },
    onActivated() {
      $(this.$refs.resize.$el)
        .find('.vdr-stick')
        .addClass('mdi mdi-resize-bottom-right q-btn q-btn--dense q-btn--round q-icon contrast-50')
    }
  }
}
</script>

<style>
.modal-mini {
  max-height: unset !important;
  max-width: unset !important;
}
.vdr-stick.q-icon:before {
  font-size: 1.718em;
  left: -5px;
  position: relative;
  bottom: 5px;
}
.vdr-stick.q-icon {
  bottom: 9px !important;
  right: 25px !important;
  font-size: 14px;
  background: none;
  border: none;
  box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2), 0 2px 2px rgba(0, 0, 0, 0.14), 0 3px 1px -2px rgba(0, 0, 0, 0.12);
  display: none;
  cursor: nwse-resize !important;
  position: absolute !important;
}
.vdr.active {
  font-weight: unset;
}
.modal-mini .vdr-stick {
  display: inline-flex;
}
</style>
