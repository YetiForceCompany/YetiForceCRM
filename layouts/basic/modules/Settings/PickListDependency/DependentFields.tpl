{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-PickListDependency-DependentFields -->
	<div class="form-group row mb-0">
		{foreach from=$STRUCTURE item=FIELD_MODEL name=field}
			<div class="col-12 col-md-4 mb-2 js-field-container">
				<label class="u-text-small-bold mb-1">
					{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}
					{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
					{if $FIELD_MODEL->get('tooltip')}
						<div class="js-popover-tooltip ml-1 d-inline my-auto u-h-fit u-cursor-pointer popover-triggered" data-placement="top" data-content="{\App\Language::translate($FIELD_MODEL->get('tooltip'), $QUALIFIED_MODULE)}">
							<span class="fas fa-info-circle"></span>
						</div>
					{/if}:
				</label>
				<div class="fieldValue m-auto">
					{if $FIELD_MODEL->getName() eq 'third_field'}
						<div class="input-group w-100">
							{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue') )}
							<select name="{$FIELD_MODEL->getFieldName()}" {if !$FIELD_VALUE} disabled="diasbled" {/if} class="select2 form-control js-third-field" data-fieldinfo='{\App\Json::encode($FIELD_MODEL->getFieldInfo())|escape}' tabindex="{$FIELD_MODEL->getTabIndex()}"
								title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}"
								data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
								{if !empty($FIELD_MODEL->getValidator())}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getValidator()))}' {/if}
								data-selected-value="{\App\Purifier::encodeHtml($FIELD_VALUE)}" {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if}>
								{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$FIELD_MODEL->getPicklistValues()}
									<option value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}" title="{\App\Purifier::encodeHtml($PICKLIST_VALUE)}" {if trim($FIELD_VALUE) eq trim($PICKLIST_NAME)}selected{/if}>
										{\App\Purifier::encodeHtml($PICKLIST_VALUE)}
									</option>
								{/foreach}
							</select>
							<div class="input-group-append">
								<button type="button" class="js-add-next-level-field btn {if $FIELD_VALUE}btn-danger{else}btn-success{/if}" data-on="btn-success" data-off="btn-danger" title="{\App\Language::translate('LBL_ON_OFF_FIELD', $QUALIFIED_MODULE)}" {if $FIELD_MODEL->isEditableReadOnly()}disabled="disabled" {/if}>
									<span class="fas fa-power-off"></span>
								</button>
							</div>
						</div>
					{else}
						{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE MODULE_NAME=$QUALIFIED_MODULE RECORD=null}
					{/if}
				</div>
			</div>
		{/foreach}
	</div>
	<!-- /tpl-Settings-PickListDependency-DependentFields -->
{/strip}
