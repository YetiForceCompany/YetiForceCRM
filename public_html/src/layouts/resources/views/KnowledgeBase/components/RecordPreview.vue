/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

<template>
  <q-dialog
    v-model="dialog"
    :maximized="maximized"
    transition-show="slide-up"
    transition-hide="slide-down"
    content-class="quasar-reset"
  >
    <vue-drag-resize
      @activated="onActivated"
      :isResizable="true"
      :isDraggable="!maximized"
      v-on:resizing="resize"
      v-on:dragging="resize"
      dragHandle=".js-drag"
      :sticks="['br']"
      :x="this.left"
      :y="this.top"
      :w="this.width"
      :h="this.height"
      :class="[maximized ? 'fit position-sticky' : 'modal-mini', 'overflow-hidden']"
      ref="resize"
    >
      <q-card class="KnowledgeBase__RecordPreview">
        <q-bar dark class="bg-yeti text-white dialog-header">
          <div class="flex items-center">
            <div class="flex items-center no-wrap ellipsis q-mr-sm-sm">
              <q-icon name="mdi-text" class="q-mr-sm" />
              {{ record.subject }}
            </div>
            <div class="flex items-center text-grey-4 small">
              <div class="flex items-center">
                <q-icon :name="tree.topCategory.icon" size="15px"></q-icon>
                <q-icon size="1.5em" name="mdi-chevron-right" />
                <span v-html="record.category" class="flex items-center"></span>
                <q-tooltip>
                  {{ translate('JS_CATEGORY') }}
                </q-tooltip>
              </div>
              <q-separator dark vertical spaced />
              <div>
                <q-icon name="mdi-calendar-clock" size="15px"></q-icon>
                {{ record.short_createdtime }}
                <q-tooltip>
                  {{ translate('JS_CREATED') + ': ' + record.full_createdtime }}
                </q-tooltip>
              </div>
              <template v-if="record.short_modifiedtime">
                <q-separator dark vertical spaced />
                <div>
                  <q-icon name="mdi-square-edit-outline" size="15px"></q-icon>
                  {{ record.short_modifiedtime }}
                  <q-tooltip>
                    {{ translate('JS_MODIFIED') + ': ' + record.full_modifiedtime }}
                  </q-tooltip>
                </div>
              </template>
            </div>
          </div>
          <q-space />
          <slot name="header-right">
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
            <q-btn dense flat icon="mdi-close" v-close-popup>
              <q-tooltip>{{ translate('JS_CLOSE') }}</q-tooltip>
            </q-btn>
          </slot>
        </q-bar>
        <q-card-section
          :class="['scroll', maximized ? 'modal-full-height' : '']"
          :style="{ 'max-height': `${this.height - 31.14}px` }"
        >
          <div v-show="record.introduction">
            <div class="text-subtitle2 text-bold">{{ record.introduction }}</div>
          </div>
          <div v-show="record.content">
            <q-resize-observer @resize="onResize" />
            <div ref="content">
              <carousel v-if="record.view === 'PLL_PRESENTATION' && record.content.length > 1" :record="record" />
              <div v-else>
                <q-separator />
                <div v-html="typeof record.content === 'object' ? record.content[0] : record.content"></div>
              </div>
            </div>
          </div>
          <div v-if="hasRelatedArticles">
            <q-separator />
            <records-list
              v-if="record.related"
              :data="record.related.Articles"
              :title="translate('JS_RELATED_ARTICLES')"
            />
          </div>
          <div v-if="hasRelatedRecords">
            <q-separator />
            <div class="q-pa-md q-table__title">{{ translate('JS_RELATED_RECORDS') }}</div>
            <div class="q-pa-sm featured-container items-start q-gutter-md">
              <template v-for="(moduleRecords, parentModule) in record.related">
                <q-list
                  bordered
                  padding
                  dense
                  :key="parentModule"
                  v-if="
                    parentModule !== 'Articles' && parentModule !== 'ModComments' && moduleRecords.length === undefined
                  "
                >
                  <q-item header clickable class="text-black flex">
                    <icon :icon="'userIcon-' + parentModule" :size="iconSize" class="mr-2"></icon>
                    {{ record.translations[parentModule] }}
                  </q-item>
                  <q-item
                    clickable
                    v-for="(relatedRecord, relatedRecordId) in moduleRecords"
                    :key="relatedRecordId"
                    class="text-subtitle2"
                    v-ripple
                  >
                    <q-item-section class="align-items-center flex-row no-wrap justify-content-start">
                      <a
                        class="js-popover-tooltip--record ellipsis"
                        :href="`index.php?module=${parentModule}&view=Detail&record=${relatedRecordId}`"
                      >
                        {{ relatedRecord }}
                      </a>
                    </q-item-section>
                  </q-item>
                </q-list>
              </template>
            </div>
          </div>
          <div v-if="hasRelatedComments">
            <q-separator />
            <div class="q-pa-md q-table__title">{{ translate('JS_COMMENTS') }}</div>
            <q-list padding>
              <q-item v-for="(relatedRecord, relatedRecordId) in record.related.ModComments" :key="relatedRecordId">
                <q-item-section avatar top>
                  <q-avatar size="iconSize">
                    <img v-if="relatedRecord.avatar.url !== undefined" :src="relatedRecord.avatar.url" />
                    <q-icon v-else name="mdi-account" />
                  </q-avatar>
                </q-item-section>
                <q-item-section>
                  <q-item-label>
                    <a
                      class="js-popover-tooltip--record"
                      :href="`index.php?module=Users&view=Detail&record=${relatedRecord.userid}`"
                      >{{ relatedRecord.userName }}
                    </a>
                  </q-item-label>
                  <q-item-label><div v-html="relatedRecord.comment"></div></q-item-label>
                </q-item-section>
                <q-item-section side top>
                  <q-item-label caption>{{ relatedRecord.modifiedShort }}</q-item-label>
                  <q-tooltip anchor="top middle" self="center middle">
                    {{ translate('JS_MODIFIED') + ': ' + relatedRecord.modifiedFull }}
                  </q-tooltip>
                </q-item-section>
              </q-item>
            </q-list>
          </div>
        </q-card-section>
      </q-card>
    </vue-drag-resize>
  </q-dialog>
