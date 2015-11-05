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
{strip}
<!DOCTYPE html>
<html lang="{$HTMLLANG}">
	<head>
		<title>{vtranslate($PAGETITLE, $QUALIFIED_MODULE)}</title>
		<link REL="SHORTCUT ICON" HREF="layouts/vlayout/skins/images/favicon.ico">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="robots" content="noindex" />
		
		{foreach key=index item=cssModel from=$STYLES}
			<link rel="{$cssModel->getRel()}" href="{vresource_url($cssModel->getHref())}" />
		{/foreach}
		{foreach key=index item=jsModel from=$HEADER_SCRIPTS}
				<script type="{$jsModel->getType()}" src="{vresource_url($jsModel->getSrc())}"></script>
		{/foreach}

		<!--[if IE]>
			<script type="text/javascript" src="libraries/html5shim/html5.js"></script>
			<script type="text/javascript" src="libraries/html5shim/respond.js"></script>
		<![endif]-->
		{* ends *}

		{* ADD <script> INCLUDES in JSResources.tpl - for better performance *}
	</head>
	<body data-skinpath="{$SKIN_PATH}" data-language="{$LANGUAGE}">
		<div id="js_strings" class="hide noprint">{Zend_Json::encode($LANGUAGE_STRINGS)}</div>
		{assign var=CURRENT_USER_MODEL value=Users_Record_Model::getCurrentUserModel()}
		<input type="hidden" id="start_day" value="{$CURRENT_USER_MODEL->get('dayoftheweek')}" />
		<input type="hidden" id="row_type" value="{$CURRENT_USER_MODEL->get('rowheight')}" />
		<input type="hidden" id="current_user_id" value="{$CURRENT_USER_MODEL->get('id')}" />
		<input type="hidden" id="userDateFormat" value="{$CURRENT_USER_MODEL->get('date_format')}" />
		<input type="hidden" id="userTimeFormat" value="{$CURRENT_USER_MODEL->get('hour_format')}" />
		<input type="hidden" id="numberOfCurrencyDecimal" value="{$CURRENT_USER_MODEL->get('no_of_currency_decimals')}" />
		<input type="hidden" id="currencyGroupingSeparator" value="{$CURRENT_USER_MODEL->get('currency_grouping_separator')}" />
		<input type="hidden" id="currencyDecimalSeparator" value="{$CURRENT_USER_MODEL->get('currency_decimal_separator')}" />
		<input type="hidden" id="currencyGroupingPattern" value="{$CURRENT_USER_MODEL->get('currency_grouping_pattern')}" />
		<input type="hidden" id="truncateTrailingZeros" value="{$CURRENT_USER_MODEL->get('truncate_trailing_zeros')}" />
		<input type="hidden" id="backgroundClosingModal" value="{vglobal('backgroundClosingModal')}" />
		<input type="hidden" id="gsAutocomplete" value="{vglobal('gsAutocomplete')}" />
		<input type="hidden" id="gsMinLength" value="{vglobal('gsMinLength')}" />
		<input type="hidden" id="gsAmountResponse" value="{vglobal('gsAmountResponse')}" />
		
		<div id="page">
			<!-- container which holds data temporarly for pjax calls -->
			<div id="pjaxContainer" class="hide noprint"></div>
{/strip}
