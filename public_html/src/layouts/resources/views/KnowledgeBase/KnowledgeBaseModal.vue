<!--
/**
 * KnowledgeBaseModal component
 *
 * @description KnowledgeBaseModal parent component
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
    <drag-resize :coordinates="coordinates" v-on:onChangeCoordinates="onChangeCoordinates" :maximized="maximized">
      <q-card class="KnowledgeBaseModal full-height">
        <q-bar dark class="bg-yeti text-white dialog-header">
          <div class="flex items-center">
            <div class="flex items-center no-wrap ellipsis q-mr-sm-sm">
              <span :class="[`userIcon-${moduleName}`, 'q-mr-sm']"></span>
              {{ translate(`JS_${moduleName.toUpperCase()}`) }}
            </div>
          </div>
          <q-space />
          <template v-if="$q.platform.is.desktop">
            <a v-show="!maximized" class="flex grabbable text-decoration-none text-white" href="#">
              <q-icon class="js-drag" name="mdi-drag" size="19px" />
            </a>
            <q-btn
              dense
              flat
              :icon="maximized ? 'mdi-window-restore' : 'mdi-window-maximize'"
              @click="maximized = !maximized"
            >
              <q-tooltip>{{ maximized ? translate('JS_MINIMIZE') : translate('JS_MAXIMIZE') }}</q-tooltip>
            </q-btn>
          </template>
          <q-btn dense flat icon="mdi-close" v-close-popup>
            <q-tooltip>{{ translate('JS_CLOSE') }}</q-tooltip>
          </q-btn>
        </q-bar>
        <div>
          <knowledge-base :coordinates="coordinates" />
        </div>
      </q-card>
    </drag-resize>
  </q-dialog>
</template>
<script>
import DragResize from './components/DragResize.vue'
import KnowledgeBase from './KnowledgeBase.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('KnowledgeBase')
export default {
  name: 'KnowledgeBaseModal',
  components: { KnowledgeBase, DragResize },
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
    ...mapGetters(['maximized', 'moduleName']),
    dialog: {
      set(val) {
        this.$store.commit('KnowledgeBase/setDialog', val)
      },
      get() {
        return this.$store.getters['KnowledgeBase/dialog']
      }
    },
    maximized: {
      set(val) {
        this.$store.commit('KnowledgeBase/setMaximized', val)
      },
      get() {
        return this.$store.getters['KnowledgeBase/maximized']
      }
    }
  },
  methods: {
    ...mapActions(['fetchCategories', 'initState']),
    onChangeCoordinates: function(coordinates) {
      this.coordinates = coordinates
    }
  },
  async created() {
    await this.initState(this.$options.state)
  }
}
</script>
<style>
.dialog-header {
  padding-top: 3px !important;
  padding-bottom: 3px !important;
  height: unset !important;
}
.modal-full-height {
  max-height: calc(100vh - 31.14px) !important;
}
.grabbable:hover {
  cursor: move;
  cursor: grab;
  cursor: -moz-grab;
  cursor: -webkit-grab;
}
.grabbable:active {
  cursor: grabbing;
  cursor: -moz-grabbing;
  cursor: -webkit-grabbing;
}
.contrast-50 {
  filter: contrast(50%);
}
</style>
