<?php
/**
 * Index file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
?>
<!DOCTYPE html>
<html>
  <head>
    <title>YetiForce CRM</title>

    <meta charset="utf-8" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="msapplication-tap-highlight" content="no" />
    <meta
      name="viewport"
      content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width"
    />
    <link href="/node_modules/@mdi/font/css/materialdesignicons.min.css" rel="stylesheet" />
    <link href="/node_modules/animate.css/animate.min.css" rel="stylesheet" />
    <link href="/node_modules/quasar/dist/quasar.min.css" rel="stylesheet" type="text/css" />
  </head>

  <body>
    <div id="app"></div>

    <script>window.env = <?php echo $webUI->getEnv(); ?>;</script>
    <script src="/node_modules/quasar/dist/quasar.ie.polyfills.umd.min.js"></script>
    <script src="/node_modules/vue/dist/vue.js"></script>
    <script src="/node_modules/vuex/dist/vuex.js"></script>
    <script src="/node_modules/vue-router/dist/vue-router.js"></script>
    <script src="/node_modules/quasar/dist/quasar.umd.min.js"></script>
    <script src="/node_modules/quasar/dist/icon-set/mdi-v3.umd.min.js"></script>
    <script src="/node_modules/axios/dist/axios.js"></script>
    <script src="/node_modules/vue-i18n/dist/vue-i18n.js"></script>
    <script src="/src/statics/modules.js"></script>
    <script src="/src/main.js" type="module"></script>
  </body>
</html>
