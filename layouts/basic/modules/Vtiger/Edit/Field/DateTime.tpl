{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-DateTime -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	<div class="input-group {$WIDTHTYPE_GROUP} dateTime">
		<input name="{$FIELD_MODEL->getFieldName()}" class=" {if !$FIELD_MODEL->isEditableReadOnly()} dateTimePickerField {/if} form-control"
			value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}"
			id="{$MODULE_NAME}_editView_fieldName_{$FIELD_MODEL->getName()}" type="text" data-hour-format="{$USER_MODEL->get('hour_format')}"
			data-date-format="{$USER_MODEL->get('date_format')}" type="text" tabindex="{$FIELD_MODEL->getTabIndex()}"
			data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}
			data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if} autocomplete="off" />
		<div class="input-group-append">
			<span class="input-group-text u-cursor-pointer" {if $FIELD_MODEL->isEditableReadOnly()} disabled {/if}>
				<span class="fas fa-calendar-alt"></span> &nbsp; <span class="far fa-clock"></span>
			</span>
		</div>
	</div>
	<!-- /tpl-Base-Edit-Field-DateTime -->
{/strip}
