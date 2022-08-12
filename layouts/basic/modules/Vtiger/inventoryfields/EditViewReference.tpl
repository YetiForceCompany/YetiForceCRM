{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewReference -->
	{assign var="REFERENCE_LIST" value=$FIELD->getReferenceModules()}
	{assign var=FIELD_NAME value="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]"}
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode(['mandatory'=>true]))}
	{assign var="REFERENCE_LIST_COUNT" value=count($REFERENCE_LIST)}
	<div class="invUitype_{$MODULE}_{$FIELD_NAME} input-group input-group-sm referenceGroup">
		{if $REFERENCE_LIST_COUNT eq 1}
			{assign var="REFERENCED_MODULE_NAME" value=reset($REFERENCE_LIST)}
			<input name="popupReferenceModule" type="hidden" data-multi-reference="0" title="{reset($REFERENCE_LIST)}"
				value="{reset($REFERENCE_LIST)}" />
		{/if}
		{if $REFERENCE_LIST_COUNT gt 1}
			{assign var="REFERENCED_MODULE_NAME" value=$FIELD->getReferenceModule($ITEM_VALUE)}
			{if in_array($REFERENCED_MODULE_NAME, $REFERENCE_LIST)}
				<input name="popupReferenceModule" type="hidden" data-multi-reference="1"
					value="{$REFERENCED_MODULE_NAME}" />
			{else}
				{assign var="REFERENCED_MODULE_NAME" value=$REFERENCE_LIST[0]}
				<input name="popupReferenceModule" type="hidden" data-multi-reference="1"
					value="{$REFERENCED_MODULE_NAME}" />
			{/if}
		{/if}
		{if $REFERENCE_LIST_COUNT > 1}
			<div class="input-group-prepend referenceModulesListGroup">
				<select class="referenceModulesList" title="{\App\Language::translate('LBL_RELATED_MODULE_TYPE')}"
					required="required">
					{foreach key=index item=REFERENCE from=$REFERENCE_LIST}
						<option value="{$REFERENCE}"
							title="{\App\Language::translate($REFERENCE, $REFERENCE)}" {if $REFERENCE eq $REFERENCED_MODULE_NAME} selected {/if}>{\App\Language::translate($REFERENCE, $REFERENCE)}</option>
					{/foreach}
				</select>
			</div>
		{/if}
		<input id="{$FIELD_NAME}_display" name="{$FIELD_NAME}_display" type="text"
			title="{\App\Purifier::encodeHtml($FIELD->getEditValue($ITEM_VALUE))}"
			class="form-control autoComplete" {if !empty($ITEM_VALUE)}readonly="true" {/if}
			value="{\App\Purifier::encodeHtml($FIELD->getEditValue($ITEM_VALUE))}"
			data-validation-engine="validate[{if $FIELD->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			data-fieldinfo="{$FIELD_INFO}"
			{if $FIELD->isReadOnly()} readonly="readonly" {else} placeholder="{\App\Language::translate('LBL_TYPE_SEARCH',$MODULE_NAME)}" {/if} />
		<div class="input-group-append u-cursor-pointer">
			{if !$FIELD->isReadOnly()}
				<button class="btn btn-light clearReferenceSelection" type="button">
					<span class="fas fa-times-circle" title="{\App\Language::translate('LBL_CLEAR', $MODULE)}"></span>
				</button>
				<button class="btn btn-light relatedPopup" type="button">
					<span class="fas fa-search" title="{\App\Language::translate('LBL_SELECT', $MODULE)}"></span>
				</button>
				{assign var=REFERENCE_MODULE_MODEL value=Vtiger_Module_Model::getInstance($REFERENCED_MODULE_NAME)}
				<!-- Show the add button only if it is edit view  -->
				{if $VIEW eq 'Edit' && $REFERENCE_MODULE_MODEL->isQuickCreateSupported()}
					<button class="btn btn-light createReferenceRecord" type="button">
						<span class="fas fa-plus" title="{\App\Language::translate('LBL_CREATE', $MODULE)}"></span>
					</button>
				{/if}
			{/if}
		</div>
		<input name="{$FIELD_NAME}" type="hidden" value="{$ITEM_VALUE}" title="{$ITEM_VALUE}" class="sourceField"
			data-type="inventory" data-columnname="{$FIELD->getColumnName()}" data-fieldinfo='{$FIELD_INFO}'
			{if $FIELD->isReadOnly()}readonly="readonly" {/if} />
	</div>
	<!-- /tpl-Base-inventoryfields-EditViewReference -->
{/strip}
