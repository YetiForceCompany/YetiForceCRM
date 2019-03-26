(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? factory(require('@quasar/extras/roboto-font/roboto-font.css'), require('@quasar/extras/mdi-v3/mdi-v3.css'), require('quasar-styl'), require('src/css/app.styl'), require('vue'), require('quasar/icon-set/mdi-v3'), require('quasar'), require('app/src/App.vue'), require('app/src/store/index.js'), require('app/src/router/index.js'), require('boot/i18n'), require('boot/axios')) :
  typeof define === 'function' && define.amd ? define(['@quasar/extras/roboto-font/roboto-font.css', '@quasar/extras/mdi-v3/mdi-v3.css', 'quasar-styl', 'src/css/app.styl', 'vue', 'quasar/icon-set/mdi-v3', 'quasar', 'app/src/App.vue', 'app/src/store/index.js', 'app/src/router/index.js', 'boot/i18n', 'boot/axios'], factory) :
  (global = global || self, factory(null, null, null, null, global.Vue, global.iconSet, global.quasar, global.App, global.createStore, global.createRouter, global.b_I18n, global.b_Axios));
}(this, function (robotoFont_css, mdiV3_css, quasarStyl, app_styl, Vue, iconSet, quasar, App, createStore, createRouter, b_I18n, b_Axios) { 'use strict';

  Vue = Vue && Vue.hasOwnProperty('default') ? Vue['default'] : Vue;
  iconSet = iconSet && iconSet.hasOwnProperty('default') ? iconSet['default'] : iconSet;
  App = App && App.hasOwnProperty('default') ? App['default'] : App;
  createStore = createStore && createStore.hasOwnProperty('default') ? createStore['default'] : createStore;
  createRouter = createRouter && createRouter.hasOwnProperty('default') ? createRouter['default'] : createRouter;
  b_I18n = b_I18n && b_I18n.hasOwnProperty('default') ? b_I18n['default'] : b_I18n;
  b_Axios = b_Axios && b_Axios.hasOwnProperty('default') ? b_Axios['default'] : b_Axios;

  /**
   * THIS FILE IS GENERATED AUTOMATICALLY.
   * DO NOT EDIT.
   *
   * You are probably looking on adding startup/initialization code.
   * Use "quasar new boot <name>" and add it there.
   * One boot file per concern. Then reference the file(s) in quasar.conf.js > boot:
   * boot: ['file', ...] // do not add ".js" extension to it.
   *
   * Boot files are your "main.js"
   **/


  Vue.use(quasar.Quasar, { config: {"notify":{}},iconSet: iconSet,components: {QLayout: quasar.QLayout,QHeader: quasar.QHeader,QDrawer: quasar.QDrawer,QPageContainer: quasar.QPageContainer,QPage: quasar.QPage,QToolbar: quasar.QToolbar,QToolbarTitle: quasar.QToolbarTitle,QBtn: quasar.QBtn,QIcon: quasar.QIcon,QList: quasar.QList,QItem: quasar.QItem,QItemSection: quasar.QItemSection,QItemLabel: quasar.QItemLabel,QInput: quasar.QInput,QSelect: quasar.QSelect,QFooter: quasar.QFooter,QAvatar: quasar.QAvatar,QSeparator: quasar.QSeparator,QBreadcrumbs: quasar.QBreadcrumbs,QBreadcrumbsEl: quasar.QBreadcrumbsEl,QExpansionItem: quasar.QExpansionItem,QTable: quasar.QTable,QToggle: quasar.QToggle},plugins: {Notify: quasar.Notify} });

  /**
   * THIS FILE IS GENERATED AUTOMATICALLY.
   * DO NOT EDIT.
   *
   * You are probably looking on adding startup/initialization code.
   * Use "quasar new boot <name>" and add it there.
   * One boot file per concern. Then reference the file(s) in quasar.conf.js > boot:
   * boot: ['file', ...] // do not add ".js" extension to it.
   *
   * Boot files are your "main.js"
   **/

  function createApp () {
    // create store and router instances
    
    const store = typeof createStore === 'function'
      ? createStore()
      : createStore;
    
    const router = typeof createRouter === 'function'
      ? createRouter({store})
      : createRouter;
    
    // make router instance available in store
    store.$router = router;
    

    // Create the app instantiation Object.
    // Here we inject the router, store to all child components,
    // making them available everywhere as `this.$router` and `this.$store`.
    const app = {
      el: '#q-app',
      router,
      store,
      render: h => h(App)
    };

    

    // expose the app, the router and the store.
    // note we are not mounting the app here, since bootstrapping will be
    // different depending on whether we are in a browser or on the server.
    return {
      app,
      store,
      router
    }
  }

  /**
   * THIS FILE IS GENERATED AUTOMATICALLY.
   * DO NOT EDIT.
   *
   * You are probably looking on adding startup/initialization code.
   * Use "quasar new boot <name>" and add it there.
   * One boot file per concern. Then reference the file(s) in quasar.conf.js > boot:
   * boot: ['file', ...] // do not add ".js" extension to it.
   *
   * Boot files are your "main.js"
   **/












  const { app, store, router } = createApp();



  async function start () {
    
    const bootFiles = [b_I18n,b_Axios];
    for (let i = 0; i < bootFiles.length; i++) {
      try {
        await bootFiles[i]({
          app,
          router,
          store,
          Vue,
          ssrContext: null
        });
      }
      catch (err) {
        if (err && err.url) {
          window.location.href = err.url;
          return
        }

        console.error('[Quasar] boot error:', err);
        return
      }
    }
    

    

      

      

        new Vue(app);

      

    

  }

  start();

}));
