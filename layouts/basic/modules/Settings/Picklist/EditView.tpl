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
	<div class='modelContainer modal fade' tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">{\App\Language::translate('LBL_RENAME_PICKLIST_ITEM', $QUALIFIED_MODULE)}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form id="renameItemForm" class="form-horizontal" method="post" action="index.php">
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="parent" value="Settings" />
					<input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
					<input type="hidden" name="action" value="SaveAjax" />
					<input type="hidden" name="mode" value="rename" />
					<input type="hidden" name="picklistName" value="{$FIELD_MODEL->getName()}" />
					<input type="hidden" name="pickListValues" value='{\App\Purifier::encodeHtml(\App\Json::encode($SELECTED_PICKLISTFIELD_EDITABLE_VALUES))}' />
					<div class="modal-body tabbable">
						<div class="form-group row align-items-center">
							<div class="col-md-3 col-form-label text-right">{\App\Language::translate('LBL_ITEM_TO_RENAME',$QUALIFIED_MODULE)}</div>
							<div class="col-md-9 controls">
								{assign var=PICKLIST_VALUES value=$SELECTED_PICKLISTFIELD_EDITABLE_VALUES}
								<select class="chzn-select form-control" name="oldValue">
									<optgroup>
										{foreach from=$PICKLIST_VALUES key=PICKLIST_VALUE_KEY item=PICKLIST_VALUE}
											<option {if $FIELD_VALUE eq $PICKLIST_VALUE} selected="" {/if}value="{\App\Purifier::encodeHtml($PICKLIST_VALUE)}" data-id={$PICKLIST_VALUE_KEY}>{\App\Language::translate($PICKLIST_VALUE,$SOURCE_MODULE)}</option>
										{/foreach}	
									</optgroup>
								</select>	
							</div>
						</div>
						<div class="form-group row align-items-center">
							<div class="col-md-3 col-form-label text-right"><span class="redColor">*</span>{\App\Language::translate('LBL_ENTER_NEW_NAME',$QUALIFIED_MODULE)}</div>
							<div class="col-md-9 controls"><input type="text" class="form-control" data-validation-engine="validate[required, funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-validator={\App\Json::encode([['name'=>'FieldLabel']])} name="newValue"></div>
						</div>
						{if $SELECTED_PICKLISTFIELD_NON_EDITABLE_VALUES}
							<div class="form-group row align-items-center">
								<div class="col-md-3 col-form-label text-right">{\App\Language::translate('LBL_NON_EDITABLE_PICKLIST_VALUES',$QUALIFIED_MODULE)}</div>
								<div class="col-md-9 controls nonEditableValuesDiv">
									<ul class="nonEditablePicklistValues list-unstyled">
										{foreach from=$SELECTED_PICKLISTFIELD_NON_EDITABLE_VALUES key=NON_EDITABLE_VALUE_KEY item=NON_EDITABLE_VALUE}
											<li>{\App\Language::translate($NON_EDITABLE_VALUE,$SOURCE_MODULE)}</li>
											{/foreach}
									</ul>
								</div>
							</div>
						{/if}
					</div>
					{include file=App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
{/strip}
