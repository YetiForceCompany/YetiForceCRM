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
					<h5 class="modal-title">
						<span class="fas fa-trash-alt mr-1"></span>
						{\App\Language::translate('LBL_DELETE_ROLE', $QUALIFIED_MODULE)} - {\App\Language::translate($RECORD_MODEL->getName(), $QUALIFIED_MODULE)}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form class="form-horizontal" id="roleDeleteForm" method="post" action="index.php">
					<input type="hidden" name="module" value="{$MODULE}"/>
					<input type="hidden" name="parent" value="Settings"/>
					<input type="hidden" name="action" value="Delete"/>
					<input type="hidden" name="record" id="record" value="{$RECORD_MODEL->getId()}"/>
					<div class="modal-body">
						<h5>{\App\Language::translate('LBL_TRANSFER_OWNERSHIP',$QUALIFIED_MODULE)}</h5>
						<div class="form-group row">
							<div class="col-md-3"><span
										class="redColor">*</span>{\App\Language::translate('LBL_TO_OTHER_ROLE',$QUALIFIED_MODULE)}
							</div>
							<div class="controls col-md-9">
								<select class="select2 form-control" id="transfer_record"
										name="transfer_record"
										type="text">
									{foreach from=$ALL_ROLES item=ROLE}
										<option value="{$ROLE->getId()}">{\App\Language::translate($ROLE->getName())}</option>
									{/foreach}
								</select>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-success" type="submit">
							<span class="fas fa-check mr-1"></span>
							{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
						</button>
						<button class="cancelLink btn btn-danger" data-dismiss="modal" type="reset">
							<span class="fas fa-times mr-1"></span>
							{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}
