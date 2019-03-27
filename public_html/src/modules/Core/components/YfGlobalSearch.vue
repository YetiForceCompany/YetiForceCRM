<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <hook-wrapper class="Core-Component-YfGlobalSearch">
    <div>
      <q-btn class="lt-md" round :size="iconSize" flat icon="mdi-magnify" dense />
      <div class="flex gt-sm">
        <div class="q-pl-sm headerField" style="min-width: 200px">
          <q-select v-model="searchModule" :options="searchModules" placeholder="Placeholder" dense />
        </div>
        <q-input
          class="q-pl-lg headerField"
          style="min-width: 200px"
          v-model="searchText"
          placeholder="Placeholder"
          dense
        >
          <template v-slot:after>
            <q-btn round :size="iconSize" flat icon="mdi-magnify" dense />
            <q-btn round :size="iconSize" flat icon="mdi-format-text" dense />
            <q-btn round :size="iconSize" flat icon="mdi-feature-search-outline" dense />
          </template>
        </q-input>
      </div>
    </div>
  </hook-wrapper>
</template>
<script>
export default {
  name: 'YfGlobalSearch',
  data() {
    return {
      searchModule: 'All records',
      searchModules: ['All records', 'acc', 'con'],
      searchText: '',
      iconSize: '.75rem'
    }
  }
}
</script>

<style scoped lang="stylus">
.headerField, .headerField input {
  min-width: 200px;
}
</style>
