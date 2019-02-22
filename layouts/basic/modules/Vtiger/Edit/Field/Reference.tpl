{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Base-Edit-Field-Reference -->
	{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
	{assign var="REFERENCE_LIST" value=$FIELD_MODEL->getReferenceList()}
	{assign var="REFERENCE_LIST_COUNT" value=count($REFERENCE_LIST)}
	{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	<div>
		{if {$REFERENCE_LIST_COUNT} eq 1}
			<input name="popupReferenceModule" type="hidden" data-multi-reference="0" title="{reset($REFERENCE_LIST)}" value="{reset($REFERENCE_LIST)}"/>
		{/if}
		{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
		{assign var="VALUE" value=$FIELD_MODEL->getEditViewValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
		{if {$REFERENCE_LIST_COUNT} gt 1}
			{assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($VALUE)}
			{if !empty($REFERENCED_MODULE_STRUCT)}
				{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
			{else}
				{assign var="REFERENCED_MODULE_NAME" value=''}
			{/if}
			{if in_array($REFERENCED_MODULE_NAME, $REFERENCE_LIST)}
				<input name="popupReferenceModule" type="hidden" data-multi-reference="1" value="{$REFERENCED_MODULE_NAME}"/>
			{else}
				<input name="popupReferenceModule" type="hidden" data-multi-reference="1" value="{$REFERENCE_LIST[0]}"/>
			{/if}
		{/if}
		{assign var=REFERENCE_MODULE_MODEL value=Vtiger_Module_Model::getInstance($REFERENCE_LIST[0])}
		<input name="{$FIELD_MODEL->getFieldName()}" type="hidden" value="{$VALUE}" title="{$FIELD_VALUE}" class="sourceField" data-type="entity" data-fieldtype="{$FIELD_MODEL->getFieldDataType()}" data-displayvalue="{$FIELD_VALUE}" data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if} />
		<div class="input-group referenceGroup">
			{if $REFERENCE_LIST_COUNT > 1}
				<div class="input-group-prepend referenceModulesListGroup">
					<select class="select2 referenceModulesList"
							title="{\App\Language::translate('LBL_RELATED_MODULE_TYPE')}" required="required">
						{foreach key=index item=REFERENCE from=$REFERENCE_LIST}
							<option value="{$REFERENCE}" title="{\App\Language::translate($REFERENCE, $REFERENCE)}" {if $REFERENCE eq $REFERENCED_MODULE_NAME} selected {/if}>{\App\Language::translate($REFERENCE, $REFERENCE)}</option>
						{/foreach}
					</select>
				</div>
			{/if}
			<input id="{$FIELD_NAME}_display" name="{$FIELD_MODEL->getFieldName()}_display" type="text"
				   title="{$FIELD_VALUE}" class="marginLeftZero form-control autoComplete"
				   {if !empty($VALUE)}readonly="true"{/if}
				   value="{$FIELD_VALUE}"
				   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
				   data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->get('displaytype') != 10}placeholder="{\App\Language::translate('LBL_TYPE_SEARCH',$MODULE)}"{/if} {if $REFERENCE_MODULE_MODEL == false}disabled{/if}
					{if !empty($SPECIAL_VALIDATOR)}data-validator="{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}"{/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}/>
			<div class="input-group-append u-cursor-pointer">
				<button class="btn btn-light clearReferenceSelection" type="button"
						{if $REFERENCE_MODULE_MODEL == false || $FIELD_MODEL->isEditableReadOnly()}disabled{/if}>
					<span id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_clear" class="fas fa-times-circle"
						  title="{\App\Language::translate('LBL_CLEAR', $MODULE)}"></span>
				</button>
				<button class="btn btn-light relatedPopup" type="button"
						{if $REFERENCE_MODULE_MODEL == false || $FIELD_MODEL->isEditableReadOnly()}disabled{/if}>
					<span id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_select" class="fas fa-search"
						  title="{\App\Language::translate('LBL_SELECT', $MODULE)}"></span>
				</button>
				<!-- Show the add button only if it is edit view  -->
				{if (! empty($VIEW) && ($VIEW eq 'Edit') ) && $REFERENCE_MODULE_MODEL && $REFERENCE_MODULE_MODEL->isQuickCreateSupported()}
					<button class="btn btn-light createReferenceRecord" type="button"
							{if $FIELD_MODEL->isEditableReadOnly()}disabled{/if}>
						<span id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_create" class="fas fa-plus"
							  title="{\App\Language::translate('LBL_CREATE', $MODULE)}"></span>
					</button>
				{/if}
			</div>
		</div>
	</div>
	<!-- /tpl-Base-Edit-Field-Reference -->
{/strip}
