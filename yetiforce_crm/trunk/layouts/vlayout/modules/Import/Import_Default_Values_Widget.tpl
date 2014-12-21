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

<div style="visibility: hidden; height: 0px;" id="defaultValuesElementsContainer">
	{foreach key=_FIELD_NAME item=_FIELD_INFO from=$AVAILABLE_FIELDS}
	<span id="{$_FIELD_NAME}_defaultvalue_container" name="{$_FIELD_NAME}_defaultvalue" class="small span11">
		{assign var="_FIELD_TYPE" value=$_FIELD_INFO->getFieldDataType()}
		{if $_FIELD_TYPE eq 'picklist' || $_FIELD_TYPE eq 'multipicklist'}
			<select id="{$_FIELD_NAME}_defaultvalue" name="{$_FIELD_NAME}_defaultvalue" class="small chzn-select">
            {if $_FIELD_NAME neq 'hdnTaxType'} <option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option> {/if}
			{foreach item=_PICKLIST_DETAILS from=$_FIELD_INFO->getPicklistDetails()}
				<option value="{$_PICKLIST_DETAILS.value}">{$_PICKLIST_DETAILS.label|@vtranslate:$FOR_MODULE}</option>
			{/foreach}
			</select>
		{elseif $_FIELD_TYPE eq 'integer'}
			<input type="text" id="{$_FIELD_NAME}_defaultvalue" name="{$_FIELD_NAME}_defaultvalue" class="small defaultInputTextContainer" value="0" />
		{elseif $_FIELD_TYPE eq 'owner' || $_FIELD_INFO->getUIType() eq '52'}
			<select id="{$_FIELD_NAME}_defaultvalue" name="{$_FIELD_NAME}_defaultvalue" class="small chzn-select">
				<option value="">--{'LBL_NONE'|@vtranslate:$FOR_MODULE}--</option>
			{foreach key=_ID item=_NAME from=$USERS_LIST}
				<option value="{$_ID}">{$_NAME}</option>
			{/foreach}
			{if $_FIELD_INFO->getUIType() eq '53'}
				{foreach key=_ID item=_NAME from=$GROUPS_LIST}
				<option value="{$_ID}">{$_NAME}</option>
				{/foreach}
			{/if}
			</select>
		{elseif $_FIELD_TYPE eq 'date'}
			<input type="text" id="{$_FIELD_NAME}_defaultvalue" name="{$_FIELD_NAME}_defaultvalue"
					data-date-format="{$DATE_FORMAT}" class="defaultInputTextContainer span2 datepicker" value="" />
		{elseif $_FIELD_TYPE eq 'datetime'}
				<input type="text" id="{$_FIELD_NAME}_defaultvalue" name="{$_FIELD_NAME}_defaultvalue"
					   class="defaultInputTextContainer small span2" value="" data-date-format="{$DATE_FORMAT}"/>
		{elseif $_FIELD_TYPE eq 'boolean'}
			<input type="checkbox" id="{$_FIELD_NAME}_defaultvalue" name="{$_FIELD_NAME}_defaultvalue" class="small" />
		{elseif $_FIELD_TYPE neq 'reference'}
			<input type="input" id="{$_FIELD_NAME}_defaultvalue" name="{$_FIELD_NAME}_defaultvalue" class="defaultInputTextContainer small" />
		{/if}
		</span>
	{/foreach}
</div>
<script type="text/javascript">
	jQuery(document).ready(function() {
		$('.small .span2').datepicker();
	});
</script>