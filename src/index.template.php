<?php
/**
 * Index file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
$langDir = 'languages'.DIRECTORY_SEPARATOR.\App\Language::getLanguage();
$langFiles = preg_grep("/^\.|\.\.|.*(?<!.json)$/", scandir($langDir), PREG_GREP_INVERT);
$translations = [];
foreach ($langFiles as $file) {
    $translations[rtrim($file, '.json')] = \App\Json::decode(file_get_contents($langDir.DIRECTORY_SEPARATOR.$file), true)['php'];
}
$env = [
    'baseURL' => \AppConfig::main('site_URL'),
    'publicDir' => '',
  'routerMode' => 'hash',
  'lang' => \App\Language::getLanguage(),
  'translations' => $translations,
];

?>
<!DOCTYPE html>
<html>

<head>
  <title><%= htmlWebpackPlugin.options.productName %></title>

  <meta charset="utf-8">
  <meta name="description" content="<%= htmlWebpackPlugin.options.productDescription %>">
  <meta name="format-detection" content="telephone=no">
  <meta name="msapplication-tap-highlight" content="no">
  <meta name="viewport"
    content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width<% if (htmlWebpackPlugin.options.ctx.mode.cordova) { %>, viewport-fit=cover<% } %>">

  <link rel="icon" href="/dist/statics/quasar-logo.png" type="image/x-icon">
  <link rel="icon" type="image/png" sizes="32x32" href="/dist/statics/icons/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/dist/statics/icons/favicon-16x16.png">
  <script>window.env = <?php echo json_encode($env); ?>;</script>
  <script src="<%= htmlWebpackPlugin.options.modulesFile %>"></script>
</head>

<body>
  <!-- DO NOT touch the following DIV -->
  <div id="q-app"></div>
</body>

</html>
