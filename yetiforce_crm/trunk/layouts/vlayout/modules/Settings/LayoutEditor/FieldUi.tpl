{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
{strip}
{foreach item=FIELD_MODEL from=$FIELD_MODELS_LIST}
	{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
	<div class="span6 opacity editFields marginLeftZero border1px" data-field-id="{$FIELD_MODEL->get('id')}" data-sequence="{$FIELD_MODEL->get('sequence')}">
	<div class="row-fluid padding1per">
		{assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
		<span class="span1">&nbsp;
			{if $FIELD_MODEL->isEditable()}
				<a><img src="{vimage_path('drag.png')}" border="0" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/></a>
			{/if}
		</span>
		<div class="span11 marginLeftZero">
			<span class="fieldLabel">{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}&nbsp;
																{if $IS_MANDATORY}<span class="redColor">*</span>{/if}</span>
			<span class="btn-group pull-right actions">
				{if $FIELD_MODEL->isEditable()}
				<a href="javascript:void(0)" class="dropdown-toggle editFieldDetails" data-toggle="dropdown">
					<i class="icon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></i>
				</a>
				<div class="basicFieldOperations pull-right hide" style="width : 250px;">
					<form class="form-horizontal fieldDetailsForm" method="POST">
						<div class="modal-header"><strong>{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}</strong></div>
						<div class="contentsBackground" style="padding-bottom: 5px;">
						<span><label class="checkbox" style="padding-left: 25px; padding-top: 5px;"><input type="hidden" name="mandatory" value="O" />
								<input type="checkbox" name="mandatory" {if $IS_MANDATORY} checked {/if} 
									{if $FIELD_MODEL->isMandatoryOptionDisabled()} readonly="readonly" {/if} value="M" />&nbsp;
								{vtranslate('LBL_MANDATORY_FIELD', $QUALIFIED_MODULE)}
						</label></span>
						<span><label class="checkbox" style="padding-left: 25px; padding-top: 5px;"><input type="hidden" name="presence" value="1" />
								<input type="checkbox" name="presence" {if $FIELD_MODEL->isViewable()} checked {/if} 
									{if $FIELD_MODEL->isActiveOptionDisabled()} readonly="readonly" class="optionDisabled"{/if}{if $IS_MANDATORY} readonly="readonly"{/if} value="2" />&nbsp;
								{vtranslate('LBL_ACTIVE', $QUALIFIED_MODULE)}
						</label></span>
						<span><label class="checkbox" style="padding-left: 25px; padding-top: 5px;"><input type="hidden" name="quickcreate" value="1" />
								<input type="checkbox" name="quickcreate" {if $FIELD_MODEL->isQuickCreateEnabled()} checked {/if} 
									{if $FIELD_MODEL->isQuickCreateOptionDisabled()} readonly="readonly" class="optionDisabled"{/if}{if $IS_MANDATORY} readonly="readonly"{/if} value="2" />&nbsp;
								{vtranslate('LBL_QUICK_CREATE', $QUALIFIED_MODULE)}
						</label></span>
						<span><label class="checkbox" style="padding-left: 25px; padding-top: 5px;"><input type="hidden" name="summaryfield" value="0" />
								<input type="checkbox" name="summaryfield" {if $FIELD_MODEL->isSummaryField()} checked {/if}
									{if $FIELD_MODEL->isSummaryFieldOptionDisabled()} readonly="readonly" class="optionDisabled"{/if} value="1" />&nbsp;
									{vtranslate('LBL_SUMMARY_FIELD', $QUALIFIED_MODULE)}
						</label></span>
						<span><label class="checkbox" style="padding-left: 25px; padding-top: 5px;"><input type="hidden" name="masseditable" value="2" />
								<input type="checkbox" name="masseditable" {if $FIELD_MODEL->isMassEditable()} checked {/if} 
									{if $FIELD_MODEL->isMassEditOptionDisabled()} readonly="readonly" {/if} value="1" />&nbsp;
								{vtranslate('LBL_MASS_EDIT', $QUALIFIED_MODULE)}
						</label></span>
						<span><label class="checkbox" style="padding-left: 25px; padding-top: 5px;"><input type="hidden" name="defaultvalue" value="" />
								<input type="checkbox" name="defaultvalue" {if $FIELD_MODEL->hasDefaultValue()} checked {/if} 
									{if $FIELD_MODEL->isDefaultValueOptionDisabled()} readonly="readonly" {/if} value="" />&nbsp;
								{vtranslate('LBL_DEFAULT_VALUE', $QUALIFIED_MODULE)}</label>
								<div class="padding1per defaultValueUi {if !$FIELD_MODEL->hasDefaultValue()} zeroOpacity {/if}" style="padding : 0px 10px 0px 25px;">
								{if $FIELD_MODEL->isDefaultValueOptionDisabled() neq "true"}
									{if $FIELD_MODEL->getFieldDataType() eq "picklist"}
										{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
										<select class="span2" name="fieldDefaultValue" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if} data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"  data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_INFO))}'>
											{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
												<option value="{$PICKLIST_NAME}" {if $FIELD_MODEL->get('defaultvalue') eq $PICKLIST_NAME} selected {/if}>{vtranslate($PICKLIST_VALUE, $SELECTED_MODULE_NAME)}</option>
											{/foreach}
										</select>
									{elseif $FIELD_MODEL->getFieldDataType() eq "multipicklist"}
										{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
										{assign var="FIELD_VALUE_LIST" value=explode(' |##| ',$FIELD_MODEL->get('defaultvalue'))}
										<select multiple class="span2" name="fieldDefaultValue" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if} data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"  data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_INFO))}'>
											{foreach item=PICKLIST_VALUE from=$PICKLIST_VALUES}
												<option value="{$PICKLIST_VALUE}" {if in_array($PICKLIST_VALUE, $FIELD_VALUE_LIST)} selected {/if}>{vtranslate($PICKLIST_VALUE, $SELECTED_MODULE_NAME)}</option>
											{/foreach}
										</select>
									{elseif $FIELD_MODEL->getFieldDataType() eq "boolean"}
										<input type="hidden" name="fieldDefaultValue" value="" />
										<input type="checkbox" name="fieldDefaultValue" value="1" 
											{if $FIELD_MODEL->get('defaultvalue') eq 1} checked {/if} data-fieldinfo='{ZEND_JSON::encode($FIELD_INFO)}' />
									{elseif $FIELD_MODEL->getFieldDataType() eq "time"}
										<div class="input-append time">
											<input type="text" class="input-small" data-toregister="time" data-format="{$USER_MODEL->get('hour_format')}" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if} data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"  value="{$FIELD_MODEL->get('defaultvalue')}" name="fieldDefaultValue" data-fieldinfo='{ZEND_JSON::encode($FIELD_INFO)}'/>
											<span class="add-on cursorPointer">
												<i class="icon-time"></i>
											</span>
										</div>
									{elseif $FIELD_MODEL->getFieldDataType() eq "date"}
										<div class="input-append date">
											{assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
											<input type="text" class="input-medium" name="fieldDefaultValue" data-toregister="date" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if} data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"  data-date-format="{$USER_MODEL->get('date_format')}" data-fieldinfo='{ZEND_JSON::encode($FIELD_INFO)}'
												value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('defaultvalue'))}" />
											<span class="add-on"><i class="icon-calendar"></i></span>
										</div>
									{elseif $FIELD_MODEL->getFieldDataType() eq "percentage"}
										<div class="input-append">
											<input type="number" class="input-medium" name="fieldDefaultValue" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if} data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" 
												value="{$FIELD_MODEL->get('defaultvalue')}" data-fieldinfo='{ZEND_JSON::encode($FIELD_INFO)}' step="any" /><span class="add-on">%</span>
										</div>
									{elseif $FIELD_MODEL->getFieldDataType() eq "currency"}
										<div class="input-prepend">
											<span class="add-on">{$USER_MODEL->get('currency_symbol')}</span>
											<input type="text" class="input-medium" name="fieldDefaultValue" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if} data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" 
												data-fieldinfo='{ZEND_JSON::encode($FIELD_INFO)}' value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('defaultvalue'))}" 
												data-decimal-seperator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-seperator='{$USER_MODEL->get('currency_grouping_separator')}' />
										</div>
									{else}
										<input type="text" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if} data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" class="input-medium" name="fieldDefaultValue" value="{$FIELD_MODEL->get('defaultvalue')}" data-fieldinfo='{ZEND_JSON::encode($FIELD_INFO)}'/>
									{/if}
								{/if}
								</div>
							</span></div>
							<div class="modal-footer" style="padding: 0px;"><span class="pull-right">
								<button class="btn btn-success saveFieldDetails" data-field-id="{$FIELD_MODEL->get('id')}" type="submit" style="margin: 5px;">
									<strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
							</span></div>
						</form>
					</div>
					{/if}
					{if $FIELD_MODEL->isCustomField() eq 'true'}
						<a href="javascript:void(0)" class="deleteCustomField" data-field-id="{$FIELD_MODEL->get('id')}">
							<i class="icon-trash alignMiddle" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}"></i></a>
					{/if}
				</span>
			</div>
		</div>
	</div>
{/foreach}
{/strip}