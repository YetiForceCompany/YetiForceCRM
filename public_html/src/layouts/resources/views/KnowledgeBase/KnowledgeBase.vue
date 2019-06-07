<!--
/**
 * KnowledgeBase component
 *
 * @description Knowledge base view root component
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
<template>
  <div class="KnowledgeBase h-100">
    <q-layout
      view="hHh Lpr fFf"
      container
      class="absolute"
      :style="coordinates.height && !maximized ? { 'max-height': `${coordinates.height - 31.14}px` } : {}"
    >
      <q-header elevated class="bg-white text-primary">
        <q-toolbar class="q-py-xs flex-wrap flex-md-nowrap">
          <div v-show="!searchData" class="flex items-center no-wrap q-mr-auto q-mr-sm-sm">
            <q-btn dense round push icon="mdi-menu" @click="toggleDrawer()">
              <q-tooltip>{{ translate('JS_TOGGLE_CATEGORY_MENU') }}</q-tooltip>
            </q-btn>
            <q-breadcrumbs class="ml-2">
              <template v-slot:separator>
                <q-icon size="1.5em" name="mdi-chevron-right" />
              </template>
              <q-breadcrumbs-el
                :icon="tree.topCategory.icon"
                :label="translate(tree.topCategory.label)"
                @click="activeCategory === '' ? '' : fetchData()"
                :class="[activeCategory === '' ? 'text-black' : 'cursor-pointer']"
              />
              <template v-if="activeCategory !== ''">
                <q-breadcrumbs-el
                  v-for="(category, index) in tree.categories[activeCategory].parentTree"
                  :key="index"
                  :class="[
                    index === tree.categories[activeCategory].parentTree.length - 1 ? 'text-black' : 'cursor-pointer'
                  ]"
                  @click="index === tree.categories[activeCategory].parentTree.length - 1 ? '' : fetchData(category)"
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
          <div class="mx-auto tree-search flex no-wrap order-sm-none order-xs-last q-pt-sm-none q-pt-xs-xs">
            <q-input
              class="full-width"
              v-model="filter"
              :placeholder="translate('JS_SEARCH_PLACEHOLDER')"
              rounded
              outlined
              type="search"
              @input="search"
              autofocus
            >
              <template v-slot:prepend>
                <q-icon name="mdi-magnify" />
                <q-tooltip v-model="inputFocus" anchor="top middle" self="center middle">{{
                  translate('JS_INPUT_TOO_SHORT').replace('_LENGTH_', '3')
                }}</q-tooltip>
              </template>
              <template v-slot:append>
                <q-icon v-if="filter !== ''" name="mdi-close" @click.stop="clearSearch()" class="cursor-pointer" />
                <div class="flex items-center q-ml-sm">
                  <icon-info :customOptions="{ iconSize: '21px' }">
                    <div style="white-space: pre-line;" v-html="translate('JS_FULL_TEXT_SEARCH_INFO')"></div>
                  </icon-info>
                </div>
                <div v-show="activeCategory !== ''" class="flex">
                  <q-toggle v-model="categorySearch" icon="mdi-file-tree" />
                  <q-tooltip>{{ translate('JS_SEARCH_CURRENT_CATEGORY') }}</q-tooltip>
                </div>
              </template>
            </q-input>
          </div>
          <div class="q-ml-auto q-ml-sm-sm">
            <q-btn round dense color="white" text-color="primary" icon="mdi-plus" @click="openQuickCreateModal()">
              <q-tooltip>{{ translate('JS_QUICK_CREATE') }}</q-tooltip>
            </q-btn>
          </div>
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
        ref="drawer"
      >
        <q-scroll-area class="fit">
          <q-list>
            <q-item v-show="activeCategory === ''" active>
              <q-item-section avatar>
                <q-icon :name="tree.topCategory.icon" :size="iconSize" />
              </q-item-section>
              <q-item-section>{{ translate(tree.topCategory.label) }}</q-item-section>
            </q-item>
            <q-item v-if="activeCategory !== ''" clickable active @click="fetchParentCategoryData()">
              <q-item-section avatar>
                <icon :size="iconSize" :icon="tree.categories[activeCategory].icon || defaultTreeIcon" />
              </q-item-section>
              <q-item-section>{{ tree.categories[activeCategory].label }}</q-item-section>
              <q-item-section avatar>
                <q-icon name="mdi-chevron-left" />
              </q-item-section>
            </q-item>
            <q-item
              v-for="(categoryValue, categoryKey) in data.categories"
              :key="categoryKey"
              clickable
              v-ripple
              @click="fetchData(categoryValue)"
            >
              <q-item-section avatar>
                <icon :size="iconSize" :icon="tree.categories[categoryValue].icon || defaultTreeIcon" />
              </q-item-section>
              <q-item-section>{{ tree.categories[categoryValue].label }}</q-item-section>
              <q-item-section avatar>
                <q-icon name="mdi-chevron-right" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-scroll-area>
      </q-drawer>
      <q-page-container>
        <q-page class="q-pa-sm">
          <div v-show="!searchData">
            <div
              v-if="typeof data.featured.length === 'undefined' && data.categories"
              class="q-pa-sm featured-container items-start q-gutter-md"
            >
              <template v-for="(categoryValue, categoryKey) in data.categories">
                <q-list bordered padding dense v-if="data.featured[categoryValue]" :key="categoryKey">
                  <q-item header clickable class="text-black flex" @click="fetchData(categoryValue)">
                    <icon :icon="tree.categories[categoryValue].icon" :size="iconSize" class="mr-2"></icon>
                    {{ tree.categories[categoryValue].label }}
                  </q-item>
                  <q-item
                    clickable
                    v-for="featuredValue in data.featured[categoryValue]"
                    :key="featuredValue.id"
                    class="text-subtitle2"
                    v-ripple
                    @click.prevent="fetchRecord(featuredValue.id)"
                  >
                    <q-item-section class="align-items-center flex-row no-wrap justify-content-start">
                      <q-icon name="mdi-star" :size="iconSize" class="mr-2"></q-icon>
                      <a
                        class="js-popover-tooltip--record ellipsis"
                        :href="`index.php?module=${moduleName}&view=Detail&record=${featuredValue.id}`"
                        >{{ featuredValue.subject }}</a
                      >
                    </q-item-section>
                  </q-item>
                </q-list>
              </template>
            </div>
            <records-list v-show="activeCategory !== ''" :data="data.records" :title="translate('JS_ARTICLES')" />
          </div>
          <records-list v-show="searchData" :data="searchDataArray" :title="translate('JS_ARTICLES')" />
        </q-page>
      </q-page-container>
    </q-layout>
    <record-preview :isDragResize="true" />
  </div>
</template>
<script>
import Icon from '../../../../components/Icon.vue'
import IconInfo from '../../../../components/IconInfo.vue'
import Carousel from './components/Carousel.vue'
import RecordsList from './components/RecordsList.vue'
import RecordPreview from './components/RecordPreview.vue'
import { createNamespacedHelpers } from 'vuex'

const { mapGetters, mapActions } = createNamespacedHelpers('KnowledgeBase')
export default {
  name: 'KnowledgeBase',
  components: { Icon, IconInfo, Carousel, RecordsList, RecordPreview },
  props: {
    coordinates: {
      type: Object,
      default: () => {
        return {
          width: 0,
          height: 0,
          top: 0,
          left: 0
        }
      }
    }
  },
  data() {
    return {
      defaultTreeIcon: 'mdi-subdirectory-arrow-right',
      drawerBehaviour: 'desktop',
      miniState: false,
      left: true,
      filter: '',
      categorySearch: false,
      searchData: false,
      activeCategory: '',
      data: {
        categories: [],
        records: [],
        featured: {}
      }
    }
  },
  computed: {
    ...mapGetters(['tree', 'record', 'iconSize', 'moduleName', 'maximized']),
    searchDataArray() {
      return this.searchData ? this.searchData : []
    },
    inputFocus: {
      set(val) {
        return false
      },
      get() {
        return this.filter.length > 0
      }
    }
  },
  methods: {
    search() {
      if (this.filter.length >= 3) {
        this.debouncedSearch()
      } else {
        this.searchData = false
      }
    },
    clearSearch() {
      this.filter = ''
      this.searchData = false
    },
    fetchParentCategoryData() {
      let parentCategory = ''
      const parentTreeArray = this.tree.categories[this.activeCategory].parentTree
      if (parentTreeArray.length !== 1) {
        parentCategory = parentTreeArray[parentTreeArray.length - 2]
      }
      this.fetchData(parentCategory)
    },
    fetchData(category = '') {
      const aDeferred = $.Deferred()
      this.activeCategory = category
      const progressIndicatorElement = $.progressIndicator({
        blockInfo: { enabled: true }
      })
      return AppConnector.request({
        module: this.moduleName,
        action: 'KnowledgeBaseAjax',
        mode: 'list',
        category: category
      }).done(data => {
        let listData = data.result
        if (listData.records) {
          listData.records = Object.keys(listData.records).map(function(key) {
            return { ...listData.records[key], id: key }
          })
        }
        this.data = listData
        progressIndicatorElement.progressIndicator({ mode: 'hide' })
        aDeferred.resolve(listData)
      })
    },
    openQuickCreateModal() {
      const headerInstance = new window.Vtiger_Header_Js()
      headerInstance.quickCreateModule(this.moduleName)
    },
    toggleDrawer() {
      if (this.$q.platform.is.desktop && (!this.coordinates.width || this.coordinates.width > 700)) {
        this.miniState = !this.miniState
      } else {
        this.left = !this.left
      }
    },
    ...mapActions(['fetchRecord', 'fetchCategories'])
  },
  async created() {
    await this.fetchCategories()
    await this.fetchData()
  },
  mounted() {
    const debounceDelay = 1000
    this.debouncedSearch = Quasar.utils.debounce(() => {
      if (this.filter.length < 3) {
        return
      }
      const aDeferred = $.Deferred()
      const progressIndicatorElement = $.progressIndicator({
        blockInfo: { enabled: true }
      })
      AppConnector.request({
        module: this.moduleName,
        action: 'KnowledgeBaseAjax',
        mode: 'search',
        value: this.filter,
        category: this.categorySearch ? this.activeCategory : ''
      }).done(data => {
        let listData = data.result
        if (listData) {
          listData = Object.keys(listData).map(function(key) {
            return { ...listData[key], id: key }
          })
        }
        this.searchData = listData
        aDeferred.resolve(listData)
        progressIndicatorElement.progressIndicator({ mode: 'hide' })
        return listData
      })
    }, debounceDelay)
  }
}
</script>
<style>
.tree-search {
  min-width: 320px;
  width: 50%;
}
.tree-search .q-field__control,
.tree-search .q-field__marginal {
  height: 40px;
}
.featured-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  grid-auto-flow: dense;
}
</style>
