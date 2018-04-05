{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	{assign var="TIME_FORMAT" value=$USER_MODEL->get('hour_format')}
	{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
	<div class="tpl-Edit-Field-Time input-group time">
		{if $FIELD_NAME neq 'time_end'}
			<div class="input-group-prepend">
				<span class="input-group-text"><span class="notEvent js-help-info" data-placement="top" data-content="{\App\Language::translate('LBL_AUTO_FILL_DESCRIPTION', $MODULE)}">
						<input type="checkbox" class="autofill" />
					</span>
				</span>
			</div>
		{/if}
		<input id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}" type="text" data-format="{$TIME_FORMAT}" class="clockPicker form-control" value="{$FIELD_VALUE}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" name="{$FIELD_MODEL->getFieldName()}"
			   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"   {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if} data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if} />
		<div class="input-group-append">
			<span class="input-group-text u-cursor-pointer js-clock__btn" data-js="click">
				<span class="far fa-clock"></span>
			</span>
		</div>
	</div>
{/strip}
