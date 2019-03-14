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
$lang = \App\Language::getLanguage();
$env = App\Json::encode([
	'Env' => [
		'baseURL' => \App\Config::main('site_URL'),
		'publicDir' => '/dist',
		'routerMode' => 'hash',
	],
	'Users' => ['isLoggedIn' => true],
	'Language' => [
		'lang' => $lang,
		'translations' => \App\Language::getLanguageData($lang),
	],
]);

?>
<!DOCTYPE html>
<html>

<head>
  <title><%= htmlWebpackPlugin.options.productName %></title>

  <meta charset="utf-8">
  <meta name="format-detection" content="telephone=no">
  <meta name="msapplication-tap-highlight" content="no">
  <meta name="viewport"
    content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width<% if (htmlWebpackPlugin.options.ctx.mode.cordova) { %>, viewport-fit=cover<% } %>">

  <link rel="icon" type="image/png" sizes="32x32" href="/dist/statics/icons/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/dist/statics/icons/favicon-16x16.png">
  <script>window.env = <?php echo $env; ?>;</script>
  <script src="<%= htmlWebpackPlugin.options.modulesFile %>"></script>
</head>

<body>
  <!-- DO NOT touch the following DIV -->
  <div id="q-app"></div>
</body>

</html>
