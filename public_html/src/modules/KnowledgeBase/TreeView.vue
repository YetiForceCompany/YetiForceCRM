/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

<template>
  <div class="h-100">
    <q-layout view="hHh lpr fFf" container class="absolute">
      <q-header elevated class="bg-primary text-white">
        <q-toolbar>
          <q-btn dense flat round icon="mdi-menu" @click="left = !left"></q-btn>
          <q-toolbar-title>
            Knowledge Base
          </q-toolbar-title>

          <q-breadcrumbs active-color="info">
            <q-breadcrumbs-el icon="mdi-home" @click="getData()" />
            <template v-if="this.active !== ''">
              <q-breadcrumbs-el
                v-for="category in tree.categories[this.active].parentTree"
                :key="tree.categories[category].label"
                :label="tree.categories[category].label"
                @click="getData(category)"
              />
            </template>
            <q-breadcrumbs-el v-if="record !== false" icon="mdi-text" :label="record.subject" />
          </q-breadcrumbs>
          <q-input
            v-model="filter"
            placeholder="Search"
            square
            outlined
            type="search"
            bg-color="grey-1"
            class="tree-search"
            @input="search"
          >
            <template v-slot:append>
              <q-icon name="mdi-magnify" />
            </template>
          </q-input>
        </q-toolbar>
      </q-header>

      <q-drawer v-model="left" side="left" elevated :width="250" :breakpoint="700">
        <q-scroll-area class="fit">
          <q-list>
            <q-item v-show="active === ''" clickable active>
              <q-item-section avatar>
                <q-icon name="mdi-home" />
              </q-item-section>
              <q-item-section>
                Home
              </q-item-section>
            </q-item>
            <q-item
              v-if="active !== ''"
              clickable
              :active="!record"
              @click="
                getData(
                  tree.categories[active].parentTree.length !== 1
                    ? tree.categories[active].parentTree[tree.categories[active].parentTree.length - 2]
                    : ''
                )
              "
            >
              <q-item-section avatar>
                <q-icon v-if="/^mdi|^fa/.test(tree.categories[active].icon)" :name="tree.categories[active].icon" />
                <q-icon v-else :class="[tree.categories[active].icon, 'q-icon']" />
              </q-item-section>
              <q-item-section>
                {{ tree.categories[active].label }}
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
                <q-icon
                  v-if="/^mdi|^fa/.test(tree.categories[categoryValue].icon)"
                  :name="tree.categories[categoryValue].icon"
                />
                <q-icon v-else :class="[tree.categories[categoryValue].icon, 'q-icon']" />
              </q-item-section>
              <q-item-section>
                {{ tree.categories[categoryValue].label }}
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
            <div class="q-pa-md row items-start q-gutter-md">
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
                    @click="record = featuredValue"
                  >
                    <q-item-section avatar>
                      <q-icon name="mdi-text"></q-icon>
                    </q-item-section>
                    <q-item-section> {{ featuredValue.subject }} </q-item-section>
                  </q-item>
                </q-list>
              </template>
            </div>
            <div class="q-pa-md row items-start q-gutter-md">
              <q-table
                v-if="active !== ''"
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
                      </q-item-section>
                    </q-item>
                  </q-list>
                </template>
              </q-table>
            </div>
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
                    <q-item-label caption>{{ props.row.short_time }}</q-item-label>
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
export default {
  name: 'TreeView',
  data() {
    return {
      left: true,
      filter: '',
      record: false,
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
      active: '',
      tree: {
        data: {
          records: []
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
      this.active = category
      this.record = false
      console.log(category)
      return AppConnector.request({
        module: 'KnowledgeBase',
        action: 'TreeAjax',
        mode: 'data',
        category: category
      }).done(data => {
        this.tree.data = data.result
        console.log(data.result)
        aDeferred.resolve(data.result)
      })
    },
    search(e) {
      if (this.filter.length > 3) {
        const aDeferred = $.Deferred()
        AppConnector.request({
          module: 'KnowledgeBase',
          action: 'TreeAjax',
          mode: 'search',
          value: this.filter
        }).done(data => {
          this.searchData = data.result
          aDeferred.resolve(data.result)
          return data.result
        })
      } else {
        this.searchData = false
      }
    }
  },
  created() {
    this.getCategories()
    this.getData()
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
  width: 50%;
  max-width: 100%;
}
</style>
