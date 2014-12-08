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
<div class="modelContainer">
	<div class="modal-header">
		<button class="close vtButton" data-dismiss="modal">×</button>
		<h3>{vtranslate('LBL_DELETE_ROLE', $QUALIFIED_MODULE)} - {$RECORD_MODEL->getName()}</h3>
	</div>
	<form class="form-horizontal" id="roleDeleteForm" method="post" action="index.php">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="parent" value="Settings" />
		<input type="hidden" name="action" value="Delete" />
		<input type="hidden" name="record" id="record" value="{$RECORD_MODEL->getId()}" />

		<div class="modal-body">
			<h5>{vtranslate('LBL_TRANSFER_OWNERSHIP',$QUALIFIED_MODULE)}</h5>
			<div class="control-group">
				<div class="control-label"><span class="redColor">*</span>{vtranslate('LBL_TO_OTHER_ROLE',$QUALIFIED_MODULE)}</div>
				<div class="controls">
					<input id="transfer_record" name="transfer_record" type="hidden" value="" class="sourceField">
					<div class="input-prepend input-append">
						<span class="add-on cursorPointer" id="clearRole">
							<i class="icon-remove-sign"></i>
						</span>
						<input id="transfer_record_display" data-validation-engine='validate[required]' name="transfer_record_display" readonly type="text" class="input-medium" required value="">
						<span class="add-on cursorPointer relatedPopup" data-field="transfer_record" data-action="popup" data-url="{$RECORD_MODEL->getPopupWindowUrl()}&type=Transfer">
							<i class="icon-search"></i>
						</span>
					</div>
				</div>
			</div>
		</div>

			<div class="modal-footer">
				<div class=" pull-right cancelLinkContainer">
					<a class="cancelLink" data-dismiss="modal" type="reset">Cancel</a>
				</div>
				<button class="btn btn-success pull-right" type="submit">{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
			</div>
	</form>
</div>
{/strip}