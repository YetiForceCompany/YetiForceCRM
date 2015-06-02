{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
-->*}
{strip}
<!DOCTYPE html>
<html lang="{$HTMLLANG}">
	<head>
		<title>
			{vtranslate($PAGETITLE, $MODULE_NAME)}
		</title>
		<link REL="SHORTCUT ICON" HREF="layouts/vlayout/skins/images/favicon.ico">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="robots" content="noindex">
		<link rel="stylesheet" href="libraries/jquery/chosen/chosen.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="libraries/jquery/jquery-ui/css/custom-theme/jquery-ui.min.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="libraries/jquery/select2/select2.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="libraries/bootstrap/css/bootstrap.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="layouts/vlayout/resources/styles.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="libraries/jquery/posabsolute-jQuery-Validation-Engine/css/validationEngine.jquery.css" />
		<link rel="stylesheet" href="libraries/jquery/select2/select2.css" />
		<link rel="stylesheet" href="libraries/guidersjs/guiders-1.2.6.css"/>
		<link rel="stylesheet" href="libraries/jquery/pnotify/jquery.pnotify.default.css"/>
		<link rel="stylesheet" href="libraries/jquery/pnotify/use for pines style icons/jquery.pnotify.default.icons.css"/>
		<link rel="stylesheet" media="screen" type="text/css" href="libraries/jquery/datepicker/css/datepicker.css" />
		<link rel="stylesheet" href="libraries/bootstrap/css/bootstrap-switch.min.css" type="text/css" media="screen" />
		{foreach key=index item=cssModel from=$STYLES}
                    <link rel="{$cssModel->getRel()}" href="{vresource_url($cssModel->getHref())}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
		{/foreach}

		{* For making pages - print friendly *}
		<style type="text/css">
		@media print {
		.noprint { display:none; }
		}
		</style>

		{* This is needed as in some of the tpl we are using jQuery.ready *}
		<script type="text/javascript" src="libraries/jquery/jquery.min.js"></script>
		<script type="text/javascript" src="libraries/jquery/jquery-migrate-1.2.1.js"></script>
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
		<input type="hidden" id="backgroundClosingModal" value="{vglobal('backgroundClosingModal')}" />
		<div id="page">
			<!-- container which holds data temporarly for pjax calls -->
			<div id="pjaxContainer" class="hide noprint"></div>
{/strip}
