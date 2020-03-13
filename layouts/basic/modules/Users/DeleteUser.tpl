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
	<div class="tpl-Users-DeleteUser modelContainer modal" id="massEditContainer" role="dialog" tabindex="-1">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 id="massEditHeader" class="modal-title">
						<span class="fas fa-exchange-alt fa-fw"></span>
						{\App\Language::translate('LBL_TRANSFER_RECORDS_TO_USER', $MODULE)} {$MODULE}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true" title="{\App\Language::translate('LBL_CLOSE')}">&times;</span>
					</button>
				</div>
				<form class="form-horizontal" id="deleteUser" name="deleteUser" method="post" action="index.php">
					<input type="hidden" name="module" value="{$MODULE}"/>
					<input type="hidden" name="userid" value="{$USERID}"/>
					<div name='massEditContent'>
						<div class="modal-body tabbable">
							<div class="tab-content massEditContent">
								<table class="massEditTable table table-bordered">
									<tr>
										<td class="fieldLabel align-middle">{\App\Language::translate('LBL_USER_TO_BE_DELETED', $MODULE)}</td>
										<td class="fieldValue">{$DELETE_USER_NAME}</td>
									</tr>
									<tr>
										<td class="fieldLabel align-middle">{\App\Language::translate('LBL_TRANSFER_RECORDS_TO_USER', $MODULE)}</td>
										<td class="fieldValue">
											<select class="select2 form-control" name="tranfer_owner_id"
													data-validation-engine="validate[ required]">
												{foreach item=USER_MODEL key=USER_ID from=$USER_LIST}
													<option value="{$USER_ID}">{\App\Purifier::encodeHtml($USER_MODEL->getName())}</option>
												{/foreach}
											</select>
										</td>
									</tr>
									{if !$PERMANENT}
										<tr>
											<td colspan="2">
												<div class="checkbox d-flex justify-content-center align-items-center">
													<input type="checkbox" class="checkbox mr-2" name="deleteUserPermanent" value="true">
													{\App\Language::translate('LBL_DELETE_USER_PERMANENTLY',$MODULE)}
												</div>
											</td>
										</tr>
									{/if}
								</table>
							</div>
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
{/strip}
