{*<!-- {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-MultiReference -->
	{assign var="FIELD_NAME" value=$FIELD_MODEL->getName()}
	{assign var="REFERENCE_LIST" value=$FIELD_MODEL->getReferenceList()}
	{assign var="REFERENCE_LIST_COUNT" value=count($REFERENCE_LIST)}
	{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	<div class="uitype_{$MODULE_NAME}_{$FIELD_NAME} js-multiReference-container">
		{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD)}
		{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
		{assign var=REFERENCE_MODULE_MODEL value=Vtiger_Module_Model::getInstance($REFERENCE_LIST[0])}
		<input name="{$FIELD_MODEL->getFieldName()}" type="hidden" data-module="{$MODULE_NAME}" data-multiple="1"
			   value="{\App\Purifier::encodeHtml($FIELD_MODEL->get('fieldvalue'))}" class="js-source-field"
			   data-type="entity" data-fieldtype="{$FIELD_MODEL->getFieldDataType()}" data-displayvalue="{$FIELD_VALUE}"
			   data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if} />
		<div class="input-group referenceGroup">
			<input class="js-popup-reference-module" type="hidden" value="{reset($REFERENCE_LIST)}"/>
			<input id="{$FIELD_NAME}_display" type="text"
				   class="marginLeftZero form-control js-auto-complete"
				   {if !empty($DISPLAYID)}readonly="true"{/if}
				   value="{$FIELD_VALUE}"
				   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
				   data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->get('displaytype') != 10}placeholder="{\App\Language::translate('LBL_TYPE_SEARCH',$MODULE_NAME)}"{/if} {if $REFERENCE_MODULE_MODEL == false} disabled{/if}
					{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}/>
			<div class="input-group-append u-cursor-pointer">
				<button class="btn btn-light js-clear-selection" type="button"
						{if $REFERENCE_MODULE_MODEL == false || $FIELD_MODEL->isEditableReadOnly()} disabled{/if}>
					<span id="{$MODULE_NAME}_editView_fieldName_{$FIELD_NAME}_clear" class="fas fa-times-circle"
						  title="{\App\Language::translate('LBL_CLEAR', $MODULE_NAME)}"></span>
				</button>
				<button class="btn btn-light js-related-popup" type="button" data-irl=""
						{if $REFERENCE_MODULE_MODEL == false || $FIELD_MODEL->isEditableReadOnly()} disabled{/if}>
					<span id="{$MODULE_NAME}_editView_fieldName_{$FIELD_NAME}_select" class="fas fa-search"
						  title="{\App\Language::translate('LBL_SELECT', $MODULE_NAME)}"></span>
				</button>
			</div>
		</div>
	</div>
	<!-- /tpl-Base-Edit-Field-MultiReference -->
{/strip}
