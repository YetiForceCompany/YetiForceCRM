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
	<!-- tpl-Edit-Field-DocumentsFileUpload -->
	{if !empty($RECORD_STRUCTURE['LBL_FILE_INFORMATION']['filelocationtype'])}
		{assign var=FILE_LOCATION_TYPE_FIELD value=$RECORD_STRUCTURE['LBL_FILE_INFORMATION']['filelocationtype']}
	{else}
		{assign var=DOCUMENTS_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Documents')}
		{assign var=FILE_LOCATION_TYPE_FIELD value=$DOCUMENTS_MODULE_MODEL->getField('filelocationtype')}
	{/if}
	{assign var=IS_INTERNAL_LOCATION_TYPE value=$FILE_LOCATION_TYPE_FIELD->get('fieldvalue') neq 'E'}
	{assign var=IS_EXTERNAL_LOCATION_TYPE value=$FILE_LOCATION_TYPE_FIELD->get('fieldvalue') eq 'E'}

	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	{assign var=RAW_FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
	{if $IS_INTERNAL_LOCATION_TYPE}
		{$RAW_FIELD_INFO['type'] = 'file'}
	{else}
		{$RAW_FIELD_INFO['type'] = 'url'}
	{/if}
	{assign var=FIELD_INFO value=\App\Json::encode($RAW_FIELD_INFO)}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	<div class="fileUploadContainer">
		{if $IS_EXTERNAL_LOCATION_TYPE}
			<input type="text" class="form-control{if $FIELD_MODEL->isNameField()} nameField{/if}"
				data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
				name="{$FIELD_MODEL->getFieldName()}" tabindex="{$FIELD_MODEL->getTabIndex()}" value="{if $IS_EXTERNAL_LOCATION_TYPE} {$FIELD_VALUE} {/if}" data-fieldinfo='{$FIELD_INFO}'
				{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} />
		{else}
			<input type="file" class="{if $FIELD_MODEL->isNameField()}nameField{/if}"
				data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
				name="{$FIELD_MODEL->getFieldName()}" tabindex="{$FIELD_MODEL->getTabIndex()}" value="{if $IS_INTERNAL_LOCATION_TYPE} {$FIELD_VALUE} {/if}" data-fieldinfo='{$FIELD_INFO}'
				{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} />
		{/if}
		<div class="uploadedFileDetails {if $IS_EXTERNAL_LOCATION_TYPE}d-none{/if}">
			<div class="uploadedFileSize"></div>
			<div class="uploadedFileName">
				{if $IS_INTERNAL_LOCATION_TYPE && !empty($FIELD_VALUE)}
					[{$FIELD_VALUE}]
				{/if}
			</div>
			<div class="uploadFileSizeLimit redColor">
				{\App\Language::translate('LBL_MAX_UPLOAD_SIZE',$MODULE)}&nbsp;{App\Config::getMaxUploadSize(true, true)}{\App\Language::translate('MB',$MODULE)}
			</div>
		</div>
	</div>
	<!-- /tpl-Edit-Field-DocumentsFileUpload -->
{/strip}
