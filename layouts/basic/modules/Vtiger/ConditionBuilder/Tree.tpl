{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-ConditionBuilder-Tree -->
	{assign var="WIDTHTYPE_GROUP" value='input-group-sm'}
	{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{if isset($VALUE)}
		{assign var="FIELD_VALUES" value=str_replace('##',',',$VALUE)}
	{else}
		{assign var="FIELD_VALUES" value=''}
	{/if}
	{assign var="DISPLAY_VALUE" value=\App\Purifier::encodeHtml($FIELD_MODEL->getDisplayValue($FIELD_VALUES, false, false, true))}
	<div class="js-tree-container fieldValue" data-js="container">
		<input name="{$FIELD_MODEL->getFieldName()}" type="hidden" value="{$FIELD_VALUES}" class="sourceField js-condition-builder-value"
			data-fieldinfo='{$FIELD_INFO}'
			data-multiple="{if $FIELD_MODEL->getFieldDataType() !== 'tree'}1{else}0{/if}"
			data-treetemplate="{$FIELD_MODEL->getFieldParams()}" data-module-name="{$FIELD_MODEL->getModuleName()}">
		{assign var="displayId" value=$FIELD_MODEL->get('fieldvalue')}
		<div class="input-group {$WIDTHTYPE_GROUP}">
			{if $FIELD_MODEL->get('displaytype') != 10}
				<span class="input-group-prepend clearTreeSelection u-cursor-pointer">
					<span class="input-group-text">
						<span id="{$MODULE_NAME}_editView_fieldName_{$FIELD_NAME}_clear" class='fas fa-times-circle' title="{\App\Language::translate('LBL_CLEAR', $MODULE_NAME)}"></span>
					</span>
				</span>
			{/if}
			<input id="{$FIELD_NAME}_display" name="{$FIELD_MODEL->getFieldName()}_display" type="text"
				class="ml-0 treeAutoComplete form-control" {if !empty($DISPLAY_VALUE)}readonly="true" {/if}
				value="{$DISPLAY_VALUE}" tabindex="{$FIELD_MODEL->getTabIndex()}"
				data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
				data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->get('displaytype') != 10}placeholder="{\App\Language::translate('LBL_TYPE_SEARCH',$MODULE_NAME)}" {/if}
				{if !empty($SPECIAL_VALIDATOR)}data-validator="{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}" {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if} />
			{if $FIELD_MODEL->get('displaytype') != 10}
				<span class="input-group-append js-tree-modal u-cursor-pointer">
					<span class="input-group-text">
						<span id="{$MODULE_NAME}_editView_fieldName_{$FIELD_NAME}_select" class="fas fa-search" title="{\App\Language::translate('LBL_SELECT', $MODULE_NAME)}"></span>
					</span>
				</span>
			{/if}
		</div>
	</div>
	<!-- /tpl-Base-ConditionBuilder-Tree -->
{/strip}
