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
	<div class="modal-header">
		<button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">&times;</button>
		<h3 class="modal-title">{vtranslate('LBL_ADD_FEED_SOURCE', $MODULE)}</h3>
	</div>
	<form class="form-horizontal" id="rssAddForm"  method="post" action="index.php" >
		<div class="modal-body tabbable">
			<div class="form-group">
				<div class="control-label col-md-4"><span class="redColor">*</span>&nbsp;{vtranslate('LBL_FEED_SOURCE',$MODULE)}</div>
				<div class="controls col-md-8">
					<input class="form-control" type="text" id="feedurl" name="feedurl" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-validator='[ { "name":"Url" } ]' title="{vtranslate('LBL_FEED_SOURCE',$MODULE)}" placeholder="{vtranslate('LBL_ENTER_FEED_SOURCE',$MODULE)}" />
				</div>
			</div>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
	</form>
{/strip}
