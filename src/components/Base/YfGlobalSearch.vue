<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    <q-btn class="lt-md" round :size="iconSize" flat icon="mdi-magnify" dense />
    <div class="flex gt-sm">
      <div :class="['q-pl-sm', $style.headerField]">
        <q-select v-model="searchModule" :options="searchModules" placeholder="Placeholder" dense />
      </div>
      <q-input :class="['q-pl-lg', $style.headerField]" v-model="searchText" placeholder="Placeholder" dense>
        <template v-slot:after>
          <q-btn round :size="iconSize" flat icon="mdi-magnify" dense />
          <q-btn round :size="iconSize" flat icon="mdi-format-text" dense />
          <q-btn round :size="iconSize" flat icon="mdi-feature-search-outline" dense />
        </template>
      </q-input>
    </div>
  </div>
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

<style module lang="stylus">
.headerField, .headerField input {
  min-width: 200px;
}
</style>
