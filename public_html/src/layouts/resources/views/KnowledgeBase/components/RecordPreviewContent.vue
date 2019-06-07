<!--
/**
 * RecordPreviewContent component
 *
 * @description Part of q-dialog
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
<template>
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
        <template v-if="$q.platform.is.desktop">
          <a v-show="!previewMaximized" class="flex grabbable text-decoration-none text-white" href="#">
            <q-icon class="js-drag" name="mdi-drag" size="19px" />
          </a>
          <q-btn
            dense
            flat
            :icon="previewMaximized ? 'mdi-window-restore' : 'mdi-window-maximize'"
            @click="previewMaximized = !previewMaximized"
          >
            <q-tooltip>{{ previewMaximized ? translate('JS_MINIMIZE') : translate('JS_MAXIMIZE') }}</q-tooltip>
          </q-btn>
        </template>
        <q-btn dense flat icon="mdi-close" v-close-popup>
          <q-tooltip>{{ translate('JS_CLOSE') }}</q-tooltip>
        </q-btn>
      </slot>
    </q-bar>
    <q-card-section
      :class="['scroll', previewMaximized ? 'modal-full-height' : '']"
      :style="height ? { 'max-height': `${height - 31.14}px` } : {}"
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
          :data="record.related.base.Articles"
          :title="translate('JS_RELATED_ARTICLES')"
        />
      </div>
      <div v-if="hasRelatedRecords">
        <q-separator />
        <div class="q-pa-md q-table__title">{{ translate('JS_RELATED_RECORDS') }}</div>
        <div class="q-pa-sm row items-start q-col-gutter-md">
          <template v-for="(moduleRecords, parentModule) in record.related.dynamic">
            <div v-if="moduleRecords.length === undefined" :class="[relatedColClass]" :key="parentModule">
              <q-list bordered padding dense>
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
            </div>
          </template>
        </div>
      </div>
      <div v-if="hasRelatedComments">
        <q-separator />
        <div class="q-pa-md q-table__title">{{ translate('JS_COMMENTS') }}</div>
        <q-list padding>
          <q-item v-for="(relatedRecord, relatedRecordId) in record.related.base.ModComments" :key="relatedRecordId">
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
</template>
<script>
import Icon from '../../../../../components/Icon.vue'
import Carousel from './Carousel.vue'
import RecordsList from './RecordsList.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('KnowledgeBase')
export default {
  name: 'RecordPreviewContent',
  components: { Icon, Carousel, RecordsList },
  props: {
    height: {
      type: Number,
      default: 0
    },
    previewMaximized: {
      type: Boolean,
      true: 0
    }
  },
  computed: {
    ...mapGetters(['tree', 'record', 'iconSize']),
    hasRelatedRecords() {
      if (this.record) {
        return Object.keys(this.record.related.dynamic).some(obj => {
          return this.record.related.dynamic[obj].length === undefined
        })
      }
    },
    relatedColClass() {
      if (this.record) {
        let relatedModules = 0
        let relatedColClass = 'col'
        Object.keys(this.record.related.dynamic).forEach(key => {
          if (this.record.related.dynamic[key].length === undefined) {
            relatedModules++
          }
        })
        if (relatedModules === 2) {
          relatedColClass = 'col-sm-6'
        } else if (relatedModules === 3) {
          relatedColClass = 'col-sm-6 col-md-4'
        }
        return relatedColClass
      }
    },
    hasRelatedArticles() {
      return this.record ? this.record.related.base.Articles.length !== 0 : false
    },
    hasRelatedComments() {
      return this.record ? this.record.related.base.ModComments.length !== 0 : false
    }
  },
  watch: {
    previewMaximized() {
      this.$emit('onMaximizedToggle', this.previewMaximized)
    }
  },
  methods: {
    ...mapActions(['fetchCategories', 'fetchRecord', 'initState']),
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
