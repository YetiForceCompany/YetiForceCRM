{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!DOCTYPE html>
	<html lang="{$HTMLLANG}" class="o-view-{$VIEW|lower}">

	<head>
		<title>{$PAGETITLE}</title>
		<link REL="SHORTCUT ICON" HREF="{\App\Layout::getImagePath('favicon.ico')}">
		{if !empty($IS_IE)}
			<meta http-equiv="x-ua-compatible" content="IE=11,edge">
		{/if}
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="robots" content="noindex,nofollow" />
		{foreach key=index item=cssModel from=$STYLES}
			<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" />
		{/foreach}
		{foreach key=index item=jsModel from=$HEADER_SCRIPTS}
			<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
		{/foreach}
		{assign var="HEAD_LOCKS" value=$USER_MODEL->getHeadLocks()}
		{if $HEAD_LOCKS}
			<script type="text/javascript" {if $NONCE}nonce="{$NONCE}" {/if}>
				{$HEAD_LOCKS}
			</script>
		{/if}
		<script type="text/javascript" {if $NONCE}nonce="{$NONCE}" {/if}>
			var CONFIG = {\App\Config::getJsEnv()};
			var LANG = {\App\Json::encode($LANGUAGE_STRINGS)};
		</script>
		{if \App\Debuger::isDebugBar()}
			{\App\Debuger::getDebugBar()->loadScripts()}
		{/if}
	</head>

	<body class="{if !empty($SHOW_FOOTER_BAR)} {if App\Config::module('Users', 'IS_VISIBLE_USER_INFO_FOOTER')}user-info--active {/if} {else} footer-non--active {/if} {if \App\YetiForce\Shop::check('YetiForceDisableBranding')}limited-footer--active{/if}" data-language="{$LANGUAGE}" data-module="{$MODULE_NAME}" data-view="{$VIEW}" data-skinpath="{$SKIN_PATH}" data-layoutpath="{$LAYOUT_PATH}" {$USER_MODEL->getBodyLocks()}>
		<div id="configuration">
			<input type="hidden" id="currencyGroupingPattern" value="{$USER_MODEL->get('currency_grouping_pattern')}" />
			<input type="hidden" id="truncateTrailingZeros" value="{$USER_MODEL->get('truncate_trailing_zeros')}" />
			<input type="hidden" id="gsAutocomplete" value="{App\Config::search('GLOBAL_SEARCH_AUTOCOMPLETE')}" />
			<input type="hidden" id="gsMinLength" value="{App\Config::search('GLOBAL_SEARCH_AUTOCOMPLETE_MIN_LENGTH')}" />
			<input type="hidden" id="gsAmountResponse" value="{App\Config::search('GLOBAL_SEARCH_AUTOCOMPLETE_LIMIT')}" />
			<input type="hidden" id="module" value="{$MODULE_NAME}" />
			<input type="hidden" id="parent" value="{$PARENT_MODULE}" />
			<input type="hidden" id="view" value="{$VIEW}" />
			<input type="hidden" id="sounds" value="{\App\Purifier::encodeHtml(\App\Json::encode(App\Config::sounds()))}" />
			<input type="hidden" id="intervalForNotificationNumberCheck" value="{App\Config::performance('INTERVAL_FOR_NOTIFICATION_NUMBER_CHECK')}" />
		</div>
		<div id="page">
			{if $SHOW_BODY_HEADER}
				{include file=\App\Layout::getTemplatePath('Body.tpl', $MODULE_NAME)}
			{/if}
{/strip}
