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
	{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
<div class="tpl-Edit-Field-Image">
	<input type="file" class="input-large {if $MODULE eq 'Products'}multi" title="{\App\Language::translate('LBL_SELECT_FILE')}" maxlength="6"{else}"{/if} name="{$FIELD_MODEL->getFieldName()}[]" value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}"
		   data-validation-engine="validate[{if ($FIELD_MODEL->isMandatory() eq true) and (empty($IMAGE_DETAILS))} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
		   data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if} />
	{if $MODULE eq 'Products'}<div id="MultiFile1_wrap_list" class="MultiFile-list"></div>{/if}

	{foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
		<div class="row">
			{if !empty($IMAGE_INFO.path) && !empty({$IMAGE_INFO.orgname})}
				<span class="col-md-8" name="existingImages"><img src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE_INFO.path))}" alt="{$IMAGE_INFO.orgname}" data-image-id="{$IMAGE_INFO.id}" class="img-fluid"></span>
				<span class="col-md-12">
					<span class="">[{$IMAGE_INFO.name}]</span>&nbsp;
					<span class=""><input type="button" id="file_{$ITER}" value="{\App\Language::translate('LBL_DELETE', $MODULE)}" class="imageDelete"></span>
				</span>
			{/if}
		</div>
	{/foreach}
</div>
{/strip}
