/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

<template>
  <div class="h-100">
    <q-layout view="hHh lpr fFf" container class="absolute">
      <q-header elevated class="bg-white text-primary">
        <q-toolbar>
          <q-btn dense flat round icon="mdi-menu" @click="left = !left"></q-btn>
          <q-breadcrumbs class="ml-2">
            <template v-slot:separator>
              <q-icon size="1.5em" name="mdi-chevron-right" />
            </template>
            <q-breadcrumbs-el icon="mdi-home" @click="getData()" />
            <template v-if="this.activeCategory !== ''">
              <q-breadcrumbs-el
                v-for="category in tree.categories[this.activeCategory].parentTree"
                :key="tree.categories[category].label"
                @click="getData(category)"
              >
                <icon :icon="tree.categories[category].icon" class="q-mr-sm"></icon>
                {{ tree.categories[category].label }}
              </q-breadcrumbs-el>
            </template>
            <q-breadcrumbs-el v-if="record !== false" icon="mdi-text" :label="record.subject" />
          </q-breadcrumbs>
          <q-checkbox v-model="categorySearch" label="Search current category" class="ml-auto" />
          <q-input
            v-model="filter"
            placeholder="Search"
            rounded
            outlined
            type="search"
            class="tree-search"
            @input="search"
          >
            <template v-slot:append>
              <q-icon name="mdi-magnify" />
            </template>
          </q-input>
        </q-toolbar>
      </q-header>

      <q-drawer v-model="left" side="left" elevated :width="250" :breakpoint="700" content-class="bg-yeti text-white">
        <q-scroll-area class="fit">
          <q-list dark>
            <q-item v-show="activeCategory === ''" clickable active active-class="text-blue-2">
              <q-item-section avatar>
                <q-icon name="mdi-home" />
              </q-item-section>
              <q-item-section>
                Home
              </q-item-section>
            </q-item>
            <q-item
              v-if="activeCategory !== ''"
              clickable
              :active="!record"
              active-class="text-blue-2"
              @click="
                getData(
                  tree.categories[activeCategory].parentTree.length !== 1
                    ? tree.categories[activeCategory].parentTree[tree.categories[activeCategory].parentTree.length - 2]
                    : ''
                )
              "
            >
              <q-item-section avatar>
                <icon :icon="tree.categories[activeCategory].icon" />
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
                <icon :icon="tree.categories[categoryValue].icon" />
              </q-item-section>
              <q-item-section>
                {{ tree.categories[categoryValue].label }}
              </q-item-section>
              <q-item-section avatar>
                <q-icon name="mdi-chevron-right" />
              </q-item-section>
            </q-item>

            <q-separator v-if="tree.data.records.length" />
            <q-item
              v-for="(recordValue, index) in tree.data.records"
              :key="index"
              clickable
              v-ripple
              :active="record === recordValue"
              @click="record = recordValue"
            >
              <q-item-section avatar>
                <q-icon name="mdi-text" />
              </q-item-section>
              <q-item-section>
                {{ recordValue.subject }}
              </q-item-section>
            </q-item>
          </q-list>
        </q-scroll-area>
      </q-drawer>

      <q-page-container>
        <q-page class="q-pa-md">
          <div v-if="!record && !searchData">
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
                  <q-item-label header>{{ tree.categories[categoryValue].label }}</q-item-label>
                  <q-item
                    clickable
                    v-for="featuredValue in tree.data.featured[categoryValue]"
                    :key="featuredValue.id"
                    class="text-subtitle2"
                    v-ripple
                    @click.prevent="record = featuredValue"
                  >
                    <q-item-section avatar>
                      <q-icon name="mdi-text"></q-icon>
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
              :data="Object.values(tree.data.records)"
              :columns="columns"
              row-key="subject"
              grid
              hide-header
            >
              <template v-slot:item="props">
                <q-list class="list-item" padding @click="record = props.row">
                  <q-item clickable>
                    <q-item-section>
                      <q-item-label overline>{{ props.row.subject }}</q-item-label>
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
            </q-table>
          </div>
          <q-table
            v-if="searchData"
            :data="Object.values(searchData)"
            :columns="columns"
            row-key="subject"
            grid
            hide-header
          >
            <template v-slot:item="props">
              <q-list
                class="list-item"
                padding
                @click="
                  record = props.row
                  searchData = false
                "
              >
                <q-item clickable>
                  <q-item-section>
                    <q-item-label overline>{{ props.row.subject }}</q-item-label>
                    <q-item-label caption>{{ props.row.introduction }}</q-item-label>
                  </q-item-section>
                  <q-item-section side top>
                    <q-item-label caption
                      >{{ props.row.short_time }}
                      <q-tooltip>
                        {{ props.row.full_time }}
                      </q-tooltip>
                    </q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </template>
          </q-table>
          <div v-if="record && !searchData">
            <h5>{{ record.subject }}</h5>
            <p>{{ record.introduction }}</p>
          </div>
        </q-page>
      </q-page-container>
    </q-layout>
  </div>
</template>
<script>
import Icon from '../../components/Icon.vue'
export default {
  name: 'TreeView',
  components: { Icon },
  data() {
    return {
      left: true,
      filter: '',
      record: false,
      categorySearch: false,
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
        categories: {}
      },
      searchData: false
    }
  },
  computed: {},
  methods: {
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
      this.record = false
      const progressIndicatorElement = $.progressIndicator({
        blockInfo: { enabled: true }
      })
      return AppConnector.request({
        module: 'KnowledgeBase',
        action: 'TreeAjax',
        mode: 'data',
        category: category
      }).done(data => {
        this.tree.data = data.result
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
  width: 50%;
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
  max-width: 600px;
}
</style>
