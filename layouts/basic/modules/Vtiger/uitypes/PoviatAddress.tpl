{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}

	{assign var="POSTFIX" value=substr($FIELD_NAME, -1)}
	{assign var="FIELD_NAME_TOWNSHIP" value='addresslevel4'|cat:$POSTFIX}
	{assign var="FIELD_NAME_CITY_DISTRICT" value='addresslevel6'|cat:$POSTFIX}
	{if $MODE neq 'massedit'}

		{assign var="MODULE_MODEL" value=$RECORD->getModule()}
		{assign var="FIELD_TOWNSHIP_VALUE" value=Vtiger_Util_Helper::toSafeHTML($RECORD->get($FIELD_NAME_TOWNSHIP))}
		{assign var="FIELD_CITY_DISCTRICT_VALUE" value=Vtiger_Util_Helper::toSafeHTML($RECORD->get($FIELD_NAME_CITY_DISTRICT))}
	{else}
		{assign var="MODULE_MODEL" value=Vtiger_Module_Model::getInstance($MODULE)}
		{assign var="FIELD_TOWNSHIP_VALUE" value=''}
		{assign var="FIELD_CITY_DISCTRICT_VALUE" value=''}
	{/if}
	{assign var="FIELD_MODEL_TOWNSHIP" value=Vtiger_Field_Model::getInstance($FIELD_NAME_TOWNSHIP, $MODULE_MODEL)}
	{assign var="FIELD_MODEL_CITY_DISTRICT" value=Vtiger_Field_Model::getInstance($FIELD_NAME_CITY_DISTRICT, $MODULE_MODEL)}

    <div>
		<div class="col-md-4 noSpaces">
			<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" title="{\App\Language::translate($FIELD_MODEL->get('label'), $MODULE)}" class="noRightRadius form-control {if $FIELD_MODEL->isNameField()}nameField{/if}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_InputMask_Validator_Js.invokeValidation]]" name="{$FIELD_MODEL->getFieldName()}" value="{Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->get('fieldvalue'))}"
				   {if $FIELD_MODEL->get('uitype') eq '3' || $FIELD_MODEL->get('uitype') eq '4'|| $FIELD_MODEL->isReadOnly()} readonly {/if} data-fieldinfo="{$FIELD_INFO}" {if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if} 
				   {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if} {if $FIELD_MODEL->get('fieldparams') != ''}data-inputmask="'mask': '{$FIELD_MODEL->get('fieldparams')}'"{/if} placeholder="{\App\Language::translate($FIELD_MODEL->get('label'), $MODULE)}"/>
		</div>
		<div class="col-md-4 noSpaces">
			{if $FIELD_MODEL_TOWNSHIP}
				{assign var="SPECIAL_VALIDATOR_TOWNSHIP" value=$FIELD_MODEL_TOWNSHIP->getValidator()}
				{assign var="FIELD_INFO_TOWNSHIP" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL_TOWNSHIP->getFieldInfo()))}
				<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME_TOWNSHIP}" type="text" title="{\App\Language::translate($FIELD_MODEL_TOWNSHIP->get('label'), $MODULE)}" class="noLeftRadius form-control {if $FIELD_MODEL_TOWNSHIP->isNameField()}nameField{/if}" data-validation-engine="validate[{if $FIELD_MODEL_TOWNSHIP->isMandatory() eq true}required,{/if}funcCall[Vtiger_InputMask_Validator_Js.invokeValidation]]" name="{$FIELD_MODEL_TOWNSHIP->getFieldName()}" value="{$FIELD_TOWNSHIP_VALUE}"
					   {if $FIELD_MODEL_TOWNSHIP->get('uitype') eq '3' || $FIELD_MODEL_TOWNSHIP->get('uitype') eq '4'|| $FIELD_MODEL_TOWNSHIP->isReadOnly()} readonly {/if} data-fieldinfo="{$FIELD_INFO_TOWNSHIP}" {if !empty($SPECIAL_VALIDATOR_TOWNSHIP)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR_TOWNSHIP)}{/if} 
					   {if $FIELD_MODEL_TOWNSHIP->get('displaytype') == 10}readonly="readonly"{/if} {if $FIELD_MODEL_TOWNSHIP->get('fieldparams') != ''}data-inputmask="'mask': '{$FIELD_MODEL_TOWNSHIP->get('fieldparams')}'"{/if} placeholder="{\App\Language::translate($FIELD_MODEL_TOWNSHIP->get('label'), $MODULE)}"/>
			{/if}
		</div>
		<div class="col-md-4 noSpaces">
			{if $FIELD_MODEL_CITY_DISTRICT}
				{assign var="SPECIAL_VALIDATOR_CITY_DISTRICT" value=$FIELD_MODEL_CITY_DISTRICT->getValidator()}
				{assign var="FIELD_INFO_CITY_DISTRICT" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL_CITY_DISTRICT->getFieldInfo()))}
				<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME_CITY_DISTRICT}" type="text" title="{\App\Language::translate($FIELD_MODEL_CITY_DISTRICT->get('label'), $MODULE)}" class="form-control {if $FIELD_MODEL_CITY_DISTRICT->isNameField()}nameField{/if}" data-validation-engine="validate[{if $FIELD_MODEL_CITY_DISTRICT->isMandatory() eq true}required,{/if}funcCall[Vtiger_InputMask_Validator_Js.invokeValidation]]" name="{$FIELD_MODEL_CITY_DISTRICT->getFieldName()}" value="{$FIELD_CITY_DISCTRICT_VALUE}"
					   {if $FIELD_MODEL_CITY_DISTRICT->get('uitype') eq '3' || $FIELD_MODEL_CITY_DISTRICT->get('uitype') eq '4'|| $FIELD_MODEL_CITY_DISTRICT->isReadOnly()} readonly {/if} data-fieldinfo="{$FIELD_INFO_CITY_DISTRICT}" {if !empty($SPECIAL_VALIDATOR_CITY_DISTRICT)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR_CITY_DISTRICT)}{/if} 
					   {if $FIELD_MODEL_CITY_DISTRICT->get('displaytype') == 10}readonly="readonly"{/if} {if $FIELD_MODEL_CITY_DISTRICT->get('fieldparams') != ''}data-inputmask="'mask': '{$FIELD_MODEL_CITY_DISTRICT->get('fieldparams')}'"{/if} placeholder="{\App\Language::translate($FIELD_MODEL_CITY_DISTRICT->get('label'), $MODULE)}"/>
			{/if}
		</div>
    </div>
{/strip}
