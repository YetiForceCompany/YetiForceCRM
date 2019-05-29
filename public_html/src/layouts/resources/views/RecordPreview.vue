/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

<template>
  <q-dialog v-model="dialog" :maximized="maximizedToggle" transition-show="slide-up" transition-hide="slide-down">
    <q-card class="quasar-reset Knowledge-Base__dialog">
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
        <q-btn dense flat icon="mdi-close" @click="hideModal()">
          <q-tooltip>{{ translate('JS_CLOSE') }}</q-tooltip>
        </q-btn>
      </q-bar>
      <q-card-section v-show="record.introduction">
        <div class="text-subtitle2 text-bold">{{ record.introduction }}</div>
      </q-card-section>
      <q-card-section v-show="record.content">
        <carousel v-if="record.view === 'PLL_PRESENTATION'" :record="record" />
        <div v-else v-html="record.content"></div>
      </q-card-section>
      <q-card-section v-if="hasRelatedArticles">
        <q-table
          v-if="record.related"
          :data="getTableArray(record.related.Articles)"
          :columns="columns"
          row-key="subject"
          grid
          hide-header
          :title="translate('JS_RELATED_ARTICLES')"
        >
          <template v-slot:item="props">
            <q-list class="list-item" padding @click="getRecord(props.row.id)">
              <q-item clickable>
                <q-item-section avatar>
                  <q-icon name="mdi-text" />
                </q-item-section>
                <q-item-section>
                  <q-item-label class="text-primary"> {{ props.row.subject }}</q-item-label>
                  <q-item-label class="flex items-center" overline>
                    <q-breadcrumbs class="mr-2 text-grey-8" active-color="grey-8">
                      <q-breadcrumbs-el
                        v-for="category in tree.categories[props.row.category].parentTree"
                        :key="tree.categories[category].label"
                      >
                        <icon
                          v-if="tree.categories[category].icon"
                          :size="iconSize"
                          :icon="tree.categories[category].icon"
                          class="q-mr-sm"
                        />
                        {{ tree.categories[category].label }}
                      </q-breadcrumbs-el>
                      <q-tooltip>
                        {{ translate('JS_CATEGORY') }}
                      </q-tooltip>
                    </q-breadcrumbs>
                    | {{ translate('JS_AUTHORED_BY') }}:
                    <span v-html="props.row.assigned_user_id" class="q-ml-sm"></span>
                  </q-item-label>
                  <q-item-label caption>{{ props.row.introduction }}</q-item-label>
                </q-item-section>
                <q-item-section side top>
                  <q-item-label caption>{{ props.row.short_time }}</q-item-label>
                  <q-tooltip>
                    {{ props.row.full_time }}
                  </q-tooltip>
                </q-item-section>
              </q-item>
            </q-list>
          </template>
          <template v-slot:bottom="props"></template>
        </q-table>
      </q-card-section>
      <q-card-section v-if="hasRelatedRecords">
        <div class="q-pa-md q-table__title">{{ translate('JS_RELATED_RECORDS') }}</div>
        <div v-if="record.related" class="q-pa-sm featured-container items-start q-gutter-md">
          <template v-for="(moduleRecords, moduleName) in record.related">
            <q-list
              bordered
              padding
              dense
              v-if="moduleName !== 'Articles' && moduleRecords.length === undefined"
              :key="moduleName"
            >
              <q-item header clickable class="text-black flex">
                <icon :icon="'userIcon-' + moduleName" :size="iconSize" class="mr-2"></icon>
                {{ moduleName }}
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
                    :href="`index.php?module=${moduleName}&view=Detail&record=${relatedRecordId}`"
                  >
                    {{ relatedRecord }}
                  </a>
                </q-item-section>
              </q-item>
            </q-list>
          </template>
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>
<script>
import Icon from '../../../components/Icon.vue'
import Carousel from '../../../components/Carousel.vue'
export default {
  name: 'KnowledgeBase',
  components: { Icon, Carousel },
  data() {
    return {
      defaultTreeIcon: 'mdi-subdirectory-arrow-right',
      showing: false,
      miniState: false,
      iconSize: '18px',
      record: false,
      dialog: true,
      maximizedToggle: true,
      pagination: {
        rowsPerPage: 0
      },
      columns: [
        {
          name: 'desc',
          required: true,
          label: 'Title',
          align: 'left',
          field: row => row.subject,
          format: val => `${val}`,
          sortable: true
        },
        { name: 'short_time', align: 'center', label: 'Short time', field: 'short_time', sortable: true },
        { name: 'introduction', align: 'center', label: 'Introduction', field: 'introduction', sortable: true }
      ],
      tree: {
        topCategory: {
          icon: 'mdi-file-tree',
          label: 'JS_MAIN_CATEGORIES'
        },
        categories: {}
      }
    }
  },
  computed: {
    hasRelatedRecords() {
      if (this.record) {
        return Object.keys(this.record.related).some(obj => {
          return obj !== 'Articles' && this.record.related[obj].length === undefined
        })
      } else {
        return false
      }
    },
    hasRelatedArticles() {
      return this.record ? this.record.related.Articles.length === undefined : false
    }
  },
  methods: {
    translate(key) {
      return app.vtranslate(key)
    },
    getTableArray(tableObject) {
      if (typeof tableObject === 'object') {
        return Object.keys(tableObject).map(function(key) {
          return { ...tableObject[key], id: key }
        })
      } else {
        return []
      }
    },
    getCategories() {
      const aDeferred = $.Deferred()
      return AppConnector.request({
        module: this.$options.options.moduleName,
        action: 'KnowledgeBaseAjax',
        mode: 'categories'
      }).done(data => {
        this.tree.categories = data.result
        aDeferred.resolve(data.result)
      })
    },
    getRecord(id) {
      const aDeferred = $.Deferred()
      const progressIndicatorElement = $.progressIndicator({
        blockInfo: { enabled: true }
      })
      return AppConnector.request({
        module: this.$options.options.moduleName,
        action: 'KnowledgeBaseAjax',
        mode: 'detail',
        record: id
      }).done(data => {
        this.record = data.result
        this.dialog = true
        progressIndicatorElement.progressIndicator({ mode: 'hide' })
        aDeferred.resolve(data.result)
      })
    },
    hideModal() {
      app.hideModalWindow()
      this.$destroy()
    }
  },
  async created() {
    await this.getCategories()
    await this.getRecord(this.$options.options.recordId)
  }
}
</script>
<style>
.Knowledge-Base .q-table__bottom,
.Knowledge-Base__dialog .q-table__bottom {
  display: none;
}

.list-item {
  width: 100%;
}
.featured-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  grid-auto-flow: dense;
}

.dialog-header {
  padding-top: 3px;
  padding-bottom: 3px;
  height: unset !important;
}
</style>
