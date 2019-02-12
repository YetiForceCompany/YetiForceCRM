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
						{\App\Language::translate('LBL_DELETE_PROFILE', $QUALIFIED_MODULE)} - {$RECORD_MODEL->getName()}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form class="form-horizontal" id="DeleteModal" name="AddComment" method="post" action="index.php">
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="parent" value="Settings" />
					<input type="hidden" name="action" value="Delete" />
					<input type="hidden" name="record" id="record" value="{$RECORD_MODEL->getId()}" />

					<div class="modal-body">
						<div class="form-group row">
							<div class="col-md-6 col-sm-6">{\App\Language::translate('LBL_TRANSFER_ROLES_TO_PROFILE',$QUALIFIED_MODULE)}</div>
							<div class="col-md-6 col-sm-6">
								<select id="transfer_record form-control" name="transfer_record" class="select2 form-control">
									<optgroup label="{\App\Language::translate('LBL_PROFILES', $QUALIFIED_MODULE)}">
										{foreach from=$ALL_RECORDS item=PROFILE_MODEL}
											{assign var=PROFILE_ID value=$PROFILE_MODEL->get('profileid')}
											{if $PROFILE_ID neq $RECORD_MODEL->getId()}
												<option value="{$PROFILE_ID}">{\App\Language::translate($PROFILE_MODEL->get('profilename'), $QUALIFIED_MODULE)}</option>
											{/if}
										{/foreach}
									</optgroup>
								</select>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-success" type="submit"><span class="fas fa-check mr-1"></span>{\App\Language::translate('LBL_SAVE', $MODULE)}</button>
						<button class="cancelLink btn btn-danger" data-dismiss="modal" type="reset"><span class="fas fa-times mr-1"></span>{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
					</div>
				</form>
			</div>				
		</div>				
	</div>				
{/strip}
