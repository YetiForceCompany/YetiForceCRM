{*<!-- {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Companies-Edit-Field-Email -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	<div class="input-group mb-1">
		<input name="{$FIELD_MODEL->getFieldName()}" class="tpl-Edit-Field-Email form-control"
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}"
			data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}{if $FIELD_MODEL->get('maximumlength')}maxSize[{$FIELD_MODEL->get('maximumlength')}],{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}"
			{if !empty($MODE) && $MODE eq 'edit' && $FIELD_MODEL->getUIType() eq '106'} readonly="readonly" {/if}
			data-fieldinfo='{$FIELD_INFO}' tabindex="{$FIELD_MODEL->getTabIndex()}"
			{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if} />
		{if !empty($EMAIL_URL)}
			<div class="input-group-append u-cursor-pointer">
				<button class="btn btn-light js-show-modal" type="button" data-url="{$EMAIL_URL}" data-modalid="RegistryEmail-{\App\Layout::getUniqueId()}">
					<span id="{$MODULE_NAME}_editView_fieldName_{$FIELD_NAME}_edit" class="yfi yfi-full-editing-view" title="{\App\Language::translate('LBL_EDIT', $MODULE_NAME)}"></span>
				</button>
			</div>
		{/if}
	</div>
	<!-- /tpl-Settings-Companies-Edit-Field-Email -->
{/strip}
