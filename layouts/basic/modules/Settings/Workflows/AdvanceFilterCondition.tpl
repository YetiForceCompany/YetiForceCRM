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
	<div class="row js-conditions-row marginBottom10px align-items-center" data-js="container | clone">
		<div class="col-md-4">
			<select class="{if empty($NOCHOSEN)}select2{/if} form-control" name="columnname"
				data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
				{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
					<optgroup label='{\App\Language::translate($BLOCK_LABEL, $SELECTED_MODULE_NAME)}'>
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
							{if in_array( $FIELD_MODEL->getFieldDataType(), $SKIPPED_FIELD_DATA_TYPES)}
								{continue}
							{/if}
							{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
							{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
							{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
							{if !empty($COLUMNNAME_API)}
								{assign var=columnNameApi value=$COLUMNNAME_API}
							{else}
								{assign var=columnNameApi value=getCustomViewColumnName}
							{/if}
							{if isset($CONDITION_INFO['value'])}
								{assign var=FIELD_VALUE value=$CONDITION_INFO['value']}
							{else}
								{assign var=FIELD_VALUE value=""}
							{/if}
							<option value="{$FIELD_MODEL->$columnNameApi()}"
								data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_NAME}"
								{if !empty($CONDITION_INFO['columnname']) && App\Purifier::decodeHtml($FIELD_MODEL->$columnNameApi()) eq $CONDITION_INFO['columnname']}
									{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldDataType()}
									{assign var=SELECTED_FIELD_MODEL value=$FIELD_MODEL}
									{$FIELD_INFO['value'] = App\Purifier::decodeHtml($FIELD_VALUE)}
									selected="selected"
								{/if}
								{if in_array($FIELD_MODEL->get('uitype'), [302,309])}
									{$FIELD_INFO['treetemplate'] = App\Purifier::decodeHtml($FIELD_MODEL->getFieldParams())}
								{/if}
								data-fieldinfo='{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}'
								{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}>
								{if $SELECTED_MODULE_NAME neq $MODULE_MODEL->get('name')}
									({\App\Language::translate($MODULE_MODEL->get('name'), $MODULE_MODEL->get('name'))}) - {\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_MODEL->get('name'))} ({\App\Language::translate($FIELD_MODEL->getBlockName(), $MODULE_MODEL->get('name'))})
								{else}
									{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SELECTED_MODULE_NAME)}
								{/if}
							</option>
						{/foreach}
					</optgroup>
				{/foreach}
			</select>
		</div>
		<div class="col-md-3">
			<select class="{if empty($NOCHOSEN)}select2{/if} form-control" name="comparator">
				{if !empty($FIELD_TYPE)}
					{assign var=ADVANCE_FILTER_OPTIONS value=$ADVANCED_FILTER_OPTIONS_BY_TYPE[$FIELD_TYPE]}
				{else}
					{assign var=ADVANCE_FILTER_OPTIONS value=''}
				{/if}
				{foreach item=ADVANCE_FILTER_OPTION from=$ADVANCE_FILTER_OPTIONS}
					<option value="{$ADVANCE_FILTER_OPTION}"
						{if !empty($CONDITION_INFO['comparator']) && $ADVANCE_FILTER_OPTION eq $CONDITION_INFO['comparator']}
							selected
							{/if}>{if !empty($ADVANCED_FILTER_OPTIONS[$ADVANCE_FILTER_OPTION])}{\App\Language::translate($ADVANCED_FILTER_OPTIONS[$ADVANCE_FILTER_OPTION])}{/if}</option>
					{/foreach}
				</select>
			</div>
			<div class="col-md-4 fieldUiHolder">
				<input name="{if !empty($SELECTED_FIELD_MODEL)}{$SELECTED_FIELD_MODEL->get('name')}{/if}" data-value="value"
					class="form-control" type="text"
					value="{if !empty($CONDITION_INFO['value'])}{$CONDITION_INFO['value']|escape}{/if}" />
			</div>
			<span class="d-none">
				{if empty($CONDITION)}
					{assign var=CONDITION value="and"}
				{/if}
				<input type="hidden" name="column_condition" value="{$CONDITION}" />
			</span>
			<span class="col-md-1">
				<button class="btn btn-danger js-condition-delete" type="button" data-js="click">
					<span class="fas fa-trash-alt" title="{\App\Language::translate('LBL_DELETE', $MODULE)}"></span>
				</button>
			</span>
		</div>
	{/strip}
