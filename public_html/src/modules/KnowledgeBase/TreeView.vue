/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

<template>
  <div class="h-100">
    <q-layout view="hHh lpr fFf" container class="absolute">
      <q-header elevated class="bg-white text-primary">
        <q-toolbar>
          <q-btn dense flat round icon="mdi-menu" @click="left = !left"></q-btn>
          <q-breadcrumbs v-show="!searchData" class="ml-2">
            <template v-slot:separator>
              <q-icon size="1.5em" name="mdi-chevron-right" />
            </template>
            <q-breadcrumbs-el
              :icon="tree.topCategory.icon"
              :label="translate(tree.topCategory.label)"
              @click="activeCategory === '' ? '' : getData()"
              :disabled="activeCategory === ''"
              :class="[activeCategory === '' ? '' : 'cursor-pointer']"
            />
            <template v-if="activeCategory !== ''">
              <q-breadcrumbs-el
                v-for="(category, index) in tree.categories[activeCategory].parentTree"
                :key="index"
                :disabled="index === tree.categories[activeCategory].parentTree.length - 1"
                :class="[index === tree.categories[activeCategory].parentTree.length - 1 ? '' : 'cursor-pointer']"
                @click="index === tree.categories[activeCategory].parentTree.length - 1 ? '' : getData(category)"
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
            </q-input>
            <div>
              <q-toggle v-model="categorySearch" icon="mdi-file-tree" />
              <q-tooltip>
                {{ translate('JS_SEARCH_CURRENT_CATEGORY') }}
              </q-tooltip>
            </div>
          </div>
        </q-toolbar>
      </q-header>

      <q-drawer
        v-show="!searchData"
        v-model="left"
        side="left"
        elevated
        :width="searchData ? 0 : 250"
        :breakpoint="700"
        content-class="bg-white text-black"
      >
        <q-scroll-area class="fit">
          <q-list>
            <q-item v-show="activeCategory === ''" clickable active>
              <q-item-section avatar>
                <q-icon :name="tree.topCategory.icon" :size="iconSize" />
              </q-item-section>
              <q-item-section>
                {{ translate(tree.topCategory.label) }}
              </q-item-section>
            </q-item>
            <q-item
              v-if="activeCategory !== ''"
              clickable
              active
              @click="
                getData(
                  tree.categories[activeCategory].parentTree.length !== 1
                    ? tree.categories[activeCategory].parentTree[tree.categories[activeCategory].parentTree.length - 2]
                    : ''
                )
              "
            >
              <q-item-section avatar>
                <icon :size="iconSize" :icon="tree.categories[activeCategory].icon" />
              </q-item-section>
              <q-item-section>
                {{ tree.categories[activeCategory].label }}
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
              @click="getData(categoryValue)"
            >
              <q-item-section avatar>
                <icon :size="iconSize" :icon="tree.categories[categoryValue].icon" />
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
        <q-page class="q-pa-md">
          <div v-if="!searchData">
            <div v-show="typeof tree.data.featured.length === 'undefined'" class="q-pa-md row items-start q-gutter-md">
              <template v-for="(categoryValue, categoryKey) in tree.data.categories">
                <q-list
                  bordered
                  padding
                  dense
                  v-if="tree.data.featured[categoryValue]"
                  :key="categoryKey"
                  class="home-card"
                >
                  <q-item-label header class="text-black flex" @click="getData(categoryValue)">
                    <icon :icon="tree.categories[categoryValue].icon" :size="iconSize" class="mr-2"></icon>
                    {{ tree.categories[categoryValue].label }}
                  </q-item-label>
                  <q-item
                    clickable
                    v-for="featuredValue in tree.data.featured[categoryValue]"
                    :key="featuredValue.id"
                    class="text-subtitle2"
                    v-ripple
                    @click.prevent="getRecord(featuredValue.id)"
                  >
                    <q-item-section avatar>
                      <q-icon name="mdi-star" :size="iconSize"></q-icon>
                    </q-item-section>
                    <q-item-section>
                      <a
                        class="js-popover-tooltip--record"
                        :href="`index.php?module=KnowledgeBase&view=Detail&record=${featuredValue.id}`"
                      >
                        {{ featuredValue.subject }}</a
                      >
                    </q-item-section>
                  </q-item>
                </q-list>
              </template>
            </div>
            <q-table
              v-show="activeCategory !== ''"
              :data="getTableArray(tree.data.records)"
              :columns="columns"
              row-key="subject"
              grid
              hide-header
              :pagination.sync="pagination"
            >
              <template v-slot:item="props">
                <q-list class="list-item" padding @click="getRecord(props.row.id)">
                  <q-item clickable>
                    <q-item-section avatar>
                      <q-icon name="mdi-text" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label class="text-primary"> {{ props.row.subject }}</q-item-label>
                      <q-item-label class="flex" overline>
                        <q-breadcrumbs class="mr-2 text-grey-8" active-color="grey-8">
                          <q-breadcrumbs-el
                            v-for="category in tree.categories[props.row.category].parentTree"
                            :key="tree.categories[category].label"
                          >
                            <icon :size="iconSize" :icon="tree.categories[category].icon" class="q-mr-sm" />
                            {{ tree.categories[category].label }}
                          </q-breadcrumbs-el>
                        </q-breadcrumbs>

                        | Authored by: {{ props.row.assigned_user_id }}</q-item-label
                      >
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
              <template v-slot:bottom="props"> </template>
            </q-table>
          </div>
          <q-table
            v-if="searchData"
            :data="getTableArray(searchData)"
            :columns="columns"
            row-key="subject"
            grid
            hide-header
          >
            <template v-slot:item="props">
              <q-list class="list-item" padding @click="getRecord(props.row.id)">
                <q-item clickable>
                  <q-item-section avatar>
                    <q-icon name="mdi-text" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label class="text-primary"> {{ props.row.subject }}</q-item-label>
                    <q-item-label class="flex" overline>
                      <q-breadcrumbs class="mr-2 text-grey-8" active-color="grey-8">
                        <q-breadcrumbs-el
                          v-for="category in tree.categories[props.row.category].parentTree"
                          :key="tree.categories[category].label"
                        >
                          <icon :size="iconSize" :icon="tree.categories[category].icon" class="q-mr-sm" />
                          {{ tree.categories[category].label }}
                        </q-breadcrumbs-el>
                      </q-breadcrumbs>

                      | Authored by: {{ props.row.assigned_user_id }}</q-item-label
                    >
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
            <template v-slot:bottom="props"> </template>
          </q-table>
        </q-page>
      </q-page-container>
    </q-layout>
    <q-dialog
      v-model="dialog"
      persistent
      :maximized="maximizedToggle"
      transition-show="slide-up"
      transition-hide="slide-down"
    >
      <q-card class="quasar-reset">
        <q-bar>
          <q-space />
          <q-btn dense flat icon="mdi-window-minimize" @click="maximizedToggle = false" :disable="!maximizedToggle">
            <q-tooltip v-if="maximizedToggle" content-class="bg-white text-primary">Minimize</q-tooltip>
          </q-btn>
          <q-btn dense flat icon="mdi-window-maximize" @click="maximizedToggle = true" :disable="maximizedToggle">
            <q-tooltip v-if="!maximizedToggle" content-class="bg-white text-primary">Maximize</q-tooltip>
          </q-btn>
          <q-btn dense flat icon="mdi-close" v-close-popup>
            <q-tooltip content-class="bg-white text-primary">Close</q-tooltip>
          </q-btn>
        </q-bar>

        <q-card-section>
          <div class="text-h6">{{ record.subject }}</div>
        </q-card-section>
        <q-card-section>
          {{ record.introduction }}
        </q-card-section>
        <q-card-section>
          <q-carousel
            v-if="record.knowledgebase_view === 'PLL_PRESENTATION'"
            v-model="slide"
            transition-prev="scale"
            transition-next="scale"
            swipeable
            animated
            control-color="black"
            navigation
            padding
            arrows
            height="300px"
            class="bg-white text-black shadow-1 rounded-borders"
          >
            <q-carousel-slide
              v-for="(slide, index) in record.content"
              :name="index"
              :key="index"
              class="column no-wrap flex-center"
            >
              <div v-html="slide"></div>
            </q-carousel-slide>
          </q-carousel>
          <div v-else v-html="record.content"></div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>
