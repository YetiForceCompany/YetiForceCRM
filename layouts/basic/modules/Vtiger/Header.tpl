{strip}
{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
<!DOCTYPE html>
<html lang="{$HTMLLANG}">
<head>
	<title>{$PAGETITLE}</title>
	<link REL="SHORTCUT ICON" HREF="{\App\Layout::getImagePath('favicon.ico')}">
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="robots" content="noindex,nofollow"/>
	{foreach key=index item=cssModel from=$STYLES}
		<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}"/>
	{/foreach}
	{foreach key=index item=jsModel from=$HEADER_SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
	<!--[if IE]>
	<script type="text/javascript" src="public_html/libraries/html5shiv/html5shiv.js"></script>
	<script type="text/javascript" src="public_html/libraries/respond.js/dist/respond.min.js"></script>
	<![endif]-->
	{* ends *}
	{* ADD <script> INCLUDES in JSResources.tpl - for better performance *}
	{assign var="HEAD_LOCKS" value=$USER_MODEL->getHeadLocks()}
	{if $HEAD_LOCKS}
		<script type="text/javascript">{$HEAD_LOCKS}</script>
	{/if}
	<script type="text/javascript">
		var CONFIG = {\App\Config::getJsEnv()};
		var LANG = {\App\Json::encode($LANGUAGE_STRINGS)};
	</script>
	{if \App\Debuger::isDebugBar()}
		{\App\Debuger::getDebugBar()->getJavascriptRenderer(\App\Debuger::getJavascriptPath())->renderHead()}
	{/if}
</head>
<body class="{if AppConfig::module('Users', 'IS_VISIBLE_USER_INFO_FOOTER')}user-info--active{/if}{if AppConfig::performance('LIMITED_INFO_IN_FOOTER')} limited-footer--active{/if}" data-language="{$LANGUAGE}" data-skinpath="{$SKIN_PATH}" data-layoutpath="{$LAYOUT_PATH}" {$USER_MODEL->getBodyLocks()}>
<div id="configuration">
	<input type="hidden" id="currencyGroupingPattern" value="{$USER_MODEL->get('currency_grouping_pattern')}"/>
	<input type="hidden" id="truncateTrailingZeros" value="{$USER_MODEL->get('truncate_trailing_zeros')}"/>
	<input type="hidden" id="backgroundClosingModal" value="{\AppConfig::main('backgroundClosingModal')}"/>
	<input type="hidden" id="gsAutocomplete" value="{AppConfig::search('GLOBAL_SEARCH_AUTOCOMPLETE')}"/>
	<input type="hidden" id="gsMinLength" value="{AppConfig::search('GLOBAL_SEARCH_AUTOCOMPLETE_MIN_LENGTH')}"/>
	<input type="hidden" id="gsAmountResponse" value="{AppConfig::search('GLOBAL_SEARCH_AUTOCOMPLETE_LIMIT')}"/>
	<input type="hidden" id="module" value="{$MODULE}"/>
	<input type="hidden" id="parent" value="{$PARENT_MODULE}"/>
	<input type="hidden" id="view" value="{$VIEW}"/>
	<input type="hidden" id="sounds" value="{\App\Purifier::encodeHtml(\App\Json::encode(AppConfig::sounds()))}"/>
	<input type="hidden" id="intervalForNotificationNumberCheck" value="{AppConfig::performance('INTERVAL_FOR_NOTIFICATION_NUMBER_CHECK')}"/>
</div>
<div id="page">
	{if $SHOW_BODY_HEADER}
		{include file=\App\Layout::getTemplatePath('Body.tpl', $MODULE)}
	{/if}
	{/strip}
