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
	<!-- tpl-Settings-Picklist-EditView -->
	{assign var=OLD_VALUE value=\App\Purifier::encodeHtml($PICKLIST_VALUE['picklistValue'])}
	<div class='modelContainer modal fade' tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header d-flex align-items-center">
					<span class="yfi yfi-full-editing-view mr-2"></span>
					<h5 class="modal-title">
						{\App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}
						: {\App\Language::translate($PICKLIST_VALUE['picklistValue'], $SOURCE_MODULE)}
					</h5>
					 <a href="#" class="js-popover-tooltip m-2" data-js="popover" data-trigger="hover focus" data-content="{$PICKLIST_VALUE['picklistValue']}">
						<span class="fas fa-info-circle"></span>
					</a>
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
					<input type="hidden" name="oldValue" value="{$OLD_VALUE}"/>
					<input type="hidden" name="primaryKeyId" value="{$PICKLIST_VALUE['picklistValueId']}"/>
					<input type="hidden" name="picklist_valueid" value="{$PICKLIST_VALUE['picklist_valueid']}"/>
					<input type="hidden" name="pickListValues"
						   value='{\App\Purifier::encodeHtml(\App\Json::encode(App\Fields\Picklist::getEditableValues($FIELD_MODEL->getName())))}'/>
					<div class="modal-body tabbable">
						<div class="form-group row align-items-center">
							<div class="col-md-3 col-form-label text-right">
								{\App\Language::translate('LBL_ENTER_NEW_NAME',$QUALIFIED_MODULE)}
							</div>
							{if $EDITABLE}
								<div class="col-md-9 controls">
									<input name="newValue" type="text"
										   class="form-control"
										   data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
										   data-validator='{\App\Purifier::encodeHtml(\App\Json::encode([['name'=>'FieldLabel']]))}'>
								</div>
							{else}
								<div class="col-md-9 controls alert alert-warning">{\App\Language::translate('LBL_NON_EDITABLE_PICKLIST_VALUE',$QUALIFIED_MODULE)}</div>
							{/if}
						</div>
						<div class="form-group row align-items-center">
							<div class="col-md-3 col-form-label text-right">
								{\App\Language::translate('LBL_DESCRIPTION',$QUALIFIED_MODULE)}
								<span class="js-popover-tooltip fas fa-info-circle ml-2" data-js="popover"  data-trigger="hover focus" data-content="{\App\Language::translate('LBL_DESCRIPTION_VALUE_LIST', $QUALIFIED_MODULE)}" data-original-title="" title=""></span>
							</div>
							<div class="col-md-9 controls">
								<textarea class="form-control js-editor" name="description"
										  data-js="ckeditor">
									{if isset($PICKLIST_VALUE['description'])}
										{\App\Purifier::encodeHtml($PICKLIST_VALUE['description'])}
									{/if}
								</textarea>
							</div>
						</div>
						<div class="form-group row align-items-center">
							<div class="col-md-3 col-form-label text-right">
								{\App\Language::translate('LBL_PREFIX',$QUALIFIED_MODULE)}
								<span class="js-popover-tooltip fas fa-info-circle ml-2" data-js="popover"  data-trigger="hover focus" data-content="{\App\Language::translate('LBL_DESCRIPTION_PREFIXES', $QUALIFIED_MODULE)}" data-original-title="" title=""></span>
							</div>
							<div class="col-md-9 controls">
								<input name="prefix" type="text"
									   class="form-control"
									   value="{if isset($PICKLIST_VALUE['prefix'])}
										{\App\Purifier::encodeHtml($PICKLIST_VALUE['prefix'])}
									{/if}">
							</div>
						</div>
					</div>
					{ASSIGN var=BTN_SUCCESS value='LBL_SAVE'}
					{include file=App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-Picklist-EditView -->
{/strip}