<script>
import Icon from '../../components/Icon.vue'
export default {
  name: 'TreeView',
  components: { Icon },
  data() {
    return {
      iconSize: '18px',
      left: true,
      filter: '',
      record: false,
      dialog: false,
      categorySearch: false,
      maximizedToggle: true,
      slide: 0,
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
      activeCategory: '',
      tree: {
        data: {
          records: [],
          featured: {}
        },
        topCategory: {
          icon: 'mdi-file-tree',
          label: 'JS_TOP_CATEGORIES'
        },
        categories: {}
      },
      searchData: false
    }
  },
  methods: {
    translate(key) {
      return app.vtranslate(key)
    },
    getTableArray(tableObject) {
      return Object.keys(tableObject).map(function(key) {
        return { ...tableObject[key], id: key }
      })
    },
    getCategories() {
      const aDeferred = $.Deferred()
      return AppConnector.request({ module: 'KnowledgeBase', action: 'TreeAjax', mode: 'categories' }).done(data => {
        this.tree.categories = data.result
        aDeferred.resolve(data.result)
      })
    },
    getData(category = '') {
      const aDeferred = $.Deferred()
      this.activeCategory = category
      const progressIndicatorElement = $.progressIndicator({
        blockInfo: { enabled: true }
      })
      return AppConnector.request({
        module: 'KnowledgeBase',
        action: 'TreeAjax',
        mode: 'list',
        category: category
      }).done(data => {
        this.tree.data = data.result
        progressIndicatorElement.progressIndicator({ mode: 'hide' })
        aDeferred.resolve(data.result)
      })
    },
    getRecord(id) {
      const aDeferred = $.Deferred()
      const progressIndicatorElement = $.progressIndicator({
        blockInfo: { enabled: true }
      })
      return AppConnector.request({
        module: 'KnowledgeBase',
        action: 'TreeAjax',
        mode: 'detail',
        record: id
      }).done(data => {
        this.record = data.result
        this.dialog = true
        progressIndicatorElement.progressIndicator({ mode: 'hide' })
        aDeferred.resolve(data.result)
      })
    },
    search(e) {
      if (this.filter.length > 3) {
        const aDeferred = $.Deferred()
        const progressIndicatorElement = $.progressIndicator({
          blockInfo: { enabled: true }
        })
        AppConnector.request({
          module: 'KnowledgeBase',
          action: 'TreeAjax',
          mode: 'search',
          value: this.filter,
          category: this.categorySearch ? this.activeCategory : ''
        }).done(data => {
          this.searchData = data.result
          aDeferred.resolve(data.result)
          progressIndicatorElement.progressIndicator({ mode: 'hide' })
          return data.result
        })
      } else {
        this.searchData = false
      }
    }
  },
  async created() {
    await this.getCategories()
    await this.getData()
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
.home-card {
  width: 100%;
  max-width: 250px;
}
.list-item {
  width: 100%;
}
</style>
