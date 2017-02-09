{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}

	{assign var="POSTFIX" value=substr($FIELD_NAME, -1)}
	{assign var="FIELD_NAME_POST_CODE" value='addresslevel7'|cat:$POSTFIX}
	{assign var="FIELD_NAME_POST_BOX" value='pobox'|cat:$POSTFIX}
	{if $MODE neq 'massedit'}
		{assign var="MODULE_MODEL" value=$RECORD->getModule()}
		{assign var="FIELD_POST_CODE_VALUE" value=Vtiger_Util_Helper::toSafeHTML($RECORD->get($FIELD_NAME_POST_CODE))}
		{assign var="FIELD_POST_BOX_VALUE" value=Vtiger_Util_Helper::toSafeHTML($RECORD->get($FIELD_NAME_POST_BOX))}
	{else}
		{assign var="MODULE_MODEL" value=Vtiger_Module_Model::getInstance($MODULE)}
		{assign var="FIELD_POST_CODE_VALUE" value=''}
		{assign var="FIELD_POST_BOX_VALUE" value=''}
	{/if}
	{assign var="FIELD_MODEL_POST_CODE" value=Vtiger_Field_Model::getInstance($FIELD_NAME_POST_CODE, $MODULE_MODEL)}
	{assign var="FIELD_MODEL_POST_BOX" value=Vtiger_Field_Model::getInstance($FIELD_NAME_POST_BOX, $MODULE_MODEL)}

	{if $FIELD_MODEL_POST_CODE}
		{assign var="SPECIAL_VALIDATOR_POST_CODE" value=$FIELD_MODEL_POST_CODE->getValidator()}
		{assign var="FIELD_INFO_POST_CODE" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL_POST_CODE->getFieldInfo()))}
	{/if}
	{if $FIELD_MODEL_POST_BOX}
		{assign var="SPECIAL_VALIDATOR_POST_BOX" value=$FIELD_MODEL_POST_BOX->getValidator()}
		{assign var="FIELD_INFO_POST_BOX" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL_POST_BOX->getFieldInfo()))}
	{/if}
    <div>
		<div class="col-md-4 noSpaces">
			<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" title="{\App\Language::translate($FIELD_MODEL->get('label'), $MODULE)}" class="noRightRadius form-control {if $FIELD_MODEL->isNameField()}nameField{/if}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_InputMask_Validator_Js.invokeValidation]]" name="{$FIELD_MODEL->getFieldName()}" value="{Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->get('fieldvalue'))}"
				   {if $FIELD_MODEL->get('uitype') eq '3' || $FIELD_MODEL->get('uitype') eq '4'|| $FIELD_MODEL->isReadOnly()} readonly {/if} data-fieldinfo="{$FIELD_INFO}" {if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if} 
				   {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if} {if $FIELD_MODEL->get('fieldparams') != ''}data-inputmask="'mask': '{$FIELD_MODEL->get('fieldparams')}'"{/if} placeholder="{\App\Language::translate($FIELD_MODEL->get('label'), $MODULE)}"/>
		</div>
		<div class="col-md-4 noSpaces">
			{if $FIELD_MODEL_POST_CODE}
				<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME_POST_CODE}" type="text" title="{\App\Language::translate($FIELD_MODEL_POST_CODE->get('label'), $MODULE)}" class="noLeftRadius form-control {if $FIELD_MODEL_POST_CODE->isNameField()}nameField{/if}" data-validation-engine="validate[{if $FIELD_MODEL_POST_CODE->isMandatory() eq true}required,{/if}funcCall[Vtiger_InputMask_Validator_Js.invokeValidation]]" name="{$FIELD_MODEL_POST_CODE->getFieldName()}" value="{$FIELD_POST_CODE_VALUE}"
					   {if $FIELD_MODEL_POST_CODE->get('uitype') eq '3' || $FIELD_MODEL_POST_CODE->get('uitype') eq '4'|| $FIELD_MODEL_POST_CODE->isReadOnly()} readonly {/if} data-fieldinfo="{$FIELD_INFO_POST_CODE}" {if !empty($SPECIAL_VALIDATOR_POST_CODE)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR_POST_CODE)}{/if} 
					   {if $FIELD_MODEL_POST_CODE->get('displaytype') == 10}readonly="readonly"{/if} {if $FIELD_MODEL_POST_CODE->get('fieldparams') != ''}data-inputmask="'mask': '{$FIELD_MODEL_POST_CODE->get('fieldparams')}'"{/if} placeholder="{\App\Language::translate($FIELD_MODEL_POST_CODE->get('label'), $MODULE)}"/>
			{/if}
		</div>
		<div class="col-md-4 noSpaces">
			{if $FIELD_MODEL_POST_BOX}
				<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME_POST_BOX}" type="text" title="{\App\Language::translate($FIELD_MODEL_POST_BOX->get('label'), $MODULE)}" class="form-control {if $FIELD_MODEL_POST_BOX->isNameField()}nameField{/if}" data-validation-engine="validate[{if $FIELD_MODEL_POST_BOX->isMandatory() eq true}required,{/if}funcCall[Vtiger_InputMask_Validator_Js.invokeValidation]]" name="{$FIELD_MODEL_POST_BOX->getFieldName()}" value="{$FIELD_POST_BOX_VALUE}"
					   {if $FIELD_MODEL_POST_BOX->get('uitype') eq '3' || $FIELD_MODEL_POST_BOX->get('uitype') eq '4'|| $FIELD_MODEL_POST_BOX->isReadOnly()} readonly {/if} data-fieldinfo="{$FIELD_INFO_POST_BOX}" {if !empty($SPECIAL_VALIDATOR_POST_BOX)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR_POST_BOX)}{/if} 
					   {if $FIELD_MODEL_POST_BOX->get('displaytype') == 10}readonly="readonly"{/if} {if $FIELD_MODEL_POST_BOX->get('fieldparams') != ''}data-inputmask="'mask': '{$FIELD_MODEL_POST_BOX->get('fieldparams')}'"{/if} placeholder="{\App\Language::translate($FIELD_MODEL_POST_BOX->get('label'), $MODULE)}"/>
			{/if}
		</div>
    </div>
{/strip}
