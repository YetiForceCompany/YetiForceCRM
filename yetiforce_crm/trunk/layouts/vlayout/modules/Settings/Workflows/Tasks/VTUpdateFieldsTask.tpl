{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<div class="row-fluid">
		<div class="span2"><strong>{vtranslate('LBL_SET_FIELD_VALUES',$QUALIFIED_MODULE)}</strong></div>
	</div><br>
	<div>
		<button type="button" class="btn" id="addFieldBtn">{vtranslate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</button>
	</div><br>
	<div class="row-fluid conditionsContainer" id="save_fieldvaluemapping">
		{assign var=FIELD_VALUE_MAPPING value=ZEND_JSON::decode($TASK_OBJECT->field_value_mapping)}
		<input type="hidden" id="fieldValueMapping" name="field_value_mapping" value='{Vtiger_Util_Helper::toSafeHTML($TASK_OBJECT->field_value_mapping)}' />
		{foreach from=$FIELD_VALUE_MAPPING item=FIELD_MAP}
			<div class="row-fluid conditionRow padding-bottom1per">
				<span class="span4">
					<select name="fieldname" class="chzn-select" style="min-width: 250px" data-placeholder="{vtranslate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
						<option></option>
						{foreach from=$MODULE_MODEL->getFields() item=FIELD_MODEL}
                            {if !$FIELD_MODEL->isEditable() or $FIELD_MODEL->getFieldDataType() eq 'reference' or ($MODULE_MODEL->get('name')=="Documents" and in_array($FIELD_MODEL->get('name'),$RESTRICTFIELDS))} 
                                {continue}
                            {/if}
							{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
							{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
							<option value="{$FIELD_MODEL->get('name')}" {if $FIELD_MAP['fieldname'] eq $FIELD_MODEL->get('name')}selected=""{/if}data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->get('name')}" data-fieldinfo='{ZEND_JSON::encode($FIELD_INFO)}' >
								{if $SOURCE_MODULE neq $MODULE_MODEL->get('name')}
									({vtranslate($MODULE_MODEL->get('name'), $MODULE_MODEL->get('name'))})  {vtranslate($FIELD_MODEL->get('label'), $MODULE_MODEL->get('name'))}
								{else}
									{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
								{/if}
							</option>
						{/foreach}
					</select>
				</span>
				<span class="fieldUiHolder span4 marginLeftZero">
					<input type="text" class="getPopupUi row-fluid" readonly="" name="fieldValue" value="{$FIELD_MAP['value']}" />
					<input type="hidden" name="valuetype" value="{$FIELD_MAP['valuetype']}" />
				</span>
				<span class="cursorPointer span">
					<i class="alignMiddle deleteCondition icon-trash"></i>
				</span>
			</div>
		{/foreach}
		{include file="FieldExpressions.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
		</div><br>
		<div class="row-fluid basicAddFieldContainer hide padding-bottom1per">
			<span class="span4">
				<select name="fieldname" data-placeholder="{vtranslate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}" style="min-width: 250px">
					<option></option>
					{foreach from=$MODULE_MODEL->getFields() item=FIELD_MODEL}
                        {if !$FIELD_MODEL->isEditable() or $FIELD_MODEL->getFieldDataType() eq 'reference' or ($MODULE_MODEL->get('name')=="Documents" and in_array($FIELD_MODEL->get('name'),$RESTRICTFIELDS))}
                            {continue}
                        {/if}
						{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
						{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
						<option value="{$FIELD_MODEL->get('name')}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->get('name')}" data-fieldinfo='{ZEND_JSON::encode($FIELD_INFO)}' >
							{if $SOURCE_MODULE neq $MODULE_MODEL->get('name')}
								({vtranslate($MODULE_MODEL->get('name'), $MODULE_MODEL->get('name'))})  {vtranslate($FIELD_MODEL->get('label'), $MODULE_MODEL->get('name'))}
							{else}
								{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
							{/if}
						</option>
					{/foreach}
				</select>
			</span>
			<span class="fieldUiHolder span4 marginLeftZero">
				<input type="text" class="row-fluid" readonly="" name="fieldValue" value="" />
				<input type="hidden" name="valuetype" value="rawtext" />
			</span>
			<span class="cursorPointer span">
				<i class="alignMiddle deleteCondition icon-trash"></i>
			</span>
		</div>
		{/strip}