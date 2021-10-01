
<template>
  <q-card class="KnowledgeBase__ArticlePreview fit">
    <q-bar
      class="bg-yeti text-white dialog-header"
      dark
    >
      <div class="flex items-center">
        <div class="flex items-center no-wrap ellipsis q-mr-sm-sm">
          <q-icon
            class="q-mr-sm"
            name="mdi-text"
          />
          {{ record.subject }}
        </div>
        <div class="flex items-center text-grey-4 small">
          <div class="flex items-center">
            <q-icon
              :name="tree.topCategory.icon"
              size="15px"
            ></q-icon>
            <q-icon
              size="1.5em"
              name="mdi-chevron-right"
            />
            <span
              v-html="record.category"
              class="flex items-center"
            ></span>
            <q-tooltip>
              {{ translate('JS_KB_CATEGORY') }}
            </q-tooltip>
          </div>
          <q-separator
            dark
            vertical
            spaced
          />
          <div>
            <q-icon
              name="mdi-calendar-clock"
              size="15px"
            ></q-icon>
            {{ record.short_createdtime }}
            <q-tooltip>
              {{ translate('JS_KB_CREATED') + ': ' + record.full_createdtime }}
            </q-tooltip>
          </div>
          <template v-if="record.short_modifiedtime">
            <q-separator
              dark
              vertical
              spaced
            />
            <div>
              <q-icon
                name="mdi-square-edit-outline"
                size="15px"
              ></q-icon>
              {{ record.short_modifiedtime }}
              <q-tooltip>
                {{ translate('JS_KB_MODIFIED') + ': ' + record.full_modifiedtime }}
              </q-tooltip>
            </div>
          </template>
          <template v-if="record.accountId">
            <q-separator
              dark
              vertical
              spaced
            />
            <YfIcon
              icon="yfm-Accounts"
              size="15px"
            ></YfIcon>
            <a
              class="js-popover-tooltip--record ellipsis q-ml-xs text-grey-4"
              :href="`index.php?module=Accounts&view=Detail&record=${record.accountId}`"
            >{{ record.accountName }}
            </a>
          </template>
        </div>
      </div>
      <q-space />
      <slot name="headerRight">
        <template v-if="$q.platform.is.desktop">
          <a
            v-show="!previewMaximized"
            class="flex grabbable text-decoration-none text-white"
            href="#"
          >
            <q-icon
              class="js-drag"
              name="mdi-drag"
              size="19px"
            />
          </a>
          <q-btn
            :icon="previewMaximized ? 'mdi-window-restore' : 'mdi-window-maximize'"
            dense
            flat
            @click="toggleMaximized()"
          >
            <q-tooltip>{{ previewMaximized ? translate('JS_MINIMIZE') : translate('JS_MAXIMIZE') }}</q-tooltip>
          </q-btn>
        </template>
        <q-btn
          dense
          flat
          icon="mdi-close"
          v-close-popup
        >
          <q-tooltip>{{ translate('JS_CLOSE') }}</q-tooltip>
        </q-btn>
      </slot>
    </q-bar>
    <q-card-section
      :class="['scroll', previewMaximized ? 'modal-full-height' : '']"
      :style="height ? { 'max-height': `${height - 31.14}px` } : {}"
    >
      <div v-show="record.introduction">
        <div class="text-subtitle2 text-bold" v-html="record.introduction"></div>
      </div>
      <div v-show="record.content">
        <q-resize-observer @resize="onResize" />
        <div ref="content">
          <carousel
            v-if="record.view === 'PLL_PRESENTATION' && record.content.length > 1"
            :record="record"
          />
          <div v-else>
            <q-separator />
            <div v-html="typeof record.content === 'object' ? record.content[0] : record.content"></div>
          </div>
        </div>
      </div>
      <div v-if="hasRelatedComments">
        <q-separator />
        <div class="q-pa-md q-table__title">{{ translate('JS_KB_COMMENTS') }}</div>
        <q-list padding>
          <q-item
            v-for="(relatedRecord, relatedRecordId) in record.related.base.ModComments"
            :key="relatedRecordId"
          >
            <q-item-section
              avatar
              top
            >
              <q-avatar size="iconSize">
                <img
                  v-if="relatedRecord.avatar.url !== undefined"
                  :src="relatedRecord.avatar.url"
                  alt="user image"
                />
                <q-icon
                  v-else
                  name="mdi-account"
                />
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
              <q-item-label>
                <div v-html="relatedRecord.comment"></div>
              </q-item-label>
            </q-item-section>
            <q-item-section
              side
              top
            >
              <q-item-label caption>{{ relatedRecord.modifiedShort }}</q-item-label>
              <q-tooltip
                anchor="top middle"
                self="center middle"
              >
                {{ translate('JS_KB_MODIFIED') + ': ' + relatedRecord.modifiedFull }}
              </q-tooltip>
            </q-item-section>
          </q-item>
        </q-list>
      </div>
      <div v-if="hasRelatedArticles">
        <q-separator />
        <articles-list
          v-if="record.related"
          :data="record.related.base.Articles"
          :title="translate('JS_KB_RELATED_ARTICLES')"
        />
      </div>
      <div v-show="relatedRecords.length">
        <q-separator />
        <div class="q-pa-md q-table__title">{{ translate('JS_KB_RELATED_RECORDS') }}</div>
        <columns-grid :columnBlocks="relatedRecords">
          <template #default="slotProps">
            <q-list
              bordered
              padding
              dense
            >
              <q-item
                header
                clickable
                class="text-black flex"
              >
                <YfIcon
                  :icon="'yfm-' + slotProps.relatedBlock"
                  :size="iconSize"
                  class="mr-2"
                ></YfIcon>
                {{ record.translations[slotProps.relatedBlock] }}
              </q-item>
              <q-item
                v-for="(relatedRecord, relatedRecordId) in record.related.dynamic[slotProps.relatedBlock]"
                :key="relatedRecordId"
                class="text-subtitle2"
                clickable
                v-ripple
              >
                <q-item-section class="align-items-center flex-row no-wrap justify-content-start">
                  <a
                    class="js-popover-tooltip--record ellipsis"
                    :href="`index.php?module=${slotProps.relatedBlock}&view=Detail&record=${relatedRecordId}`"
                  >
                    {{ relatedRecord }}
                  </a>
                </q-item-section>
              </q-item>
            </q-list>
          </template>
        </columns-grid>
      </div>
    </q-card-section>
  </q-card>
