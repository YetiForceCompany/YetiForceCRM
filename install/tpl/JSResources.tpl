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
{* <script> resources below *}
	<script type="text/javascript" src="../libraries/jquery/jquery.blockUI.js"></script>
	<script type="text/javascript" src="../libraries/jquery/chosen/chosen.jquery.min.js"></script>
	<script type="text/javascript" src="../libraries/jquery/select2/select2.min.js"></script>
	<script type="text/javascript" src="../libraries/jquery/jquery-ui/js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../libraries/jquery/jquery.class.min.js"></script>
	<script type="text/javascript" src="../libraries/jquery/defunkt-jquery-pjax/jquery.pjax.js"></script>
	<script type="text/javascript" src="../libraries/jquery/jstorage.min.js"></script>
	<script type="text/javascript" src="../libraries/jquery/autosize/jquery.autosize-min.js"></script>
	<script type="text/javascript" src="../libraries/jquery/rochal-jQuery-slimScroll/jquery.slimscroll.min.js"></script>
	<script type="text/javascript" src="../libraries/jquery/pnotify/jquery.pnotify.min.js"></script>
	<script type="text/javascript" src="../libraries/jquery/jquery.hoverIntent.minified.js"></script>
	<script type="text/javascript" src="../libraries/bootstrap/js/bootstrap-alert.js"></script>
	<script type="text/javascript" src="../libraries/bootstrap/js/bootstrap-tooltip.js"></script>
	<script type="text/javascript" src="../libraries/bootstrap/js/bootstrap-tab.js"></script>
	<script type="text/javascript" src="../libraries/bootstrap/js/bootstrap-collapse.js"></script>
	<script type="text/javascript" src="../libraries/bootstrap/js/bootstrap-modal.js"></script>
	<script type="text/javascript" src="../libraries/bootstrap/js/bootstrap-dropdown.js"></script>
	<script type="text/javascript" src="../libraries/bootstrap/js/bootstrap-popover.js"></script>
	<script type="text/javascript" src="../libraries/bootstrap/js/bootstrap-switch.min.js"></script>
	<script type="text/javascript" src="../libraries/bootstrap/js/bootbox.min.js"></script>
	<script type="text/javascript" src="../layouts/vlayout/resources/jquery.additions.js"></script>
	<script type="text/javascript" src="../layouts/vlayout/resources/app.js"></script>
	<script type="text/javascript" src="../layouts/vlayout/resources/helper.js"></script>
	<script type="text/javascript" src="../layouts/vlayout/resources/Connector.js"></script>
	<script type="text/javascript" src="../layouts/vlayout/resources/ProgressIndicator.js" ></script>
	<script type="text/javascript" src="../libraries/guidersjs/guiders-1.2.6.js"></script>
	<script type="text/javascript" src="../libraries/jquery/datepicker/js/datepicker.js"></script>
	<script type="text/javascript" src="../libraries/jquery/dangrossman-bootstrap-daterangepicker/date.js"></script>
	<script type="text/javascript" src="../libraries/jquery/jquery.ba-outside-events.min.js"></script>
	<script type="text/javascript" src="../libraries/jquery/jquery.placeholder.js"></script>
        <script type="text/javascript" src="../install/tpl/resources/Index.js"></script>

	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}?&v={$YETIFORCE_VERSION}"></script>
	{/foreach}
	<!-- Added in the end since it should be after less file loaded -->
	<script type="text/javascript" src="../libraries/bootstrap/js/less.min.js"></script>
