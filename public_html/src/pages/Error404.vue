<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="fixed-center text-center">
    <p class="text-faded">
      Sorry, nothing here...
      <strong>(404)</strong>
    </p>
    <q-btn color="secondary" style="width:200px;" @click="$router.push('/')">Go back</q-btn>
  </div>
</template>

<script>
export default {
  name: 'Error404'
}
</script>
