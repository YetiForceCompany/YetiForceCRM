<template>
  <q-layout view="hHh Lpr lff">
    <q-header>
      <q-toolbar>
        <q-btn flat dense round @click="leftDrawerOpen = !leftDrawerOpen" aria-label="Menu">
          <q-icon name="menu"/>
        </q-btn>

        <q-toolbar-title>YetiForceCRM Settings</q-toolbar-title>

        <div>Login / Logout</div>
      </q-toolbar>
    </q-header>

    <q-drawer v-model="leftDrawerOpen" bordered content-class="bg-grey-2">
      <q-list bordered>
        <q-item clickable v-ripple @click="$router.push('/')">
          <q-item-section avatar>
            <q-icon name="home"/>
          </q-item-section>
          <q-item-section>{{$t('LBL_HOME')}}</q-item-section>
        </q-item>

        <q-expansion-item
          icon="security"
          group="settings"
          :label="$t('LBL_PERMISSIONS')"
          :content-inset-level="0.5"
        >
          <q-list>
            <q-item clickable v-ripple @click="$router.push('/users')">
              <q-item-section avatar>
                <q-icon name="group"/>
              </q-item-section>
              <q-item-section>{{$t('LBL_USERS')}}</q-item-section>
            </q-item>
            <q-item clickable v-ripple @click="$router.push('/roles')">
              <q-item-section avatar>
                <q-icon name="assignment_ind"/>
              </q-item-section>
              <q-item-section>{{$t('LBL_ROLES')}}</q-item-section>
            </q-item>
            <q-item clickable v-ripple @click="$router.push('/profiles')">
              <q-item-section avatar>
                <q-icon name="account_circle"/>
              </q-item-section>
              <q-item-section>{{$t('LBL_PROFILES')}}</q-item-section>
            </q-item>
            <q-item clickable v-ripple @click="$router.push('/groups')">
              <q-item-section avatar>
                <q-icon name="group_work"/>
              </q-item-section>
              <q-item-section>{{$t('LBL_GROUPS')}}</q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>

        <q-expansion-item
          group="settings"
          icon="extension"
          :label="$t('LBL_STANDARD_MODULES')"
          :content-inset-level="0.5"
        >
          <q-list>
            <q-item clickable v-ripple>
              <q-item-section avatar>
                <q-icon name="save_alt"/>
              </q-item-section>
              <q-item-section>{{$t('LBL_MODULES_INSTALATION')}}</q-item-section>
            </q-item>
            <q-item clickable v-ripple>
              <q-item-section avatar>
                <q-icon name="supervisor_account"/>
              </q-item-section>
              <q-item-section>{{$t('LBL_ROLES')}}</q-item-section>
            </q-item>
            <q-item clickable v-ripple>
              <q-item-section avatar>
                <q-icon name="account_circle"/>
              </q-item-section>
              <q-item-section>{{$t('LBL_PROFILES')}}</q-item-section>
            </q-item>
            <q-item clickable v-ripple>
              <q-item-section avatar>
                <q-icon name="group_work"/>
              </q-item-section>
              <q-item-section>{{$t('LBL_GROUPS')}}</q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view/>
    </q-page-container>
  </q-layout>
</template>

<script>
import { openURL } from "quasar";

export default {
  name: "Basic",
  data() {
    return {
      leftDrawerOpen: this.$q.platform.is.desktop
    };
  },
  methods: {
    openURL
  }
};
</script>

<style>
</style>
