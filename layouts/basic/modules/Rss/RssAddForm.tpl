{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<div class="modal-header">
		<h5 class="modal-title">
			<span class="fas fa-fw fa-rss mr-1"></span>
			{\App\Language::translate('LBL_ADD_FEED_SOURCE', $MODULE)}
		</h5>
		<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<form class="form-horizontal" id="rssAddForm" method="post" action="index.php">
		<div class="modal-body tabbable">
			<div class="form-group row">
				<div class="col-form-label col-md-4 text-right">
					<span class="redColor">*</span>&nbsp;{\App\Language::translate('LBL_FEED_SOURCE',$MODULE)}
				</div>
				<div class="controls col-md-8">
					<input class="form-control" type="text" id="feedurl" name="feedurl" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-validator='[ { "name":"Url" } ]' title="{\App\Language::translate('LBL_FEED_SOURCE',$MODULE)}" placeholder="{\App\Language::translate('LBL_ENTER_FEED_SOURCE',$MODULE)}" />
				</div>
			</div>
		</div>
		{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
	</form>
{/strip}
