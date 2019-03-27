<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import ModuleLoader from '/src/ModuleLoader.js'
import moduleStore from './store/index.js'
import mutations from './store/mutations.js'
import getters from './store/getters.js'

const moduleName = 'Core.Debug'

export function initialize({ store, router }) {
  store.registerModule(moduleName.split('.'), ModuleLoader.prepareStoreNames(moduleName, moduleStore))
}

export default {
  name: moduleName,
  props: {
    levels: {
      type: Array,
      validate(value) {
        for (let item of value) {
          if (['all', 'log', 'info', 'notice', 'warning', 'error'].indexOf(item) !== -1) {
            return false
          }
        }
        return true
      }
    }
  },
  computed: {
    all(moduleName = '') {
      return this.$store.getters[getters.Core.Debug.get]('all', moduleName)
    },
    logs(moduleName = '') {
      return this.$store.getters[getters.Core.Debug.get]('log', moduleName)
    },
    infos(moduleName = '') {
      return this.$store.getters[getters.Core.Debug.get]('info', moduleName)
    },
    warnings(moduleName = '') {
      return this.$store.getters[getters.Core.Debug.get]('warning', moduleName)
    },
    notices(moduleName = '') {
      return this.$store.getters[getters.Core.Debug.get]('notice', moduleName)
    },
    errors(moduleName = '') {
      return this.$store.getters[getters.Core.Debug.get]('error', moduleName)
    }
  },
  methods: {
    log(message, data) {
      this.$store.commit(mutations.Core.Debug.push, { type: 'log', message, data })
      this.$root.$emit('debug.log', { message, data })
      if (this.levels.indexOf('log') !== -1) {
        console.log(message, data)
      }
    },
    info(message, data) {
      this.$store.commit(mutations.Core.Debug.push, { type: 'info', message, data })
      this.$root.$emit('debug.info', { message, data })
      if (this.levels.indexOf('info') !== -1) {
        console.info(message, data)
      }
    },
    notice(message, data) {
      this.$store.commit(mutations.Core.Debug.push, { type: 'notice', message, data })
      this.$root.$emit('debug.notice', { message, data })
      if (this.levels.indexOf('notice') !== -1) {
        console.log(`%c ${message}`, 'color: orange', data)
      }
    },
    warning(message, data) {
      this.$store.commit(mutations.Core.Debug.push, { type: 'warning', message, data })
      this.$root.$emit('debug.warning', { message, data })
      if (this.levels.indexOf('warning') !== -1) {
        console.warn(message, data)
      }
    },
    error(message, data) {
      this.$store.commit(mutations.Core.Debug.push, { type: 'error', message, data })
      this.$root.$emit('debug.error', { message, data })
      if (this.levels.indexOf('error') !== -1) {
        console.error(message, data)
      }
    }
  }
}
</script>
<style></style>
