{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(\includes\utils\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}

	{assign var="POSTFIX" value=substr($FIELD_NAME, -1)}
	{assign var="FIELD_NAME_BUILDING_NUMBER" value='buildingnumber'|cat:$POSTFIX}
	{assign var="FIELD_NAME_LOCAL_NUMBER" value='localnumber'|cat:$POSTFIX}
	{if $MODE neq 'massedit'}
		{assign var="MODULE_MODEL" value=$RECORD->getModule()}
		{assign var="FIELD_BUILDING_NUMBER_VALUE" value=Vtiger_Util_Helper::toSafeHTML($RECORD->get($FIELD_NAME_BUILDING_NUMBER))}
		{assign var="FIELD_LOCAL_NUMBER_VALUE" value=Vtiger_Util_Helper::toSafeHTML($RECORD->get($FIELD_NAME_LOCAL_NUMBER))}
	{else}
		{assign var="MODULE_MODEL" value=Vtiger_Module_Model::getInstance($MODULE)}
		{assign var="FIELD_BUILDING_NUMBER_VALUE" value=''}
		{assign var="FIELD_LOCAL_NUMBER_VALUE" value=''}
	{/if}
	{assign var="FIELD_MODEL_BUILDING_NUMBER" value=Vtiger_Field_Model::getInstance($FIELD_NAME_BUILDING_NUMBER, $MODULE_MODEL)}
	{assign var="FIELD_MODEL_LOCAL_NUMBER" value=Vtiger_Field_Model::getInstance($FIELD_NAME_LOCAL_NUMBER, $MODULE_MODEL)}

	{if $FIELD_MODEL_BUILDING_NUMBER}
		{assign var="SPECIAL_VALIDATOR_BUILDING" value=$FIELD_MODEL_BUILDING_NUMBER->getValidator()}
		{assign var="FIELD_INFO_BUILDING" value=Vtiger_Util_Helper::toSafeHTML(\includes\utils\Json::encode($FIELD_MODEL_BUILDING_NUMBER->getFieldInfo()))}
	{/if}
	{if $FIELD_MODEL_LOCAL_NUMBER}
		{assign var="SPECIAL_VALIDATOR_LOCAL" value=$FIELD_MODEL_LOCAL_NUMBER->getValidator()}
		{assign var="FIELD_INFO_LOCAL" value=Vtiger_Util_Helper::toSafeHTML(\includes\utils\Json::encode($FIELD_MODEL_LOCAL_NUMBER->getFieldInfo()))}
	{/if}

    <div>
		<div class="col-md-7 noSpaces">
			<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" title="{vtranslate($FIELD_MODEL->get('label'), $MODULE)}" class="noRightRadius form-control {if $FIELD_MODEL->isNameField()}nameField{/if}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_InputMask_Validator_Js.invokeValidation]]" name="{$FIELD_MODEL->getFieldName()}" value="{Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->get('fieldvalue'))}"
				   {if $FIELD_MODEL->get('uitype') eq '3' || $FIELD_MODEL->get('uitype') eq '4'|| $FIELD_MODEL->isReadOnly()} readonly {/if} data-fieldinfo="{$FIELD_INFO}" {if !empty($SPECIAL_VALIDATOR)}data-validator={\includes\utils\Json::encode($SPECIAL_VALIDATOR)}{/if} 
				   {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if} {if $FIELD_MODEL->get('fieldparams') != ''}data-inputmask="'mask': '{$FIELD_MODEL->get('fieldparams')}'"{/if} placeholder="{vtranslate($FIELD_MODEL->get('label'), $MODULE)}"/>
		</div>
		<div class="col-md-5 noSpaces">
			<div class="input-group">
				{if $FIELD_MODEL_BUILDING_NUMBER}
					<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME_BUILDING_NUMBER}" type="text" title="{vtranslate($FIELD_MODEL_BUILDING_NUMBER->get('label'), $MODULE)}" class="noLeftRadius form-control {if $FIELD_MODEL_BUILDING_NUMBER->isNameField()}nameField{/if}" data-validation-engine="validate[{if $FIELD_MODEL_BUILDING_NUMBER->isMandatory() eq true}required,{/if}funcCall[Vtiger_InputMask_Validator_Js.invokeValidation]]" name="{$FIELD_MODEL_BUILDING_NUMBER->getFieldName()}" value="{$FIELD_BUILDING_NUMBER_VALUE}"
						   {if $FIELD_MODEL_BUILDING_NUMBER->get('uitype') eq '3' || $FIELD_MODEL_BUILDING_NUMBER->get('uitype') eq '4'|| $FIELD_MODEL_BUILDING_NUMBER->isReadOnly()} readonly {/if} data-fieldinfo="{$FIELD_INFO_BUILDING}" {if !empty($SPECIAL_VALIDATOR_BUILDING)}data-validator={\includes\utils\Json::encode($SPECIAL_VALIDATOR_BUILDING)}{/if} 
						   {if $FIELD_MODEL_BUILDING_NUMBER->get('displaytype') == 10}readonly="readonly"{/if} {if $FIELD_MODEL_BUILDING_NUMBER->get('fieldparams') != ''}data-inputmask="'mask': '{$FIELD_MODEL_BUILDING_NUMBER->get('fieldparams')}'"{/if} placeholder="{vtranslate($FIELD_MODEL_BUILDING_NUMBER->get('label'), $MODULE)}"/>
				{/if}
				{if $FIELD_MODEL_LOCAL_NUMBER}
					<span class="input-group-addon">/</span>
					<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME_LOCAL_NUMBER}" type="text" title="{vtranslate($FIELD_MODEL_LOCAL_NUMBER->get('label'), $MODULE)}" class="form-control {if $FIELD_MODEL_LOCAL_NUMBER->isNameField()}nameField{/if}" data-validation-engine="validate[{if $FIELD_MODEL_LOCAL_NUMBER->isMandatory() eq true}required,{/if}funcCall[Vtiger_InputMask_Validator_Js.invokeValidation]]" name="{$FIELD_MODEL_LOCAL_NUMBER->getFieldName()}" value="{$FIELD_LOCAL_NUMBER_VALUE}"
						   {if $FIELD_MODEL_LOCAL_NUMBER->get('uitype') eq '3' || $FIELD_MODEL_LOCAL_NUMBER->get('uitype') eq '4'|| $FIELD_MODEL_LOCAL_NUMBER->isReadOnly()} readonly {/if} data-fieldinfo="{$FIELD_INFO_LOCAL}" {if !empty($SPECIAL_VALIDATOR_LOCAL)}data-validator={\includes\utils\Json::encode($SPECIAL_VALIDATOR_LOCAL)}{/if} 
						   {if $FIELD_MODEL_LOCAL_NUMBER->get('displaytype') == 10}readonly="readonly"{/if} {if $FIELD_MODEL_LOCAL_NUMBER->get('fieldparams') != ''}data-inputmask="'mask': '{$FIELD_MODEL_LOCAL_NUMBER->get('fieldparams')}'"{/if} placeholder="{vtranslate($FIELD_MODEL_LOCAL_NUMBER->get('label'), $MODULE)}"/>
				{/if}
			</div>
		</div>
    </div>
{/strip}
