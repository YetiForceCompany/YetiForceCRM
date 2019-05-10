<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    <q-card>
      <q-card-section> {{ moduleName }} page </q-card-section>
    </q-card>
  </div>
</template>

<script>
const moduleName = 'Basic.Detail'
export default {
  name: moduleName,
  data() {
    return {
      moduleName: moduleName
    }
  }
}
</script>
