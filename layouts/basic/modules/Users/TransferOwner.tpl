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
	<div id="massEditContainer" class='modelContainer'>
		<div class="modal-header">
			<h5 class="modal-title" id="massEditHeader">{\App\Language::translate('Transfer Ownership to User', $MODULE)}</h5>
			<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<form class="form-horizontal" id="deleteUser" name="transferOwner" method="post" action="index.php">
			<input type="hidden" name="module" value="{$MODULE}" />
			<input type="hidden" name="userid" value="{$USERID}" />
			<div name='massEditContent'>
				<div class="modal-body tabbable">
					<div class="tab-content massEditContent">
						<table class="massEditTable table table-bordered">
							<tr>
								<td class="fieldLabel alignMiddle">{\App\Language::translate('Transfer Ownership to User', $MODULE)}</td>
								<td class="fieldValue">
									<select class="select2 form-control" name="tranfer_owner_id" data-validation-engine="validate[ required]" >
										{foreach item=USER_MODEL key=USER_ID from=$USER_LIST}
											<option value="{$USER_ID}" >{\App\Purifier::encodeHtml($USER_MODEL->getName())}</option>
										{/foreach}
									</select>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
		</form>
	</div>
{/strip}
