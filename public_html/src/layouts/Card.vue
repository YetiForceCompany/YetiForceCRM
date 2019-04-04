<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="q-pa-xl">
    <q-layout>
      <q-page-container class="row">
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 fixed-center">
          <keep-alive>
            <router-view class="card-shadow q-pa-xl column" style="min-height: unset" />
          </keep-alive>
        </div>
      </q-page-container>
    </q-layout>
  </div>
</template>

<script>
/**
 * @vue-data     {String}    activeComponent - component name
 * @vue-data     {Boolean}   showReminderForm - form data
 * @vue-data     {Boolean}   showLoginForm - form data
 * @vue-computed {Object}    env - env variables
 * @vue-event    {Object}    openURL
 */
export default {
  name: 'Card'
}
</script>

<style scoped>
.card-shadow {
  box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2), 0 2px 2px rgba(0, 0, 0, 0.14), 0 3px 1px -2px rgba(0, 0, 0, 0.12);
}
</style>
