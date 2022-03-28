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
	<div class="tpl-Base-AdvanceFilterCondition js-conditions-row d-flex justify-content-between bg-light p-1 rounded mb-2 "
		data-js="container | clone">
		<label class="sr-only">{\App\Language::translate('LBL_SELECT_FIELD',$MODULE)}</label>
		<div class="w-25">
			<select class="{if empty($NOCHOSEN)}select2{/if} form-control mr-sm-2" name="columnname"
				title="{\App\Language::translate('LBL_CHOOSE_FIELD')}">
				<option value="none">{\App\Language::translate('LBL_SELECT_FIELD',$MODULE)}</option>
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
									{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldType()}
									{assign var=SELECTED_FIELD_MODEL value=$FIELD_MODEL}
									{if $FIELD_MODEL->getFieldDataType() == 'reference'}
										{$FIELD_TYPE='V'}
									{/if}
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
								data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}"
								{if !empty($SPECIAL_VALIDATOR)}data-validator="{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}" {/if}>
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
		{if empty($CONDITION_INFO['comparator'])}
			{assign var=COMPARATOR_VALUE value=''}
		{else}
			{assign var=COMPARATOR_VALUE value=$CONDITION_INFO['comparator']}
		{/if}
		<input class="form-control" type="hidden" name="comparatorValue" value="{$COMPARATOR_VALUE}">
		{assign var=ADVANCE_FILTER_OPTIONS value=null}
		{if !empty($SELECTED_FIELD_MODEL)}
			{if !$FIELD_TYPE}
				{assign var=FIELD_TYPE value=$SELECTED_FIELD_MODEL->getFieldDataType()}
			{/if}
			{assign var=ADVANCE_FILTER_OPTIONS value=$ADVANCED_FILTER_OPTIONS_BY_TYPE[$FIELD_TYPE]}
			{if in_array($SELECTED_FIELD_MODEL->getFieldType(),['D','DT'])}
				{assign var=DATE_FILTER_CONDITIONS value=array_keys($DATE_FILTERS)}
				{assign var=ADVANCE_FILTER_OPTIONS value=array_merge($ADVANCE_FILTER_OPTIONS,$DATE_FILTER_CONDITIONS)}
			{/if}
		{/if}
		<div class="w-25">
			<select class="{if empty($NOCHOSEN)}select2{/if} form-control" name="comparator"
				title="{\App\Language::translate('LBL_COMAPARATOR_TYPE')}">
				<option value="none">{\App\Language::translate('LBL_NONE',$MODULE)}</option>
				{if !empty($ADVANCE_FILTER_OPTIONS)}
					{foreach item=ADVANCE_FILTER_OPTION from=$ADVANCE_FILTER_OPTIONS}
						<option value="{$ADVANCE_FILTER_OPTION}"
							{if !empty($CONDITION_INFO['comparator']) && $ADVANCE_FILTER_OPTION eq $CONDITION_INFO['comparator']}selected{/if}>{\App\Language::translate($ADVANCED_FILTER_OPTIONS[$ADVANCE_FILTER_OPTION])}</option>
					{/foreach}
				{/if}
			</select>
		</div>
		<div class="fieldUiHolder w-25">
			{if empty($CONDITION_INFO['value'])}
				{assign var=CONDITION_VALUE value=''}
			{else}
				{assign var=CONDITION_VALUE value=$CONDITION_INFO['value']|escape}
			{/if}
			<input class="form-control mr-auto"
				name="{if !empty($SELECTED_FIELD_MODEL)}{$SELECTED_FIELD_MODEL->get('name')}{/if}"
				title="{\App\Language::translate('LBL_COMPARISON_VALUE')}" data-value="value" type="text"
				value="{if !empty($CONDITION_INFO['value'])}{$CONDITION_INFO['value']|escape}{/if}" />
		</div>
		<button type="button" class="btn btn-danger js-condition-delete" data-js="click">
			<span class="fas fa-trash-alt" title="{\App\Language::translate('LBL_DELETE', $MODULE)}"></span>
		</button>
		<span class="d-none">
			{if empty($CONDITION)}
				{assign var=CONDITION value="and"}
			{/if}
			<input type="hidden" name="column_condition" value="{$CONDITION}" />
		</span>
	</div>
{/strip}
