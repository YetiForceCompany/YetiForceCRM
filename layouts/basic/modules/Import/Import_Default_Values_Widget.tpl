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

<div class="d-none" id="defaultValuesElementsContainer">
	{foreach key=BLOCK_NAME item=_FIELDS from=$AVAILABLE_BLOCKS}
		{foreach key=_FIELD_NAME item=_FIELD_INFO from=$_FIELDS}
			<div id="{$_FIELD_NAME}_defaultvalue_container" name="{$_FIELD_NAME}_defaultvalue" class="small col-md-11">
				{assign var="_FIELD_TYPE" value=$_FIELD_INFO->getFieldDataType()}
				{if $_FIELD_TYPE eq 'picklist' || $_FIELD_TYPE eq 'multipicklist'}
					<select id="{$_FIELD_NAME}_defaultvalue" name="{$_FIELD_NAME}_defaultvalue" class="small select2">
						<option value="">{\App\Language::translate('LBL_SELECT_OPTION','Vtiger')}</option>
						{foreach item=LABEL key=VALUE from=$_FIELD_INFO->getPicklistValues()}
							<option value="{\App\Purifier::encodeHtml($VALUE)}">{\App\Purifier::encodeHtml($LABEL)}</option>
						{/foreach}
					</select>
				{elseif $_FIELD_TYPE eq 'integer'}
					<input type="text" id="{$_FIELD_NAME}_defaultvalue" name="{$_FIELD_NAME}_defaultvalue" class="small defaultInputTextContainer form-control" value="" />
				{elseif $_FIELD_TYPE eq 'owner' || $_FIELD_INFO->getUIType() eq '52'}
					<select id="{$_FIELD_NAME}_defaultvalue" name="{$_FIELD_NAME}_defaultvalue" class="small select2">
						<option value="">--{\App\Language::translate('LBL_NONE', $FOR_MODULE)}--</option>
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
						data-date-format="{$DATE_FORMAT}" class="defaultInputTextContainer form-control col-md-2 dateField" value="" />
				{elseif $_FIELD_TYPE eq 'datetime'}
					<input type="text" id="{$_FIELD_NAME}_defaultvalue" name="{$_FIELD_NAME}_defaultvalue"
						class="defaultInputTextContainer form-control small col-md-2" value="" data-date-format="{$DATE_FORMAT}" />
				{elseif $_FIELD_TYPE eq 'boolean'}
					<input type="checkbox" id="{$_FIELD_NAME}_defaultvalue" name="{$_FIELD_NAME}_defaultvalue" class="small" />
				{elseif $_FIELD_TYPE eq 'reference'}
					<select id="{$_FIELD_NAME}_defaultvalue" name="{$_FIELD_NAME}_defaultvalue" class="small select2">
						{foreach item=_REFERENCE_DETAILS from=$_FIELDS[$_FIELD_NAME]->getReferenceList()}
							{assign var="REFERENCE_MODULE" value=Vtiger_Module_Model::getInstance($_REFERENCE_DETAILS)}
							<option value="{$_REFERENCE_DETAILS}">{\App\Language::translate($_REFERENCE_DETAILS, $_REFERENCE_DETAILS)}</option>
							{if $REFERENCE_MODULE && \App\Privilege::isPermitted($_REFERENCE_DETAILS)}
								<option value="{$_REFERENCE_DETAILS}::id">
									{\App\Language::translate($_REFERENCE_DETAILS, $_REFERENCE_DETAILS)}: {\App\Language::translate('LBL_SELF_ID', $_REFERENCE_DETAILS)}
								</option>
								{foreach item=REFERENCE_FIELD from=$REFERENCE_MODULE->getFieldsByType(['string', 'recordNumber'], true)}
									<option value="{$_REFERENCE_DETAILS}::{$REFERENCE_FIELD->getName()}">
										{\App\Language::translate($_REFERENCE_DETAILS, $_REFERENCE_DETAILS)}: {\App\Language::translate($REFERENCE_FIELD->getFieldLabel(), $_REFERENCE_DETAILS)}
									</option>
								{/foreach}
							{/if}
						{/foreach}
					</select>
				{elseif $_FIELD_TYPE neq 'reference'}
					<input type="input" id="{$_FIELD_NAME}_defaultvalue" name="{$_FIELD_NAME}_defaultvalue" class="defaultInputTextContainer form-control small" />
				{/if}
			</div>
		{/foreach}
	{/foreach}
</div>
