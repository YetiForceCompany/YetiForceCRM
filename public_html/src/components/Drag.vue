<!--
/**
 * DragResize component
 *
 * @description Use of vue-drag-resize
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
<template>
    <vue-drag-resize
      :isResizable="false"
      :isDraggable="true"
			:parentLimitation="true"
      v-on:dragging="drag"
      :x="coordinates.left"
      :y="coordinates.top"
			:w="width"
			:h="height"
			ref="drag"
    >
			<slot></slot>
    </vue-drag-resize>
</template>

<script>
import VueDragResize from '~/node_modules/vue-drag-resize/src/components/vue-drag-resize.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters } = createNamespacedHelpers('KnowledgeBase')
export default {
  name: 'DragResize',
  components: { VueDragResize },
  props: {
    coordinates: {
      type: Object,
      required: true
    },
    width: {
      type: Number,
      default: 42
    },
    height: {
      type: Number,
      default: 42
    },
  },
  methods: {
    drag(newRect, e) {
      this.$emit('update:coordinates', {
        top: newRect.top,
        left: newRect.left
      })
    },
  }
}
</script>

<style>
</style>
