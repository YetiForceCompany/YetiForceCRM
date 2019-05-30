/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

<template>
  <div class="Knowledge-Base h-100">
    <q-layout view="hHh Lpr fFf" container class="absolute">
      <q-header elevated class="bg-white text-primary">
        <q-toolbar>
          <div v-show="!searchData" class="flex items-center no-wrap">
            <q-btn
              dense
              round
              push
              icon="mdi-menu"
              @click="$q.platform.is.desktop ? (miniState = !miniState) : (left = !left)"
            >
              <q-tooltip>{{ $root.translate('JS_TOGGLE_MENU') }}</q-tooltip>
            </q-btn>
            <q-breadcrumbs class="ml-2">
              <template v-slot:separator>
                <q-icon size="1.5em" name="mdi-chevron-right" />
              </template>
              <q-breadcrumbs-el
                :icon="tree.topCategory.icon"
                :label="$root.translate(tree.topCategory.label)"
                @click="tree.activeCategory === '' ? '' : fetchData()"
                :disabled="tree.activeCategory === ''"
                :class="[tree.activeCategory === '' ? '' : 'cursor-pointer']"
              />
              <template v-if="tree.activeCategory !== ''">
                <q-breadcrumbs-el
                  v-for="(category, index) in tree.categories[tree.activeCategory].parentTree"
                  :key="index"
                  :disabled="index === tree.categories[tree.activeCategory].parentTree.length - 1"
                  :class="[
                    index === tree.categories[tree.activeCategory].parentTree.length - 1 ? '' : 'cursor-pointer'
                  ]"
                  @click="
                    index === tree.categories[tree.activeCategory].parentTree.length - 1 ? '' : fetchData(category)
                  "
                >
                  <icon
                    v-if="tree.categories[category].icon"
                    :size="iconSize"
                    :icon="tree.categories[category].icon"
                    class="q-mr-sm"
                  ></icon>
                  {{ tree.categories[category].label }}
                </q-breadcrumbs-el>
              </template>
            </q-breadcrumbs>
          </div>
          <div class="mx-auto w-50 flex no-wrap">
            <q-input
              class="tree-search"
              v-model="filter"
              placeholder="Search"
              rounded
              outlined
              type="search"
              @input="search"
            >
              <template v-slot:append>
                <q-icon name="mdi-magnify" />
              </template>
              <q-tooltip anchor="top middle" self="center middle">{{
                $root.translate('JS_INPUT_TOO_SHORT').replace('_LENGTH_', '3')
              }}</q-tooltip>
            </q-input>
            <div>
              <q-toggle v-model="categorySearch" icon="mdi-file-tree" />
              <q-tooltip>
                {{ $root.translate('JS_SEARCH_CURRENT_CATEGORY') }}
              </q-tooltip>
            </div>
          </div>
          <q-btn round dense color="white" text-color="primary" icon="mdi-plus" @click="openQuickCreateModal()">
            <q-tooltip>
              {{ $root.translate('JS_QUICK_CREATE') }}
            </q-tooltip>
          </q-btn>
        </q-toolbar>
      </q-header>
      <q-drawer
        v-show="!searchData"
        v-model="left"
        side="left"
        elevated
        :mini="$q.platform.is.desktop ? miniState : false"
        :width="searchData ? 0 : 250"
        :breakpoint="700"
        content-class="bg-white text-black"
      >
        <q-scroll-area class="fit">
          <q-list>
            <q-item v-show="tree.activeCategory === ''" active>
              <q-item-section avatar>
                <q-icon :name="tree.topCategory.icon" :size="iconSize" />
              </q-item-section>
              <q-item-section>
                {{ $root.translate(tree.topCategory.label) }}
              </q-item-section>
            </q-item>
            <q-item
              v-if="tree.activeCategory !== ''"
              clickable
              active
              @click="
                fetchData(
                  tree.categories[tree.activeCategory].parentTree.length !== 1
                    ? tree.categories[tree.activeCategory].parentTree[
                        tree.categories[tree.activeCategory].parentTree.length - 2
                      ]
                    : ''
                )
              "
            >
              <q-item-section avatar>
                <icon :size="iconSize" :icon="tree.categories[tree.activeCategory].icon || defaultTreeIcon" />
              </q-item-section>
              <q-item-section>
                {{ tree.categories[tree.activeCategory].label }}
              </q-item-section>
              <q-item-section avatar>
                <q-icon name="mdi-chevron-left" />
              </q-item-section>
            </q-item>
            <q-item
              v-for="(categoryValue, categoryKey) in tree.data.categories"
              :key="categoryKey"
              clickable
              v-ripple
              @click="fetchData(categoryValue)"
            >
              <q-item-section avatar>
                <icon :size="iconSize" :icon="tree.categories[categoryValue].icon || defaultTreeIcon" />
              </q-item-section>
              <q-item-section>
                {{ tree.categories[categoryValue].label }}
              </q-item-section>
              <q-item-section avatar>
                <q-icon name="mdi-chevron-right" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-scroll-area>
      </q-drawer>
      <q-page-container>
        <q-page class="q-pa-sm">
          <div v-if="!searchData">
            <div
              v-show="typeof tree.data.featured.length === 'undefined'"
              class="q-pa-sm featured-container items-start q-gutter-md"
            >
              <template v-for="(categoryValue, categoryKey) in tree.data.categories">
                <q-list bordered padding dense v-if="tree.data.featured[categoryValue]" :key="categoryKey">
                  <q-item header clickable class="text-black flex" @click="fetchData(categoryValue)">
                    <icon :icon="tree.categories[categoryValue].icon" :size="iconSize" class="mr-2"></icon>
                    {{ tree.categories[categoryValue].label }}
                  </q-item>
                  <q-item
                    clickable
                    v-for="featuredValue in tree.data.featured[categoryValue]"
                    :key="featuredValue.id"
                    class="text-subtitle2"
                    v-ripple
                    @click.prevent="fetchRecord(featuredValue.id)"
                  >
                    <q-item-section class="align-items-center flex-row no-wrap justify-content-start">
                      <q-icon name="mdi-star" :size="iconSize" class="mr-2"></q-icon>
                      <a
                        class="js-popover-tooltip--record ellipsis"
                        :href="`index.php?module=${$options.moduleName}&view=Detail&record=${featuredValue.id}`"
                      >
                        {{ featuredValue.subject }}
                      </a>
                    </q-item-section>
                  </q-item>
                </q-list>
              </template>
            </div>
            <records-list
              v-show="tree.activeCategory !== ''"
              :data="getTableArray(tree.data.records)"
              :title="$root.translate('JS_ARTICLES')"
            />
          </div>
          <records-list
            v-if="getTableArray(searchData).length"
            :data="getTableArray(searchData)"
            :title="$root.translate('JS_ARTICLES')"
          />
        </q-page>
      </q-page-container>
    </q-layout>
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
                  {{ $root.translate('JS_CATEGORY') }}
                </q-tooltip>
              </div>
              <q-separator dark vertical spaced />
              <div>
                <q-icon name="mdi-calendar-clock" size="15px"></q-icon>
                {{ record.short_createdtime }}
                <q-tooltip>
                  {{ $root.translate('JS_CREATED') + ': ' + record.full_createdtime }}
                </q-tooltip>
              </div>
              <template v-if="record.short_modifiedtime">
                <q-separator dark vertical spaced />
                <div>
                  <q-icon name="mdi-square-edit-outline" size="15px"></q-icon>
                  {{ record.short_modifiedtime }}
                  <q-tooltip>
                    {{ $root.translate('JS_MODIFIED') + ': ' + record.full_modifiedtime }}
                  </q-tooltip>
                </div>
              </template>
            </div>
          </div>
          <q-space />
          <q-btn dense flat icon="mdi-window-minimize" @click="maximizedToggle = false" :disable="!maximizedToggle">
            <q-tooltip v-if="maximizedToggle">{{ $root.translate('JS_MINIMIZE') }}</q-tooltip>
          </q-btn>
          <q-btn dense flat icon="mdi-window-maximize" @click="maximizedToggle = true" :disable="maximizedToggle">
            <q-tooltip v-if="!maximizedToggle">{{ $root.translate('JS_MAXIMIZE') }}</q-tooltip>
          </q-btn>
          <q-btn dense flat icon="mdi-close" v-close-popup>
            <q-tooltip>{{ $root.translate('JS_CLOSE') }}</q-tooltip>
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
          <records-list
            v-if="record.related"
            :data="getTableArray(record.related.Articles)"
            :title="$root.translate('JS_RELATED_ARTICLES')"
          />
        </q-card-section>
        <q-card-section v-if="hasRelatedRecords">
          <div class="q-pa-md q-table__title">{{ $root.translate('JS_RELATED_RECORDS') }}</div>
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
  </div>