</template>
<script>
import VueDragResize from 'vue-drag-resize'
import Icon from '../../../../../components/Icon.vue'
import Carousel from './Carousel.vue'
import RecordsList from './RecordsList.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('KnowledgeBase')
export default {
  name: 'RecordPreview',
  components: { Icon, Carousel, RecordsList, VueDragResize },
  data() {
    return {
      maximized: true,
      dragging: false,
      width: this.$q.screen.width - 100,
      height: this.$q.screen.height - 100,
      top: 0,
      left: this.$q.screen.width - (this.$q.screen.width - 100 / 2)
    }
  },
  computed: {
    hasRelatedRecords() {
      if (this.record) {
        return Object.keys(this.record.related).some(obj => {
          return obj !== 'Articles' && obj !== 'ModComments' && this.record.related[obj].length === undefined
        })
      }
    },
    hasRelatedArticles() {
      return this.record ? this.record.related.Articles.length !== 0 : false
    },
    hasRelatedComments() {
      return this.record ? this.record.related.ModComments.length !== 0 : false
    },
    ...mapGetters(['tree', 'record', 'iconSize']),
    dialog: {
      set(val) {
        this.$store.commit('KnowledgeBase/setDialog', val)
      },
      get() {
        return this.$store.getters['KnowledgeBase/dialog']
      }
    }
  },
  methods: {
    ...mapActions(['fetchCategories', 'fetchData', 'fetchRecord', 'initState']),
    resize(newRect) {
      this.width = newRect.width
      this.height = newRect.height
      this.top = newRect.top
      this.left = newRect.left
    },
    onActivated() {
      $(this.$refs.resize.$el)
        .find('.vdr-stick')
        .addClass('mdi mdi-resize-bottom-right q-btn q-btn--dense q-btn--round q-icon contrast-50')
    },
    onResize(size) {
      if (this.$refs.content !== undefined) {
        $(this.$refs.content)
          .find('img')
          .css('max-width', size.width)
      }
    }
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