</template>
<script>
import YfIcon from '~/components/YfIcon.vue'
import ColumnsGrid from '~/components/ColumnsGrid.vue'
import Carousel from './Carousel.vue'
import ArticlesList from './ArticlesList.vue'

import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('KnowledgeBase')
export default {
  name: 'ArticlePreviewContent',
  components: { YfIcon, Carousel, ArticlesList, ColumnsGrid },
  props: {
    height: {
      type: Number,
      default: 0
    },
    previewMaximized: {
      type: Boolean
    }
  },
  computed: {
    ...mapGetters(['tree', 'record', 'iconSize']),
    relatedRecords() {
      if (this.record) {
        let arr = Object.keys(this.record.related.dynamic).map(key => {
          return this.record.related.dynamic[key].length !== 0 ? key : false
        })
        return arr.filter(function(item) {
          return typeof item === 'string'
        })
      } else {
        return []
      }
    },
    hasRelatedArticles() {
      return this.record
        ? this.record.related.base.Articles.length !== 0
        : false
    },
    hasRelatedComments() {
      return this.record
        ? this.record.related.base.ModComments.length !== 0
        : false
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
    },
    toggleMaximized() {
	  let classList = this.$el.parentElement.parentElement.classList
	  this.previewMaximized ? classList.remove('fit') : classList.add('fit')
      this.$emit('update:previewMaximized', !this.previewMaximized)
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