</template>
<script>
import Icon from '../../../components/Icon.vue'
import Carousel from '../../../components/Carousel.vue'
import RecordsList from './RecordsList.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('KnowledgeBase')
export default {
  name: 'KnowledgeBase',
  components: { Icon, Carousel, RecordsList },
  data() {
    return {
      moduleName: '',
      defaultTreeIcon: 'mdi-subdirectory-arrow-right',
      showing: false,
      miniState: false,
      left: true,
      filter: '',
      categorySearch: false,
      maximizedToggle: true,
      searchData: false
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
    },
    dialog: {
      set(val) {
        this.$store.commit('KnowledgeBase/setDialog', val)
      },
      get() {
        return this.$store.getters['KnowledgeBase/dialog']
      }
    },
    ...mapGetters(['tree', 'record', 'iconSize'])
  },
  methods: {
    getTableArray(tableObject) {
      if (typeof tableObject === 'object') {
        return Object.keys(tableObject).map(function(key) {
          return { ...tableObject[key], id: key }
        })
      } else {
        return []
      }
    },
    search(e) {
      if (this.filter.length >= 3) {
        const aDeferred = $.Deferred()
        const progressIndicatorElement = $.progressIndicator({
          blockInfo: { enabled: true }
        })
        AppConnector.request({
          module: store.getters['KnowledgeBase/moduleName'],
          action: 'KnowledgeBaseAjax',
          mode: 'search',
          value: this.filter,
          category: this.categorySearch ? this.tree.activeCategory : ''
        }).done(data => {
          this.searchData = data.result
          aDeferred.resolve(data.result)
          progressIndicatorElement.progressIndicator({ mode: 'hide' })
          return data.result
        })
      } else {
        this.searchData = false
      }
    },
    openQuickCreateModal() {
      const headerInstance = new window.Vtiger_Header_Js()
      headerInstance.quickCreateModule(store.getters['KnowledgeBase/moduleName'])
    },
    ...mapActions(['fetchCategories', 'fetchData', 'fetchRecord', 'initState'])
  },
  async created() {
    await this.initState(this.$options.state)
    await this.fetchCategories()
    await this.fetchData()
  }
}
</script>
<style>
.tree-search {
  width: 100%;
}
.tree-search .q-field__control,
.tree-search .q-field__marginal {
  height: 40px;
}

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
