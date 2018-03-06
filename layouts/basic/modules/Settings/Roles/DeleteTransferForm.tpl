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
	<div class="modelContainer modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h3 class="modal-title">{\App\Language::translate('LBL_DELETE_ROLE', $QUALIFIED_MODULE)} - {\App\Language::translate($RECORD_MODEL->getName(), $QUALIFIED_MODULE)}</h3>
					<button class="btn btn-warning float-right" data-dismiss="modal">&times;</button>
				</div>
				<form class="form-horizontal" id="roleDeleteForm" method="post" action="index.php">
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="parent" value="Settings" />
					<input type="hidden" name="action" value="Delete" />
					<input type="hidden" name="record" id="record" value="{$RECORD_MODEL->getId()}" />
					<div class="modal-body">
						<h5>{\App\Language::translate('LBL_TRANSFER_OWNERSHIP',$QUALIFIED_MODULE)}</h5>
						<div class="form-group row">
							<div class="col-md-3"><span class="redColor">*</span>{\App\Language::translate('LBL_TO_OTHER_ROLE',$QUALIFIED_MODULE)}</div>
							<div class="controls col-md-9">
								<input id="transfer_record" name="transfer_record" type="hidden" value="" class="sourceField">
								<div class="input-group">
									<div class="input-group-prepend cursorPointer" id="clearRole">
										<span class="input-group-text">
											<span class="fas fa-times-circle"></span>
										</span>
									</div>
									<input id="transfer_record_display" data-validation-engine='validate[required]' name="transfer_record_display" readonly type="text" class="input-medium form-control" required value="">
									<div class="input-group-append">
										<button class="btn btn-outline-secondary cursorPointer relatedPopup" data-field="transfer_record" data-action="popup" data-url="{$RECORD_MODEL->getPopupWindowUrl()}&type=Transfer">
											<span class="fas fa-search"></span>
										</button>
									</div>

								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<div class="float-right cancelLinkContainer">
							<a class="cancelLink btn btn-warning" data-dismiss="modal" type="reset">{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
						</div>
						<button class="btn btn-success float-right" type="submit">{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}
