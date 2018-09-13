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
	<div class='tpl-Settings-Picklist-EditView modelContainer modal fade' tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						{\App\Language::translate('LBL_RENAME_PICKLIST_ITEM', $QUALIFIED_MODULE)} {$PICKLIST_VALUE['picklistValue']}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form id="renameItemForm" class="form-horizontal" method="post" action="index.php">
					<input type="hidden" name="module" value="{$MODULE}"/>
					<input type="hidden" name="parent" value="Settings"/>
					<input type="hidden" name="source_module" value="{$SOURCE_MODULE}"/>
					<input type="hidden" name="action" value="SaveAjax"/>
					<input type="hidden" name="mode" value="rename"/>
					<input type="hidden" name="picklistName" value="{$FIELD_MODEL->getName()}"/>
					<input type="hidden" name="oldValue" value="{$PICKLIST_VALUE['picklistValue']}"/>
					<input type="hidden" name="id" value="{$PICKLIST_VALUE['picklistValueId']}"/>
					<input type="hidden" name="picklist_valueid" value="{$PICKLIST_VALUE['picklist_valueid']}"/>
					<input type="hidden" name="pickListValues"
						   value='{\App\Purifier::encodeHtml(\App\Json::encode(App\Fields\Picklist::getEditablePicklistValues($FIELD_MODEL->getName())))}'/>
					<div class="modal-body tabbable">
						{if $EDITABLE}
							<div class="form-group row align-items-center">
								<div class="col-md-3 col-form-label text-right">
									{\App\Language::translate('LBL_ENTER_NEW_NAME',$QUALIFIED_MODULE)}
								</div>
								<div class="col-md-9 controls">
									<input type="text" class="form-control"
										   data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
										   data-validator={\App\Json::encode([['name'=>'FieldLabel']])} name="newValue">
								</div>
							</div>
							<div class="form-group row align-items-center">
								<div class="col-md-3 col-form-label text-right">
									{\App\Language::translate('LBL_DESCRIPTION',$QUALIFIED_MODULE)}
								</div>
								<div class="col-md-9 controls">
								<textarea class="form-control js-editor" name="description"
										  data-js="ckeditor">{\App\Purifier::encodeHtml($PICKLIST_VALUE['description'])}</textarea>
								</div>
							</div>
							{if $FIELD_MODEL->get('uitype') === 15}
								<div class="form-group row align-items-center">
									<div class="col-md-3 col-form-label text-right">
										{\App\Language::translate('LBL_CLOSES_RECORD',$QUALIFIED_MODULE)}
									</div>
									<div class="col-md-9 controls">
										<input class="form-control js-close-state" type="checkbox" value="1"
											   {if $PICKLIST_VALUE['close_state']}checked="checked"{/if}
											   name="close_state">
									</div>
								</div>
							{/if}
						{else}
							<div class="alert alert-warning">{\App\Language::translate('LBL_NON_EDITABLE_PICKLIST_VALUES',$QUALIFIED_MODULE)}</div>
						{/if}
					</div>
					{if $EDITABLE}
						{ASSIGN var=BTN_SUCCESS value='LBL_SAVE'}
					{/if}
					{include file=App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
{/strip}
