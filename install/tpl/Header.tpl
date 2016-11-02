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
	<html  lang="{$HTMLLANG}">
		<head>
			<title>YetiForce</title>
			<link REL="SHORTCUT ICON" HREF="../{vimage_path('favicon.ico')}">
			<meta name="viewport" content="width=device-width, initial-scale=1.0" />
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<link rel="stylesheet" href="../libraries/bootstrap3/css/bootstrap.min.css"/>
			<link rel="stylesheet" href="../libraries/jquery/chosen/chosen.min.css" />
			<link rel="stylesheet" href="../libraries/jquery/chosen/chosen.bootstrap.min.css" />
			<link rel="stylesheet" href="../libraries/jquery/jquery-ui/jquery-ui.min.css" />
			<link rel="stylesheet" href="../libraries/jquery/select2/select2.min.css" />
			<link rel="stylesheet" href="../libraries/jquery/select2/select2-bootstrap.min.css" />
			<link rel="stylesheet" href="../libraries/jquery/posabsolute-jQuery-Validation-Engine/css/validationEngine.jquery.min.css" />
			<link rel="stylesheet" href="../libraries/jquery/pnotify/pnotify.custom.min.css"/>
			<link rel="stylesheet" href="../libraries/jquery/datepicker/css/datepicker.min.css" />
			<link rel="stylesheet" href="../libraries/resources/styles.css" />
			<link rel="stylesheet" href="tpl/resources/css/style.css"/>
			<link rel="stylesheet" href="tpl/resources/css/mkCheckbox.css"/>
			{foreach key=index item=cssModel from=$STYLES}
				<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}?&v={$YETIFORCE_VERSION}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
			{/foreach}

			{* For making pages - print friendly *}
			<style type="text/css">
				@media print {
					.noprint { display:none; }
				}
			</style>

			{* This is needed as in some of the tpl we are using jQuery.ready *}
			<script type="text/javascript" src="../libraries/jquery/jquery.min.js"></script>
			<script type="text/javascript" src="../libraries/jquery/jquery-migrate.js"></script>
			<!--[if IE]>
			<script type="text/javascript" src="libraries/html5shim/html5shiv.min.js"></script>
			<script type="text/javascript" src="libraries/html5shim/respond.min.js"></script>
			<![endif]-->
			{* ends *}

			{* ADD <script> INCLUDES in JSResources.tpl - for better performance *}
		</head>

		<body data-skinpath="{$SKIN_PATH}" data-language="{$LANGUAGE}">
			<div id="js_strings" class="hide noprint">{\App\Json::encode($LANGUAGE_STRINGS)}</div>
			<input type="hidden" id="start_day" value="" />
			<input type="hidden" id="row_type"value="" />
			<input type="hidden" id="current_user_id" value="" />
			<div id="page">
				<!-- container which holds data temporarly for pjax calls -->
				<div id="pjaxContainer" class="hide noprint"></div>
			{/strip}
