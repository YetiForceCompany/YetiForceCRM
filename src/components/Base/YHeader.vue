<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-header elevated class="bg-white text-muted">
    <q-toolbar>
      <q-btn class="lt-md" round :size="iconSize" flat icon="search" dense />
      <div class="flex gt-sm">
        <div :class="['q-pl-md', $style.headerField]">
          <q-select v-model="searchModule" :options="searchModules" placeholder="Placeholder" dense />
        </div>
        <q-input :class="['q-pl-lg', $style.headerField]" v-model="searchText" placeholder="Placeholder" dense>
          <template v-slot:after>
            <q-btn round :size="iconSize" flat icon="mdi-format-text" dense />
            <q-btn round :size="iconSize" flat icon="mdi-feature-search-outline" dense />
          </template>
        </q-input>
      </div>
      <div class="q-ml-auto">
        <q-btn round :size="iconSize" flat icon="mdi-plus" />
        <q-btn round :size="iconSize" flat icon="mdi-email-outline" />
        <q-btn round :size="iconSize" flat icon="mdi-bell-ring-outline" />
        <q-btn round :size="iconSize" flat icon="mdi-calendar-multiselect" />
        <q-btn round :size="iconSize" flat icon="mdi-history" />
        <q-btn round :size="iconSize" flat icon="mdi-settings-outline" />
        <q-btn round :size="iconSize" flat icon="mdi-power-standby" />
      </div>
    </q-toolbar>
  </q-header>
</template>

<script>
export default {
  name: 'YHeader',
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
.headerField {
  min-width: 200px;
}
</style>
