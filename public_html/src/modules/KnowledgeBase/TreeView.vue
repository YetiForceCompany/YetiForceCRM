/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

<template>
  <div class="h-100">
    <q-layout view="hHh lpr fFf" container class="absolute">
      <q-header elevated class="bg-primary text-white">
        <q-toolbar> </q-toolbar>
        <q-toolbar class="justify-center q-py-md">
          <q-input v-model="search" square outlined type="search" bg-color="grey-1" class="tree-search">
            <template v-slot:append>
              <q-icon name="mdi-magnify" />
            </template>
          </q-input>
        </q-toolbar>
        <q-toolbar>
          <q-btn dense flat round icon="mdi-menu" @click="left = !left"></q-btn>
        </q-toolbar>
      </q-header>
      <q-drawer v-model="left" side="left" bordered :width="200" :breakpoint="700">
        <q-scroll-area class="fit">
          <q-list v-for="(categoryValue, categoryKey) in mainCategories.categories" :key="categoryKey">
            <q-item clickable :active="categoryValue.label === 'LBL_NONE'" v-ripple>
              <q-item-section avatar>
                <q-icon :name="categoryValue.icon" />
              </q-item-section>
              <q-item-section>
                {{ categoryValue.label }}
              </q-item-section>
            </q-item>
            <!-- <q-separator v-if="categoryValue.separator" /> -->
          </q-list>
        </q-scroll-area>
      </q-drawer>
      <q-page-container>
        <q-page class="q-pa-md">
          <div class="q-pa-md row items-start q-gutter-md">
            <q-card
              v-for="(categoryValue, categoryKey) in mainCategories.categories"
              :key="categoryKey"
              class="home-card"
            >
              <q-card-section>
                <div class="text-h6">{{ categoryValue.label }}</div>
                <div
                  v-for="featuredValue in mainCategories.featured[categoryKey]"
                  :key="featuredValue.id"
                  class="text-subtitle2"
                >
                  {{ featuredValue.subject }}
                </div>
              </q-card-section>
            </q-card>
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
      search: 'test',
      mainCategories: {
        categories: {
          T1: { tree: 'T1', parentTree: 'T1', parent: false, label: 'LBL_NONE', icon: '' },
          T2: { tree: 'T2', parentTree: 'T2', parent: false, label: 'aaaaa', icon: 'fas fa-archive' },
          T3: { tree: 'T3', parentTree: 'T3', parent: false, label: 'aaaaaa', icon: 'fas fa-adjust' },
          T6: { tree: 'T6', parentTree: 'T6', parent: false, label: 'ffff', icon: 'AdditionalIcon-Matrixes' },
          T7: { tree: 'T7', parentTree: 'T7', parent: false, label: 'gggg', icon: '' },
          T14: { tree: 'T14', parentTree: 'T14', parent: false, label: 'mmmmmmmmmm', icon: '' }
        },
        featured: {
          '0': [],
          T1: [
            { id: 306, category: 'T1', subject: 'Narz\u0119dzia' },
            { id: 307, category: 'T1', subject: 'Instrukcja dodawania kolor\u00f3w dla modu\u0142\u00f3w' },
            { id: 375, category: 'T1', subject: 'Narz\u0119dzia' },
            { id: 376, category: 'T1', subject: 'Instrukcja dodawania kolor\u00f3w dla modu\u0142\u00f3w' }
          ],
          T14: [
            { id: 372, category: 'T14', subject: 'Narz\u0119dzia' },
            { id: 373, category: 'T14', subject: 'Instrukcja dodawania kolor\u00f3w dla modu\u0142\u00f3w' }
          ]
        },
        records: []
      },
      records: {
        categories: {
          T12: { tree: 'T12', parentTree: 'T14::T12', parent: 'T14', label: 'bbbbbbbbbbbbb', icon: '' },
          T11: { tree: 'T11', parentTree: 'T14::T11', parent: 'T14', label: 'pppppppppppp', icon: '' },
          T10: { tree: 'T10', parentTree: 'T14::T10', parent: 'T14', label: 'oooooooooooo', icon: '' }
        },
        featured: [[]],
        records: [
          { id: 372, category: 'T14', subject: 'Narz\u0119dzia' },
          { id: 373, category: 'T14', subject: 'Instrukcja dodawania kolor\u00f3w dla modu\u0142\u00f3w' }
        ]
      }
    }
  },
  mounted() {
    this.$axios({
      data: { module: 'Chat', action: 'Room', mode: 'tracking' },
      responseType: 'json',
      method: 'POST',
      url: 'index.php'
    }).then(response => {
      console.log('asdfasdf', response)
      this.$q.notify('Message')
    })
  }
}
</script>
<style scoped>
.tree-search {
  width: 50%;
}
.home-card {
  width: 100%;
  max-width: 250px;
}
</style>
