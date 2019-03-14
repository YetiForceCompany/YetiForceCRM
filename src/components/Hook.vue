<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>This is a hook {{fullName}}</div>
</template>
<script>
export default {
  name: 'Hook',
  props: {
    name: { type: String, required: true },
    type: { type: String, required: false, default: 'hook', validator: value => ['hook', 'slot'].indexOf(value) !== -1 }
  },
  computed: {
    fullName() {
      return `${this.$parent.$options.name}.${this.name}`
    }
  }
}
</script>
