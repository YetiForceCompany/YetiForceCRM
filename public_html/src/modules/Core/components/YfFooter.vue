<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <hook-wrapper class="Core-Component-YfFooter">
    <q-footer elevated class="bg-blue-grey-10 text-blue-grey-11 row no-wrap">
      <q-toolbar>
        <div class="flex wrap full-width items-center justify-between">
          <yf-breadcrumbs />
          <yf-copyright />
        </div>
      </q-toolbar>
    </q-footer>
  </hook-wrapper>
</template>

<script>
import YfBreadcrumbs from './YfBreadcrumbs.js'
import YfCopyright from './YfCopyright.js'

export default {
  name: 'YfFooter',
  components: { YfBreadcrumbs, YfCopyright },
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

<style module lang="stylus"></style>
