{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	{assign var="TIME_FORMAT" value=$USER_MODEL->get('hour_format')}
	{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
	<div class="tpl-Edit-Field-Time input-group time {$WIDTHTYPE_GROUP}">
		{if $FIELD_NAME neq 'time_end' && isset($VIEW) && $VIEW !== 'MassEdit'}
			<div class="input-group-prepend">
				<span class="input-group-text js-autofill__icon u-cursor-pointer" data-js="click|addClass|removeClass">
					<span class="notEvent js-help-info" data-placement="top"
						data-content="{\App\Language::translate('LBL_AUTO_FILL_DESCRIPTION', $MODULE_NAME)}">
						<input type="checkbox" class="js-autofill d-none" data-js="prop|change" />
						<i class="fas fa-user-clock"></i>
					</span>
				</span>
			</div>
		{/if}
		<input id="{$MODULE_NAME}_editView_fieldName_{$FIELD_MODEL->getName()}" type="text" data-format="{$TIME_FORMAT}" class="clockPicker form-control" value="{$FIELD_VALUE}"
			tabindex="{$FIELD_MODEL->getTabIndex()}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}" name="{$FIELD_MODEL->getFieldName()}"
			data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}
			data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if}
			autocomplete="off" />
		<div class="input-group-append">
			<span class="input-group-text u-cursor-pointer js-clock__btn" data-js="click">
				<span class="far fa-clock"></span>
			</span>
		</div>
	</div>
{/strip}
