{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}

	{assign var="POSTFIX" value=substr($FIELD_NAME, -1)}
	{assign var="FIELD_NAME_STATE" value='addresslevel2'|cat:$POSTFIX}

	{if $MODE neq 'massedit'}
		{assign var="MODULE_MODEL" value=$RECORD->getModule()}
		{assign var="FIELD_STATE_VALUE" value=Vtiger_Util_Helper::toSafeHTML($RECORD->get($FIELD_NAME_STATE))}
	{else}
		{assign var="MODULE_MODEL" value=Vtiger_Module_Model::getInstance($MODULE)}
		{assign var="FIELD_STATE_VALUE" value=''}

	{/if}
	{assign var="FIELD_MODEL_STATE" value=Vtiger_Field_Model::getInstance($FIELD_NAME_STATE, $MODULE_MODEL)}

    <div>
		<div class="col-md-6 noSpaces">
			<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" title="{\App\Language::translate($FIELD_MODEL->get('label'), $MODULE)}" class="noRightRadius form-control {if $FIELD_MODEL->isNameField()}nameField{/if}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_InputMask_Validator_Js.invokeValidation]]" name="{$FIELD_MODEL->getFieldName()}" value="{Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->get('fieldvalue'))}"
				   {if $FIELD_MODEL->get('uitype') eq '3' || $FIELD_MODEL->get('uitype') eq '4'|| $FIELD_MODEL->isReadOnly()} readonly {/if} data-fieldinfo="{$FIELD_INFO}" {if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if} 
				   {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if} {if $FIELD_MODEL->get('fieldparams') != ''}data-inputmask="'mask': '{$FIELD_MODEL->get('fieldparams')}'"{/if} placeholder="{\App\Language::translate($FIELD_MODEL->get('label'), $MODULE)}"/>
		</div>
		<div class="col-md-6 noSpaces">
			{assign var="SPECIAL_VALIDATOR_BUILDING" value=$FIELD_MODEL_STATE->getValidator()}
			{assign var="FIELD_INFO_BUILDING" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL_STATE->getFieldInfo()))}
			{if $FIELD_MODEL_STATE}
				<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME_STATE}" type="text" title="{\App\Language::translate($FIELD_MODEL_STATE->get('label'), $MODULE)}" class="noLeftRadius form-control {if $FIELD_MODEL_STATE->isNameField()}nameField{/if}" data-validation-engine="validate[{if $FIELD_MODEL_STATE->isMandatory() eq true}required,{/if}funcCall[Vtiger_InputMask_Validator_Js.invokeValidation]]" name="{$FIELD_MODEL_STATE->getFieldName()}" value="{$FIELD_STATE_VALUE}"
					   {if $FIELD_MODEL_STATE->get('uitype') eq '3' || $FIELD_MODEL_STATE->get('uitype') eq '4'|| $FIELD_MODEL_STATE->isReadOnly()} readonly {/if} data-fieldinfo="{$FIELD_INFO_BUILDING}" {if !empty($SPECIAL_VALIDATOR_BUILDING)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR_BUILDING)}{/if} 
					   {if $FIELD_MODEL_STATE->get('displaytype') == 10}readonly="readonly"{/if} {if $FIELD_MODEL_STATE->get('fieldparams') != ''}data-inputmask="'mask': '{$FIELD_MODEL_STATE->get('fieldparams')}'"{/if} placeholder="{\App\Language::translate($FIELD_MODEL_STATE->get('label'), $MODULE)}"/>
			{/if}


		</div>
    </div>
{/strip}
