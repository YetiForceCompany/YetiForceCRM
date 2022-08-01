{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-MultiReference -->
	{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
	{assign var=REFERENCE_LIST value=$FIELD_MODEL->getReferenceList()}
	{assign var=REFERENCE_MODULE_MODEL value=Vtiger_Module_Model::getInstance($REFERENCE_LIST[0])}
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=TABINDEX value=$FIELD_MODEL->getTabIndex()}
	{assign var=IS_EDITABLE_READ_ONLY value=$FIELD_MODEL->isEditableReadOnly()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD)}
	{assign var=UITYPE_MODEL value=$FIELD_MODEL->getUITypeModel()}
	{assign var=PARAMS value=$FIELD_MODEL->getFieldParams()}
	<div class="uitype_{$MODULE_NAME}_{$FIELD_NAME} js-multiReference-container">
		<div class="input-group referenceGroup">
			<input class="js-popup-reference-module" type="hidden" value="{$REFERENCE_LIST[0]}" />
			<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value="" />
			<select name="{$FIELD_MODEL->getFieldName()}[]" multiple class="js-multi-reference form-control col-md-12" data-allow-clear="true"
				id="{$MODULE_NAME}_{$VIEW}_fieldName_{$FIELD_NAME}" data-module="{$MODULE_NAME}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}"
				data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
				{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}' {/if} data-fieldinfo='{$FIELD_INFO}' tabindex="{$TABINDEX}"
				{if $REFERENCE_MODULE_MODEL == false || $IS_EDITABLE_READ_ONLY} disabled{/if} {if $IS_EDITABLE_READ_ONLY}readonly="readonly" {/if}
				{if $FIELD_MODEL->get('displaytype') != 10}placeholder="{\App\Language::translate('LBL_TYPE_SEARCH',$MODULE_NAME)}" {/if}
				data-maximum-selection-length="{$UITYPE_MODEL->getSelectionLimit()}"
				data-ajax-search="1" data-ajax-url="index.php?module={$MODULE_NAME}&action=BasicAjax&search_module={$REFERENCE_LIST[0]}">
				{if !empty($FIELD_VALUE)}
					{foreach key=PICKLIST_ID item=PICKLIST_VALUE from=$FIELD_VALUE}
						<option value="{$PICKLIST_ID}" selected>{$PICKLIST_VALUE}</option>
					{/foreach}
				{/if}
			</select>
			<div class="input-group-append u-cursor-pointer">
				{if !empty($PARAMS['buttons'])}
					{foreach item=BUTTON from=$PARAMS['buttons']}
						<button class="btn {\App\Purifier::encodeHtml($BUTTON['class'])}" type="button" tabindex="{$TABINDEX}" {if $IS_EDITABLE_READ_ONLY}disabled{/if}
							{if isset($BUTTON['data'])}
								{foreach from=$BUTTON['data'] key=NAME item=DATA}{' '}data-{$NAME}="{\App\Purifier::encodeHtml($DATA)}" {/foreach}
							{/if}>
							<span {if isset($BUTTON['name'])}id="{$MODULE_NAME}_editView_fieldName_{$FIELD_NAME}_{\App\Purifier::encodeHtml($BUTTON['name'])}" {/if} class="{\App\Purifier::encodeHtml($BUTTON['icon'])}" title="{\App\Language::translate($BUTTON['label'], $BUTTON['labelModule'])}"></span>
						</button>
					{/foreach}
				{/if}
				{if empty($PARAMS['hideSelectButton'])}
					<button class="btn btn-light js-related-popup" type="button" tabindex="{$TABINDEX}" {if $REFERENCE_MODULE_MODEL == false || $IS_EDITABLE_READ_ONLY} disabled{/if}>
						<span id="{$MODULE_NAME}_editView_fieldName_{$FIELD_NAME}_select" class="fas fa-search" title="{\App\Language::translate('LBL_SELECT', $MODULE_NAME)}"></span>
					</button>
				{/if}
				{if empty($PARAMS['hideAddButton']) && $REFERENCE_MODULE_MODEL && $REFERENCE_MODULE_MODEL->isQuickCreateSupported()}
					<button class="btn btn-light js-create-reference-record" type="button" tabindex="{$TABINDEX}" {if $IS_EDITABLE_READ_ONLY}disabled{/if}>
						<span id="{$MODULE_NAME}_editView_fieldName_{$FIELD_NAME}_create" class="fas fa-plus" title="{\App\Language::translate('LBL_CREATE', $MODULE_NAME)}"></span>
					</button>
				{/if}
			</div>
		</div>
	</div>
	<!-- /tpl-Base-Edit-Field-MultiReference -->
{/strip}
