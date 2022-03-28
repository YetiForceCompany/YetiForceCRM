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
{strip}
	{if !$USER_MODEL}
		{assign var=USER_MODEL value = Users_Record_Model::getCurrentUserModel()}
	{/if}
	<div class="tpl-Settings-Base-AdvanceFilterCondition js-conditions-row form-row" data-js="container | clone">
		<div class="col-md-4 conditionField">
			<select class="{if empty($NOCHOSEN)}select2{/if} row form-control m-0" name="columnname"
				title="{\App\Language::translate('LBL_CHOOSE_FIELD')}">
				{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
					<optgroup label='{\App\Language::translate($BLOCK_LABEL, $SOURCE_MODULE)}'>
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
							{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
							{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
							{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
							{if !empty($COLUMNNAME_API)}
								{assign var=columnNameApi value=$COLUMNNAME_API}
							{else}
								{assign var=columnNameApi value=getCustomViewColumnName}
							{/if}
							<option value="{$FIELD_MODEL->$columnNameApi()}"
								data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_NAME}"
								{if isset($CONDITION_INFO['columnname']) && App\Purifier::decodeHtml($FIELD_MODEL->$columnNameApi()) eq App\Purifier::decodeHtml($CONDITION_INFO['columnname'])}
									{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldDataType()}
									{assign var=SELECTED_FIELD_MODEL value=$FIELD_MODEL}
									{$FIELD_INFO['value'] = App\Purifier::decodeHtml($CONDITION_INFO['value'])}
									selected="selected"
								{/if}
								{if $FIELD_MODEL->getFieldDataType() eq 'reference'}
									{assign var=referenceList value=$FIELD_MODEL->getReferenceList()}
									{if is_array($referenceList) && in_array('Users', $referenceList)}
										{assign var=USERSLIST value=[]}
										{assign var=ACCESSIBLE_USERS value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
										{foreach item=USER_NAME from=$ACCESSIBLE_USERS}
											{$USERSLIST[$USER_NAME] = $USER_NAME}
										{/foreach}
										{$FIELD_INFO['picklistvalues'] = $USERSLIST}
										{$FIELD_INFO['type'] = 'picklist'}
									{/if}
								{/if}
								data-fieldinfo='{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}'
								{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}>
								{if $SOURCE_MODULE neq $MODULE_MODEL->get('name')}
									({\App\Language::translate($MODULE_MODEL->get('name'), $MODULE_MODEL->get('name'))}) - {\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_MODEL->get('name'))} ({\App\Language::translate($FIELD_MODEL->getBlockName(), $MODULE_MODEL->get('name'))})
								{else}
									{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}
								{/if}
							</option>
						{/foreach}
					</optgroup>
				{/foreach}
			</select>
		</div>
		<div class="col-md-3">
			<input type="hidden" name="comparatorValue"
				value="{if !empty($CONDITION_INFO['comparator'])}{$CONDITION_INFO['comparator']}{/if}">
			{if !empty($SELECTED_FIELD_MODEL)}
				{if empty($FIELD_TYPE)}
					{assign var=FIELD_TYPE value=$SELECTED_FIELD_MODEL->getFieldDataType()}
				{/if}
				{if !empty($ADVANCED_FILTER_OPTIONS_BY_TYPE[$FIELD_TYPE])}
					{assign var=ADVANCE_FILTER_OPTIONS value=$ADVANCED_FILTER_OPTIONS_BY_TYPE[$FIELD_TYPE]}
				{else}
					{assign var=ADVANCE_FILTER_OPTIONS value=[]}
				{/if}
				{if in_array($SELECTED_FIELD_MODEL->getFieldType(),['D','DT'])}
					{assign var=DATE_FILTER_CONDITIONS value=array_keys($DATE_FILTERS)}
					{assign var=ADVANCE_FILTER_OPTIONS value=array_merge($ADVANCE_FILTER_OPTIONS,$DATE_FILTER_CONDITIONS)}
				{/if}
			{/if}
			<select class="{if empty($NOCHOSEN)}select2{/if} row form-control m-0" name="comparator"
				title="{\App\Language::translate('LBL_COMAPARATOR_TYPE')}">
				{if !empty($ADVANCE_FILTER_OPTIONS)}
					{foreach item=ADVANCE_FILTER_OPTION from=$ADVANCE_FILTER_OPTIONS}
						<option value="{$ADVANCE_FILTER_OPTION}"
							{if $ADVANCE_FILTER_OPTION eq $CONDITION_INFO['comparator']}selected{/if}>
							{\App\Language::translate($ADVANCED_FILTER_OPTIONS[$ADVANCE_FILTER_OPTION])}
						</option>
					{/foreach}
				{/if}
			</select>
		</div>
		<div class="col-md-4 fieldUiHolder">
			<input name="{if !empty($SELECTED_FIELD_MODEL)}{$SELECTED_FIELD_MODEL->get('name')}{/if}" title="{\App\Language::translate('LBL_COMPARISON_VALUE')}" data-value="value" class="form-control" type="text" value="{if !empty($CONDITION_INFO['value'])}{$CONDITION_INFO['value']|escape}{/if}" {if !empty($CONDITION_INFO['valuetype'])} data-valuetype="{$CONDITION_INFO['valuetype']}" {/if} />
		</div>
		<span class="d-none">
			{if empty($CONDITION)}
				{assign var=CONDITION value="and"}
			{/if}
			<input type="hidden" name="column_condition" value="{$CONDITION}" />
		</span>
		<button type="button" class="btn btn-danger js-condition-delete float-right float-xl-left" data-js="click">
			<span class="fas fa-trash-alt"
				title="{\App\Language::translate('LBL_DELETE', $MODULE)}"></span>
		</button>
	</div>
{/strip}
