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
  <div>
    <vue-drag-resize
      v-if="$q.platform.is.desktop"
      :isActive="active"
      @activated="onActivated"
      :isResizable="true"
      :isDraggable="!maximized"
      v-on:resizing="resize"
      v-on:dragging="resize"
      dragHandle=".js-drag"
      :sticks="sticks"
      :x="coordinates.left"
      :y="coordinates.top"
      :w="coordinates.width"
      :h="coordinates.height"
      :class="[maximized ? 'fit position-sticky' : 'modal-mini', 'overflow-hidden']"
      ref="resize"
    >
      <div class="fit" @mousedown="onFocusElement($event)" @touchstart="onFocusElement($event)">
        <slot></slot>
      </div>
    </vue-drag-resize>
    <div class="fit" v-else>
      <slot></slot>
    </div>
  </div>
</template>

<script>
import VueDragResize from 'vue-drag-resize'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters } = createNamespacedHelpers('KnowledgeBase')
export default {
  name: 'DragResize',
  components: { VueDragResize },
  props: {
    maximized: {
      type: Boolean,
      required: true
    },
    coordinates: {
      type: Object,
      required: true
    },
    sticks: {
      type: Array,
      default: function() {
        return ['br', 'bl', 'tr', 'tl']
      }
    },
    stickStyle: {
      type: Object,
      default: function() {
        return {
          height: '15px',
          width: '15px',
          border: 'none',
          'box-shadow': 'none',
          'background-color': 'transparent'
        }
      }
    }
  },
  data() {
    return {
      active: false
    }
  },
  methods: {
    resize(newRect) {
      this.$emit('update:coordinates', {
        width: newRect.width,
        height: newRect.height,
        top: newRect.top,
        left: newRect.left
      })
    },
    onActivated() {
      const sticks = this.$refs.resize.$el.querySelectorAll('.vdr-stick')
      Array.prototype.map.call(sticks, element => {
        for (let prop in this.stickStyle) {
          element.style[prop] = this.stickStyle[prop]
        }
      })
    },
    onFocusElement(event) {
      event.target.focus()
    }
  },
  mounted() {
    this.active = true
  }
}
</script>

<style>
.modal-mini {
  max-height: unset !important;
  max-width: unset !important;
  box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.2), 0 4px 5px rgba(0, 0, 0, 0.14), 0 1px 10px rgba(0, 0, 0, 0.12);
}
.vdr.active {
  font-weight: unset;
}
.modal-mini .vdr-stick {
  display: inline-flex;
}
</style>
