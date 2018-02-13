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
	<div class="modelContainer modal fade" tabinedx="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button class="close vtButton" data-dismiss="modal">×</button>
					<h3 class="modal-title">{\App\Language::translate('LBL_DELETE_RECORD', $QUALIFIED_MODULE)} {\App\Language::translate('SINGLE_'|cat:$MODULE, $QUALIFIED_MODULE)} - {$RECORD_MODEL->getName()}</h3>
				</div>
				<form class="form-horizontal" id="DeleteModal" name="AddComment" method="post" action="index.php">
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="parent" value="Settings" />
					<input type="hidden" name="action" value="DeleteAjax" />
					<input type="hidden" name="record" id="record" value="{$RECORD_MODEL->getId()}" />
					<div class="modal-body tabbable">
						<div class="form-group ">
							<div class="col-md-4">
								<strong>
									{\App\Language::translate('LBL_TRANSFORM_OWNERSHIP', $QUALIFIED_MODULE)} {\App\Language::translate('LBL_TO', $QUALIFIED_MODULE)}<span class="redColor">*</span>
								</strong>
							</div>
							<div class="controls col-md-8">
								<select id="transfer_record" name="transfer_record" class="chzn-select form-control">
									<optgroup label="{\App\Language::translate('LBL_USERS', $QUALIFIED_MODULE)}">
										{foreach from=$ALL_USERS key=USER_ID item=USER_MODEL}
											<option value="{$USER_ID}">{$USER_MODEL->getName()}</option>
										{/foreach}
									</optgroup>
									<optgroup label="{\App\Language::translate('LBL_GROUPS', $QUALIFIED_MODULE)}">
										{foreach from=$ALL_GROUPS key=GROUP_ID item=GROUP_MODEL}
											{if $RECORD_MODEL->getId() != $GROUP_ID }
												<option value="{$GROUP_ID}">{$GROUP_MODEL->getName()}</option>
											{/if}
										{/foreach}
									</optgroup>
								</select>
							</div>
						</div>
					</div>

					<div class="modal-footer">
						<div class="float-right cancelLinkContainer"><a class="cancelLink btn btn-warning" type="reset" data-dismiss="modal">{\App\Language::translate('LBL_CANCEL', $MODULE)}</a></div>
						<button class="btn btn-success" type="submit">{\App\Language::translate('LBL_SAVE', $MODULE)}</button>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}
