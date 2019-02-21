<template>
  <q-layout view="lHh Lpr lFf">
    <q-header v-if="this.$store.state.login.isLoggedIn">
      <q-toolbar>
        <q-btn
                flat
                dense
                round
                @click="leftDrawerOpen = !leftDrawerOpen"
                aria-label="Menu"
        >
          <q-icon name="menu"/>
        </q-btn>

        <q-toolbar-title>
          YetiForceCRM
        </q-toolbar-title>

      </q-toolbar>
    </q-header>

    <q-drawer
            v-model="leftDrawerOpen"
            v-if="this.$store.state.login.isLoggedIn"
            bordered
            content-class="bg-grey-2"
    >
      <q-list>
        <q-item-label header>YetiForceCRM</q-item-label>
        <q-item clickable tag="a" target="_blank" href="https://yetiforce.com">
          <q-item-section avatar>
            <q-icon name="star"/>
          </q-item-section>
          <q-item-section>
            <q-item-label>YetiForce</q-item-label>
            <q-item-label caption>YetiForce Home Page</q-item-label>
          </q-item-section>
        </q-item>
        <q-item clickable v-ripple @click="$router.push('/module/Module')">
          <q-item-section avatar>
            <q-icon name="star"/>
          </q-item-section>
          <q-item-section>module</q-item-section>
        </q-item>
      </q-list>
    </q-drawer>
    <q-page-container>
      <router-view/>
    </q-page-container>
  </q-layout>
</template>

<script>
  import {openURL} from 'quasar'

  export default {
    name: 'MyLayout',
    data() {
      return {
        leftDrawerOpen: this.$q.platform.is.desktop
      }
    },
    methods: {
      openURL
    }
  }
</script>

<style>
</style>
