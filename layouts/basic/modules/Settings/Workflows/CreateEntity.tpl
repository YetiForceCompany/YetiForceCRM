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
{if ($RELATED_MODULE_MODEL || $RELATED_MODULE_MODEL_NAME) && $MAPPING_PANEL}
	<h5 class="bg-success text-center menuPanel">
		{vtranslate('LBL_OVERWRITTEN_FIELDS',$QUALIFIED_MODULE)}
	</h5>
{/if}
<input type="hidden" id="fieldValueMapping" name="field_value_mapping" value='{$TASK_OBJECT->field_value_mapping}' />
<input type="hidden" value="{if $TASK_ID}{$TASK_OBJECT->reference_field}{else}{$REFERENCE_FIELD_NAME}{/if}" name='reference_field' id='reference_field' />
<div class="conditionsContainer" id="save_fieldvaluemapping">
	{if $RELATED_MODULE_MODEL_NAME neq ''}
		<div>
			<button type="button" class="btn btn-default" id="addFieldBtn">{vtranslate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</button>
		</div><br>
		{assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance($TASK_OBJECT->entity_type)}
		{assign var=FIELD_VALUE_MAPPING value=\App\Json::decode($TASK_OBJECT->field_value_mapping)}
		{foreach from=$FIELD_VALUE_MAPPING item=FIELD_MAP}
			<div class="row conditionRow padding-bottom1per">
				<div class="col-md-4">
					{assign var=SELECTED_FIELD_MODEL value=$RELATED_MODULE_MODEL->getField($FIELD_MAP['fieldname'])}
					<select name="fieldname" class="select2 form-control" {if $SELECTED_FIELD_MODEL->isMandatory() && !$MAPPING_PANEL} disabled="" {/if} >
						<option value="none"></option>
						{foreach from=$RELATED_MODULE_MODEL->getFields() item=FIELD_MODEL}
							{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
							{if $FIELD_MODEL->getFieldDataType() == 'owner'}
								{$SPECIAL_OPTION = [vtranslate('LBL_SPECIAL_OPTIONS') => ['assigned_user_id' => {vtranslate('LBL_PARENT_OWNER')}]]}
								{$FIELD_INFO['picklistvalues'] = array_merge($FIELD_INFO['picklistvalues'], $SPECIAL_OPTION)}
							{/if}
							<option value="{$FIELD_MODEL->get('name')}" {if $FIELD_MAP['fieldname'] eq $FIELD_MODEL->get('name')} {if $FIELD_MODEL->isMandatory()}{assign var=MANDATORY_FIELD value=true} {else} {assign var=MANDATORY_FIELD value=false} {/if}{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldDataType()} selected=""{/if} data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->get('name')}" data-fieldinfo='{\App\Json::encode($FIELD_INFO)}' >
								{vtranslate($FIELD_MODEL->get('label'), $RELATED_MODULE_MODEL_NAME)}{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
							</option>	
						{/foreach}
					</select>
				</div>
				<div class="col-md-3">
					<select name="modulename" class="select2 form-control" {if ($FIELD_TYPE eq 'picklist' || $FIELD_TYPE eq 'multipicklist')} disabled="" {/if}>
						<option {if $FIELD_MAP['modulename'] eq $SOURCE_MODULE} selected="" {/if} value="{$SOURCE_MODULE}">{vtranslate($SOURCE_MODULE, $SOURCE_MODULE)}</option>
						<option {if $FIELD_MAP['modulename'] eq $RELATED_MODULE_MODEL_NAME} selected="" {/if} value="{$RELATED_MODULE_MODEL_NAME}">{vtranslate($RELATED_MODULE_MODEL_NAME, $RELATED_MODULE_MODEL_NAME)}</option>
					</select>
				</div>
				<div class="fieldUiHolder col-md-4">
					<input type="text" class="getPopupUi form-control" readonly="" name="fieldValue" value="{$FIELD_MAP['value']}" />
					<input type="hidden" name="valuetype" value="{$FIELD_MAP['valuetype']}" />
				</div>
				{if $MANDATORY_FIELD neq true || $MAPPING_PANEL}
					<div class="cursorPointer btn span">
						<span class="alignMiddle deleteCondition glyphicon glyphicon-trash"></span>
					</div>
				{/if}
			</div>
		{/foreach}

		{include file="FieldExpressions.tpl"|@vtemplate_path:$QUALIFIED_MODULE RELATED_MODULE_MODEL=$RELATED_MODULE_MODEL MODULE_MODEL=$MODULE_MODEL FIELD_EXPRESSIONS=$FIELD_EXPRESSIONS}
	{else}
		{if $RELATED_MODULE_MODEL}
			<div>
				<button type="button" class="btn btn-default" id="addFieldBtn">{vtranslate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</button>
			</div><br>
			{if $MAPPING_PANEL}
				{assign var=MANDATORY_FIELD_MODELS value=[]}
			{else}
				{assign var=MANDATORY_FIELD_MODELS value=$RELATED_MODULE_MODEL->getMandatoryFieldModels()}
			{/if}
			{foreach from=$MANDATORY_FIELD_MODELS item=MANDATORY_FIELD_MODEL}
				{if in_array($SOURCE_MODULE, $MANDATORY_FIELD_MODEL->getReferenceList())}
					{continue}
				{/if}
				<div class="row conditionRow padding-bottom1per">
					<span class="col-md-4">
						<select name="fieldname" class="select2 form-control" disabled="">
							<option value="none"></option>
							{foreach from=$RELATED_MODULE_MODEL->getFields() item=FIELD_MODEL}
								{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
								{if $FIELD_MODEL->getFieldDataType() == 'owner'}
									{$SPECIAL_OPTION = [vtranslate('LBL_SPECIAL_OPTIONS') => ['assigned_user_id' => {vtranslate('LBL_PARENT_OWNER')}]]}
									{$FIELD_INFO['picklistvalues'] = array_merge($FIELD_INFO['picklistvalues'], $SPECIAL_OPTION)}
								{/if}
								<option value="{$FIELD_MODEL->get('name')}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" {if $FIELD_MODEL->get('name') eq $MANDATORY_FIELD_MODEL->get('name')} {assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldDataType()} selected=""{/if} data-field-name="{$FIELD_MODEL->get('name')}" data-fieldinfo='{\App\Json::encode($FIELD_INFO)}' >
								{vtranslate($FIELD_MODEL->get('label'), $RELATED_MODULE_MODEL->getName())}<span class="redColor">*</span>
								</option>	
							{/foreach}
						</select>
					</span>
					<span class="col-md-3">
						<select name="modulename" class="select2 form-control" {if ($FIELD_TYPE eq 'picklist' || $FIELD_TYPE eq 'multipicklist')} disabled="" {/if}>
							<option value="{$SOURCE_MODULE}">{vtranslate($SOURCE_MODULE, $SOURCE_MODULE)}</option>
							<option {if ($FIELD_TYPE eq 'picklist' || $FIELD_TYPE eq 'multipicklist')} selected="" {/if} value="{$RELATED_MODULE_MODEL->get('name')}">{vtranslate($RELATED_MODULE_MODEL->get('name'),$RELATED_MODULE_MODEL->get('name'))}</option>
						</select>
					</span>
					<span class="fieldUiHolder col-md-4">
						<input type="text" class="getPopupUi form-control" name="fieldValue" value="" />
						<input type="hidden" name="valuetype" value="rawtext" />
					</span>
				</div>
			{/foreach}
			{include file="FieldExpressions.tpl"|@vtemplate_path:$QUALIFIED_MODULE RELATED_MODULE_MODEL=$RELATED_MODULE_MODEL MODULE_MODEL=$MODULE_MODEL FIELD_EXPRESSIONS=$FIELD_EXPRESSIONS}
		{/if}
	{/if}
</div><br>
{if $RELATED_MODULE_MODEL}
	<div class="row basicAddFieldContainer padding-bottom1per hide">
		<div class="col-md-4">
			{assign var=RELATED_MODULE_MODEL_NAME value=$RELATED_MODULE_MODEL->get('name')}
			<select name="fieldname" class="form-control">
				<option value="none">{vtranslate('LBL_NONE',$QUALIFIED_MODULE)}</option>
				{foreach from=$RELATED_MODULE_MODEL->getFields() item=FIELD_MODEL}
					{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
					{if  $FIELD_MODEL->getFieldDataType() neq 'reference' && ($MAPPING_PANEL || (!$FIELD_MODEL->isMandatory() && !$MAPPING_PANEL))}
					<option value="{$FIELD_MODEL->get('name')}" data-fieldtype="{$FIELD_MODEL->getFieldType()}"  data-field-name="{$FIELD_MODEL->get('name')}" data-fieldinfo='{\App\Json::encode($FIELD_INFO)}' >
						{vtranslate($FIELD_MODEL->get('label'), $RELATED_MODULE_MODEL_NAME)} 
					</option>
					{/if}
				{/foreach}
			</select>
		</div>
		<div class="col-md-3">
			<select name="modulename" class="form-control">
				<option value="{$SOURCE_MODULE}">{vtranslate($SOURCE_MODULE, $SOURCE_MODULE)}</option>
				<option value="{$RELATED_MODULE_MODEL->get('name')}">{vtranslate($RELATED_MODULE_MODEL->get('name'), $RELATED_MODULE_MODEL->get('name'))}</option>
			</select>
		</div>
		<div class="fieldUiHolder col-md-4">
			<input type="text" class="form-control" readonly="" name="fieldValue" value="" />
			<input type="hidden" name="valuetype" value="rawtext" />
		</div>
		<div class="cursorPointer btn span">
			<span class="alignMiddle deleteCondition glyphicon glyphicon-trash"></span>
		</div>
	</div>
{/if}
