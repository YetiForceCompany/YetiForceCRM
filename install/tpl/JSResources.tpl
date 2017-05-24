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
	<script type="text/javascript" src="../public_html/libraries/jquery/jquery.blockUI.js"></script>
	<script type="text/javascript" src="../public_html/libraries/jquery/chosen/chosen.jquery.min.js"></script>
	<script type="text/javascript" src="../public_html/libraries/jquery/select2/select2.min.js"></script>
	<script type="text/javascript" src="../public_html/libraries/jquery/jquery-ui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../public_html/libraries/jquery/jquery.class.min.js"></script>
	<script type="text/javascript" src="../public_html/libraries/jquery/defunkt-jquery-pjax/jquery.pjax.js"></script>
	<script type="text/javascript" src="../public_html/libraries/jquery/jstorage.min.js"></script>
	<script type="text/javascript" src="../public_html/libraries/jquery/autosize/jquery.autosize-min.js"></script>
	<script type="text/javascript" src="../public_html/libraries/jquery/rochal-jQuery-slimScroll/jquery.slimscroll.min.js"></script>
	<script type="text/javascript" src="../public_html/libraries/jquery/pnotify/pnotify.custom.min.js"></script>
	<script type="text/javascript" src="../public_html/libraries/jquery/jquery.hoverIntent.minified.js"></script>
	<script type="text/javascript" src="../public_html/libraries/jquery/posabsolute-jQuery-Validation-Engine/js/jquery.validationEngine.js"></script>
	<script type="text/javascript" src="../public_html/libraries/jquery/posabsolute-jQuery-Validation-Engine/js/languages/jquery.validationEngine-{$HTMLLANG}.js"></script>
	<script type="text/javascript" src="../public_html/libraries/bootstrap3/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="../public_html/libraries/bootstrap3/js/bootstrap-switch.js"></script>
	<script type="text/javascript" src="../public_html/libraries/bootstrap3/js/bootbox.min.js"></script>
	<script type="text/javascript" src="../public_html/libraries/jquery/selectize/js/selectize.js"></script>
	<script type="text/javascript" src="../public_html/layouts/resources/jquery.additions.js"></script>
	<script type="text/javascript" src="../public_html/libraries/jquery/datepicker/js/datepicker.js"></script>
	<script type="text/javascript" src="../public_html/libraries/jquery/dangrossman-bootstrap-daterangepicker/date.js"></script>
	<script type="text/javascript" src="../public_html/libraries/jquery/jquery.ba-outside-events.min.js"></script>
	<script type="text/javascript" src="../public_html/libraries/jquery/jquery.placeholder.js"></script>
	<script type="text/javascript" src="../public_html/layouts/resources/app.js"></script>
	<script type="text/javascript" src="../public_html/layouts/resources/helper.js"></script>
	<script type="text/javascript" src="../public_html/layouts/resources/Connector.js"></script>
	<script type="text/javascript" src="../public_html/layouts/resources/ProgressIndicator.js" ></script>
	<script type="text/javascript" src="../install/tpl/resources/Index.js"></script>
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}?&v={$YETIFORCE_VERSION}"></script>
	{/foreach}
	<!-- Added in the end since it should be after less file loaded -->
	<script type="text/javascript" src="../public_html/libraries/bootstrap/js/less.min.js"></script>
{/strip}
