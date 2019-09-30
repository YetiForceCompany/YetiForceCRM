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
      :preventActiveBehavior="true"
      :isActive="active"
      @activated="onActivated"
      @dragstop="correctCoordinates"
      @resizestop="correctCoordinates"
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
      :minh="minHeight"
      :minw="minWidth"
      :class="[maximized ? 'fit position-static' : 'modal-mini', 'overflow-hidden']"
      ref="resize"
    >
      <div class="fit">
        <slot></slot>
      </div>
    </vue-drag-resize>
    <div class="fit" v-else>
      <slot></slot>
    </div>
  </div>
</template>

<script>
import VueDragResize from '~/node_modules/vue-drag-resize/src/components/vue-drag-resize.vue'
import { keepElementInWindow } from '~/mixins/DragResize'
export default {
  name: 'DragResize',
  mixins: [keepElementInWindow],
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
      active: false,
      minHeight: 300,
      minWidth: 300,
      minVisibleHeight: 32,
      minVisibleWidth: 120
    }
  },
  methods: {
    resize(newRect, e) {
      this.$emit('update:coordinates', {
        width: newRect.width,
        height: newRect.height,
        top: newRect.top,
        left: newRect.left
      })
    },
    correctCoordinates(rect) {
      let computedRect = Object.assign({}, rect)

      if (rect.width > window.innerWidth) {
        computedRect.width = window.innerWidth
        computedRect.left = 0
      } else if (rect.left + rect.width - this.minVisibleWidth < 0) {
        computedRect.left = this.minVisibleWidth - rect.width
      } else if (rect.width + rect.left > window.innerWidth) {
        computedRect.left = window.innerWidth - rect.width
      }

      if (rect.height > window.innerHeight) {
        computedRect.height = window.innerHeight
        computedRect.top = 0
      } else if (rect.top < 0) {
        computedRect.top = 0
      } else if (rect.top > window.innerHeight - this.minVisibleHeight) {
        computedRect.top = window.innerHeight - this.minVisibleHeight
      }
      this.$emit('update:coordinates', computedRect)
      this.$emit('dragstop', true)
    },
    onActivated() {
      const sticks = this.$refs.resize.$el.querySelectorAll('.vdr-stick')
      Array.prototype.map.call(sticks, element => {
        for (let prop in this.stickStyle) {
          element.style[prop] = this.stickStyle[prop]
        }
      })
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
