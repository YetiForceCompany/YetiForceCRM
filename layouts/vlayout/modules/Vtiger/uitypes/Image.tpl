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
{if $MODULE_NAME eq 'Webforms'}
	<input type="text" readonly="" />
{else}
{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	<input type="file" class="input-large {if $MODULE eq 'Products'}multi" maxlength="6"{else}"{/if} name="{$FIELD_MODEL->getFieldName()}[]" value="{$FIELD_MODEL->get('fieldvalue')}"
		   data-validation-engine="validate[{if ($FIELD_MODEL->isMandatory() eq true) and (empty($IMAGE_DETAILS))} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
	data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if} />
	{if $MODULE eq 'Products'}<div id="MultiFile1_wrap_list" class="MultiFile-list"></div>{/if}

	{foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
		<div class="row-fluid">
			{if !empty($IMAGE_INFO.path) && !empty({$IMAGE_INFO.orgname})}
				<span class="span8" name="existingImages"><img src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" data-image-id="{$IMAGE_INFO.id}"></span>
				<span class="span3 row-fluid">
					<span class="row-fluid">[{$IMAGE_INFO.name}]</span>
					<span class="row-fluid"><input type="button" id="file_{$ITER}" value="Delete" class="imageDelete"></span>
				</span>
			{/if}
		</div>
	{/foreach}
{/if}
{/strip}