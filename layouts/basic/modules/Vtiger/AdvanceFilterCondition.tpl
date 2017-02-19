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
	{if !$USER_MODEL}
		{assign var=USER_MODEL value = Users_Record_Model::getCurrentUserModel()}
	{/if}
	<div class="conditionRow">
		<div class="col-md-4 conditionField">
			<select class="{if empty($NOCHOSEN)}chzn-select{/if} row form-control margin0px" name="columnname" title="{vtranslate('LBL_CHOOSE_FIELD')}">
				<option value="none">{vtranslate('LBL_SELECT_FIELD',$MODULE)}</option>
				{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
					<optgroup label='{vtranslate($BLOCK_LABEL, $SOURCE_MODULE)}'>
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
							{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
							{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
							{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
							{if !empty($COLUMNNAME_API)}
								{assign var=columnNameApi value=$COLUMNNAME_API}
							{else}
								{assign var=columnNameApi value=getCustomViewColumnName}
							{/if}
							<option value="{$FIELD_MODEL->$columnNameApi()}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_NAME}"
									{if isset($CONDITION_INFO['columnname']) && decode_html($FIELD_MODEL->$columnNameApi()) eq decode_html($CONDITION_INFO['columnname'])}
										{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldType()}
										{assign var=SELECTED_FIELD_MODEL value=$FIELD_MODEL}
										{if $FIELD_MODEL->getFieldDataType() == 'reference'}
											{$FIELD_TYPE='V'}
										{/if}
										{$FIELD_INFO['value'] = decode_html($CONDITION_INFO['value'])}
										selected="selected"
									{/if}
									{if ($MODULE_MODEL->get('name') eq 'Calendar') && ($FIELD_NAME eq 'activitytype')}
										{$FIELD_INFO['picklistvalues']['Task'] = vtranslate('Task', 'Calendar')}
									{/if}
									{if $FIELD_MODEL->getFieldDataType() eq 'reference'}
										{assign var=referenceList value=$FIELD_MODEL->getWebserviceFieldObject()->getReferenceList()}
										{if is_array($referenceList) && in_array('Users', $referenceList)}
											{assign var=USERSLIST value=array()}
											{assign var=ACCESSIBLE_USERS value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
											{foreach item=USER_NAME from=$ACCESSIBLE_USERS}
												{$USERSLIST[$USER_NAME] = $USER_NAME}
											{/foreach}
											{$FIELD_INFO['picklistvalues'] = $USERSLIST}
											{$FIELD_INFO['type'] = 'picklist'}
										{/if}
									{/if}
									data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_INFO))}' 
									{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if}>
								{if $SOURCE_MODULE neq $MODULE_MODEL->get('name')}
									({vtranslate($MODULE_MODEL->get('name'), $MODULE_MODEL->get('name'))})  {vtranslate($FIELD_MODEL->get('label'), $MODULE_MODEL->get('name'))}
								{else}
									{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
								{/if}
							</option>
						{/foreach}
					</optgroup>
				{/foreach}
				{* Required to display event fields also while adding conditions *}
				{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$EVENT_RECORD_STRUCTURE}
					<optgroup label='{vtranslate($BLOCK_LABEL, 'Events')}'>
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
							{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
							{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
							{if !empty($COLUMNNAME_API)}
								{assign var=columnNameApi value=$COLUMNNAME_API}
							{else}
								{assign var=columnNameApi value=getCustomViewColumnName}
							{/if}
							<option value="{$FIELD_MODEL->$columnNameApi()}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_NAME}"
									{if isset($CONDITION_INFO['columnname']) && decode_html($FIELD_MODEL->$columnNameApi()) eq $CONDITION_INFO['columnname']}
										{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldType()}
										{assign var=SELECTED_FIELD_MODEL value=$FIELD_MODEL}
										{if $FIELD_MODEL->getFieldDataType() == 'reference'}
											{$FIELD_TYPE='V'}
										{/if}
										{$FIELD_INFO['value'] = decode_html($CONDITION_INFO['value'])}
										selected="selected"
									{/if}
									{if $FIELD_MODEL->getFieldDataType() eq 'reference'}
										{assign var=referenceList value=$FIELD_MODEL->getWebserviceFieldObject()->getReferenceList()}
										{if is_array($referenceList) && in_array('Users', $referenceList)}
											{assign var=USERSLIST value=array()}
											{assign var=ACCESSIBLE_USERS value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
											{foreach item=USER_NAME from=$ACCESSIBLE_USERS}
												{$USERSLIST[$USER_NAME] = $USER_NAME}
											{/foreach}
											{$FIELD_INFO['picklistvalues'] = $USERSLIST}
											{$FIELD_INFO['type'] = 'picklist'}
										{/if}
									{/if}
									data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_INFO))}' >
								{if $SOURCE_MODULE neq $MODULE_MODEL->get('name')}
									({vtranslate($MODULE_MODEL->get('name'), $MODULE_MODEL->get('name'))})  {vtranslate($FIELD_MODEL->get('label'), $MODULE_MODEL->get('name'))}
								{else}
									{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
								{/if}
							</option>
						{/foreach}
					</optgroup>
				{/foreach}
			</select>
		</div>
		<div class="col-md-3">
			<input type="hidden" name="comparatorValue" value="{$CONDITION_INFO['comparator']}">
			{if $SELECTED_FIELD_MODEL}
				{if !$FIELD_TYPE}
					{assign var=FIELD_TYPE value=$SELECTED_FIELD_MODEL->getFieldDataType()}
				{/if}
				{assign var=ADVANCE_FILTER_OPTIONS value=$ADVANCED_FILTER_OPTIONS_BY_TYPE[$FIELD_TYPE]}
				{if in_array($SELECTED_FIELD_MODEL->getFieldType(),['D','DT'])}
					{assign var=DATE_FILTER_CONDITIONS value=array_keys($DATE_FILTERS)}
					{assign var=ADVANCE_FILTER_OPTIONS value=array_merge($ADVANCE_FILTER_OPTIONS,$DATE_FILTER_CONDITIONS)}
				{/if}
			{/if}
			<select class="{if empty($NOCHOSEN)}chzn-select{/if} row form-control margin0px" name="comparator" title="{vtranslate('LBL_COMAPARATOR_TYPE')}">
				<option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
				{foreach item=ADVANCE_FILTER_OPTION from=$ADVANCE_FILTER_OPTIONS}
					<option value="{$ADVANCE_FILTER_OPTION}" {if $ADVANCE_FILTER_OPTION eq $CONDITION_INFO['comparator']}selected{/if}>{vtranslate($ADVANCED_FILTER_OPTIONS[$ADVANCE_FILTER_OPTION])}</option>
				{/foreach}
			</select>
		</div>
		<div class="col-md-4 fieldUiHolder">
			<input name="{if $SELECTED_FIELD_MODEL}{$SELECTED_FIELD_MODEL->get('name')}{/if}" title="{vtranslate('LBL_COMPARISON_VALUE')}" data-value="value" class="form-control" type="text" value="{$CONDITION_INFO['value']|escape}" />
		</div>
		<span class="hide">
			{if empty($CONDITION)}
				{assign var=CONDITION value="and"}
			{/if}
			<input type="hidden" name="column_condition" value="{$CONDITION}" />
		</span>
		<div  class="col-md-1 btn">
			<span class="deleteCondition glyphicon glyphicon-trash alignMiddle" title="{vtranslate('LBL_DELETE', $MODULE)}"></span>
		</div>
	</div>
{/strip}
