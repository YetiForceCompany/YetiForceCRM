{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var="dateFormat" value=$USER_MODEL->get('date_format')}
	{assign var="hourFormat" value=$USER_MODEL->get('hour_format')}
	<div class="input-group dateTime">
		<input name="{$FIELD_MODEL->getFieldName()}" class="dateTimePickerField form-control"
			   value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}"
			   id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}" type="text" data-hour-format="{$hourFormat}"
			   data-date-format="{$dateFormat}" type="text"
			   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			   {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if}
			   data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if} autocomplete="off"/>
		<span class="input-group-text u-cursor-pointer">
			<span class="fas fa-clock"></span>	&nbsp; <span class="far fa-calendar-alt"></span>
		</span>
	</div>
{/strip}