{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{if  $MAPPING_PANEL eq '1'}
	<h5 class="bg-success text-center menuPanel">
		{\App\Language::translate('LBL_OVERWRITTEN_FIELDS',$QUALIFIED_MODULE)}
	</h5>
{/if}
<input type="hidden" id="workflowModuleName" value="{$SOURCE_MODULE}">
<input type="hidden" id="fieldValueMapping" name="field_value_mapping"
	value="{if !empty($TASK_OBJECT->field_value_mapping)}{\App\Purifier::encodeHtml($TASK_OBJECT->field_value_mapping)}{/if}" />
<input type="hidden" value="{$REFERENCE_FIELD_NAME}" name="reference_field" id="reference_field" />
<div class="js-conditions-container" id="save_fieldvaluemapping" data-js="container">
	{if $RELATED_MODULE_MODEL_NAME neq ''}
		<div>
			<button type="button" class="btn btn-light"
				id="addFieldBtn">{\App\Language::translate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</button>
		</div>
		<br />
		{assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance($TASK_OBJECT->entity_type)}
		{assign var=FIELD_VALUE_MAPPING value=\App\Json::decode($TASK_OBJECT->field_value_mapping)}
		{foreach from=$FIELD_VALUE_MAPPING item=FIELD_MAP}
			<div class="row js-conditions-row padding-bottom1per" data-js="container | clone">
				{assign var=MANDATORY_FIELD value=false}
				{assign var=FIELD_TYPE value=''}
				{assign var=IS_REFERENCE value=false}
				<div class="col-md-4">
					{assign var=SELECTED_FIELD_MODEL value=$RELATED_MODULE_MODEL->getField($FIELD_MAP['fieldname'])}
					<select name="fieldname"
						class="select2 form-control" {if !$SELECTED_FIELD_MODEL || ($SELECTED_FIELD_MODEL->isMandatory() && !$MAPPING_PANEL)} disabled="" {/if}>
						<option value="none"></option>
						{foreach from=$RELATED_MODULE_MODEL->getFields() item=FIELD_MODEL}
							{if ($REFERENCE_FIELD_NAME eq $FIELD_MODEL->getName()) || !$FIELD_MODEL->isWritable()} {continue} {/if}
							{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
							{if $FIELD_MODEL->getFieldDataType() == 'owner'}
								{$SPECIAL_OPTION = [\App\Language::translate('LBL_SPECIAL_OPTIONS') => [
																	'assigned_user_id' => \App\Language::translate('LBL_PARENT_OWNER'),
																	'triggerUser' => \App\Language::translate('LBL_TRIGGER_USER',$QUALIFIED_MODULE)
																	]
																]}
								{$FIELD_INFO['picklistvalues'] = array_merge($FIELD_INFO['picklistvalues'], $SPECIAL_OPTION)}
							{/if}
							<option value="{$FIELD_MODEL->getName()}"
								{if $FIELD_MAP['fieldname'] eq $FIELD_MODEL->getName()}
									{assign var=MANDATORY_FIELD value=$FIELD_MODEL->isMandatory()}
									{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldDataType()}
									{assign var=IS_REFERENCE value=$FIELD_MODEL->isReferenceField()}
									selected=""
								{/if}
								data-fieldtype="{$FIELD_MODEL->getFieldType()}"
								data-field-name="{$FIELD_MODEL->getName()}"
								data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}"
								data-reference="{$FIELD_MODEL->isReferenceField()}">
								{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $RELATED_MODULE_MODEL_NAME)}{if $FIELD_MODEL->isMandatory()}
									<span class="redColor">*</span>
								{/if}
							</option>
						{/foreach}
					</select>
				</div>
				<div class="col-md-3">
					<select name="modulename"
						class="select2 form-control" {if ($FIELD_TYPE eq 'picklist' || $FIELD_TYPE eq 'multipicklist' || $IS_REFERENCE)} disabled="" {/if}>
						<option {if $FIELD_MAP['modulename'] eq $SOURCE_MODULE} selected="" {/if}
							value="{$SOURCE_MODULE}">{\App\Language::translate('LBL_SOURCE_MODULE', $QUALIFIED_MODULE)}: {\App\Language::translate($SOURCE_MODULE, $SOURCE_MODULE)}</option>
						<option {if $FIELD_MAP['modulename'] eq {'destinyModule::'|cat:$RELATED_MODULE_MODEL_NAME} || ($FIELD_MAP['modulename'] eq $RELATED_MODULE_MODEL_NAME && $SOURCE_MODULE neq $RELATED_MODULE_MODEL_NAME)} selected="" {/if}
							value="destinyModule::{$RELATED_MODULE_MODEL_NAME}">{\App\Language::translate('LBL_DESTINY_MODULE', $QUALIFIED_MODULE)} {\App\Language::translate($RELATED_MODULE_MODEL_NAME, $RELATED_MODULE_MODEL_NAME)}</option>
					</select>
				</div>
				<div class="fieldUiHolder col-md-4">
					<input type="text" class="getPopupUi form-control" readonly="" name="fieldValue"
						value="{$FIELD_MAP['value']}" />
					<input type="hidden" name="valuetype" value="{$FIELD_MAP['valuetype']}" />
				</div>
				{if $MANDATORY_FIELD neq true || $MAPPING_PANEL}
					<button type="button" class="btn btn-danger js-condition-delete" data-js="click">
						<span class="fas fa-trash-alt"></span>
					</button>
				{/if}
			</div>
		{/foreach}
		{include file=\App\Layout::getTemplatePath('FieldExpressions.tpl', $QUALIFIED_MODULE) RELATED_MODULE_MODEL=$RELATED_MODULE_MODEL MODULE_MODEL=$MODULE_MODEL FIELD_EXPRESSIONS=$FIELD_EXPRESSIONS}
	{elseif isset($RELATED_MODULE_MODEL)}
		<div>
			<button type="button" class="btn btn-light"
				id="addFieldBtn">{\App\Language::translate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</button>
		</div>
		<br />
		{if $MAPPING_PANEL}
			{assign var=MANDATORY_FIELD_MODELS value=[]}
		{else}
			{assign var=MANDATORY_FIELD_MODELS value=$RELATED_MODULE_MODEL->getMandatoryFieldModels()}
		{/if}
		{foreach from=$MANDATORY_FIELD_MODELS item=MANDATORY_FIELD_MODEL}
			{if in_array($SOURCE_MODULE, $MANDATORY_FIELD_MODEL->getReferenceList())}
				{continue}
			{/if}
			{assign var=IS_REFERENCE value=false}
			<div class="row js-conditions-row padding-bottom1per" data-js="container | clone">
				<span class="col-md-4">
					<select name="fieldname" class="select2 form-control" disabled="">
						<option value="none"></option>
						{foreach from=$RELATED_MODULE_MODEL->getFields() item=FIELD_MODEL}
							{if ($REFERENCE_FIELD_NAME eq $FIELD_MODEL->getName()) || !$FIELD_MODEL->isWritable()} {continue} {/if}
							{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
							{if $FIELD_MODEL->getFieldDataType() == 'owner'}
								{$SPECIAL_OPTION = [\App\Language::translate('LBL_SPECIAL_OPTIONS') => [
														'assigned_user_id' => \App\Language::translate('LBL_PARENT_OWNER'),
														'triggerUser' => \App\Language::translate('LBL_TRIGGER_USER',$QUALIFIED_MODULE)
														]
													]}
								{$FIELD_INFO['picklistvalues'] = array_merge($FIELD_INFO['picklistvalues'], $SPECIAL_OPTION)}
							{/if}
							<option value="{$FIELD_MODEL->getName()}"
								data-fieldtype="{$FIELD_MODEL->getFieldType()}"
								{if $FIELD_MODEL->getName() eq $MANDATORY_FIELD_MODEL->getName()}
									{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldDataType()}
									{assign var=IS_REFERENCE value=$FIELD_MODEL->isReferenceField()}
									selected=""
								{/if}
								data-field-name="{$FIELD_MODEL->getName()}"
								data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}"
								data-reference="{$FIELD_MODEL->isReferenceField()}">
								{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $RELATED_MODULE_MODEL->getName())}
								<span class="redColor">*</span>
							</option>
						{/foreach}
					</select>
				</span>
				<span class="col-md-3">
					<select name="modulename"
						class="select2 form-control" {if ($FIELD_TYPE eq 'picklist' || $FIELD_TYPE eq 'multipicklist' || $IS_REFERENCE)} disabled="" {/if}>
						<option value="{$SOURCE_MODULE}">{\App\Language::translate('LBL_SOURCE_MODULE', $QUALIFIED_MODULE)}: {\App\Language::translate($SOURCE_MODULE, $SOURCE_MODULE)}</option>
						<option {if ($FIELD_TYPE eq 'picklist' || $FIELD_TYPE eq 'multipicklist')} selected="" {/if}
							value="destinyModule::{$RELATED_MODULE_MODEL->get('name')}">{\App\Language::translate('LBL_DESTINY_MODULE', $QUALIFIED_MODULE)} {\App\Language::translate($RELATED_MODULE_MODEL->get('name'),$RELATED_MODULE_MODEL->get('name'))}</option>
					</select>
				</span>
				<span class="fieldUiHolder col-md-4">
					<input type="text" class="getPopupUi form-control" name="fieldValue" value="" />
					<input type="hidden" name="valuetype" value="rawtext" />
				</span>
			</div>
		{/foreach}
		{include file=\App\Layout::getTemplatePath('FieldExpressions.tpl', $QUALIFIED_MODULE) RELATED_MODULE_MODEL=$RELATED_MODULE_MODEL MODULE_MODEL=$MODULE_MODEL FIELD_EXPRESSIONS=$FIELD_EXPRESSIONS}
	{/if}
</div><br />
{if isset($RELATED_MODULE_MODEL)}
	<div class="row js-add-basic-field-container padding-bottom1per d-none">
		<div class="col-md-4">
			{assign var=RELATED_MODULE_MODEL_NAME value=$RELATED_MODULE_MODEL->get('name')}
			<select name="fieldname" class="form-control">
				<option value="none">{\App\Language::translate('LBL_NONE',$QUALIFIED_MODULE)}</option>
				{foreach from=$RELATED_MODULE_MODEL->getFields() item=FIELD_MODEL}
					{if $REFERENCE_FIELD_NAME eq $FIELD_MODEL->getName()} {continue} {/if}
					{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
					{if $FIELD_MODEL->isWritable() && ($MAPPING_PANEL || (!$FIELD_MODEL->isMandatory() && !$MAPPING_PANEL))}
						<option value="{$FIELD_MODEL->getName()}" data-fieldtype="{$FIELD_MODEL->getFieldType()}"
							data-field-name="{$FIELD_MODEL->getName()}"
							data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}"
							data-reference="{$FIELD_MODEL->isReferenceField()}">
							{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $RELATED_MODULE_MODEL_NAME)}
						</option>
					{/if}
				{/foreach}
			</select>
		</div>
		<div class="col-md-3">
			<select name="modulename" class="form-control">
				<option value="{$SOURCE_MODULE}">{\App\Language::translate('LBL_SOURCE_MODULE', $QUALIFIED_MODULE)} {\App\Language::translate($SOURCE_MODULE, $SOURCE_MODULE)}</option>
				<option value="destinyModule::{$RELATED_MODULE_MODEL->get('name')}">{\App\Language::translate('LBL_DESTINY_MODULE', $QUALIFIED_MODULE)} {\App\Language::translate($RELATED_MODULE_MODEL->get('name'), $RELATED_MODULE_MODEL->get('name'))}</option>
			</select>
		</div>
		<div class="fieldUiHolder col-md-4">
			<input type="text" class="form-control" readonly="" name="fieldValue" value="" />
			<input type="hidden" name="valuetype" value="rawtext" />
		</div>
		<button type="button" class="btn btn-danger js-condition-delete" data-js="click">
			<span class="fas fa-trash-alt"></span>
		</button>
	</div>
{/if}
