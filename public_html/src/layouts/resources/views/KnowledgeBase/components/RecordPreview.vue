/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

<template>
  <q-dialog
    v-model="dialog"
    :maximized="maximized"
    transition-show="slide-up"
    transition-hide="slide-down"
    content-class="quasar-reset"
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
          <q-btn dense flat icon="mdi-window-minimize" @click="maximized = false" :disable="!maximized">
            <q-tooltip v-if="maximized">{{ translate('JS_MINIMIZE') }}</q-tooltip>
          </q-btn>
          <q-btn dense flat icon="mdi-window-maximize" @click="maximized = true" :disable="maximized">
            <q-tooltip v-if="!maximized">{{ translate('JS_MAXIMIZE') }}</q-tooltip>
          </q-btn>
          <q-btn dense flat icon="mdi-close" v-close-popup>
            <q-tooltip>{{ translate('JS_CLOSE') }}</q-tooltip>
          </q-btn>
        </slot>
      </q-bar>
      <q-card-section v-show="record.introduction">
        <div class="text-subtitle2 text-bold">{{ record.introduction }}</div>
      </q-card-section>
      <q-card-section v-show="record.content">
        <carousel v-if="record.view === 'PLL_PRESENTATION'" :record="record" />
        <div v-else>
          <q-separator />
          <div v-html="record.content"></div>
        </div>
      </q-card-section>
      <q-card-section v-if="hasRelatedArticles">
        <q-separator />
        <records-list v-if="record.related" :data="record.related.Articles" :title="translate('JS_RELATED_ARTICLES')" />
      </q-card-section>
      <q-card-section v-if="hasRelatedRecords">
        <q-separator />
        <div class="q-pa-md q-table__title">{{ translate('JS_RELATED_RECORDS') }}</div>
        <div class="q-pa-sm featured-container items-start q-gutter-md">
          <template v-for="(moduleRecords, parentModule) in record.related">
            <q-list
              bordered
              padding
              dense
              :key="parentModule"
              v-if="parentModule !== 'Articles' && parentModule !== 'ModComments' && moduleRecords.length === undefined"
            >
              <q-item header clickable class="text-black flex">
                <icon :icon="'userIcon-' + parentModule" :size="iconSize" class="mr-2"></icon>
                {{ parentModule }}
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
      </q-card-section>
      <q-card-section v-if="hasRelatedComments">
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
      </q-card-section>
    </q-card>
  </q-dialog>
</template>
<script>
import Icon from '../../../../../components/Icon.vue'
import Carousel from './Carousel.vue'
import RecordsList from './RecordsList.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('KnowledgeBase')
export default {
  name: 'KnowledgeBase',
  components: { Icon, Carousel, RecordsList },
  data() {
    return {
      maximized: true
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
    ...mapActions(['fetchCategories', 'fetchData', 'fetchRecord', 'initState'])
  }
}
</script>
<style>
.KnowledgeBase__RecordPreview .featured-container {
  grid-template-columns: repeat(auto-fill, minmax(33.3%, 1fr));
}

.dialog-header {
  padding-top: 3px;
  padding-bottom: 3px;
  height: unset !important;
}
</style>
