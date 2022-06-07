{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Base-Edit-Field-Icon -->
	{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
	{assign var=TABINDEX value=$FIELD_MODEL->getTabIndex()}
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=RAW_VALUE value=$FIELD_MODEL->get('fieldvalue')}
	{assign var=FIELD_VALUE value=\App\Purifier::encodeHtml($FIELD_MODEL->getEditViewDisplayValue($RAW_VALUE,$RECORD))}
	{assign var=IS_EDITABLE_READ_ONLY value=$FIELD_MODEL->isEditableReadOnly()}
	<div class="js-icon-container">
		<input name="{$FIELD_MODEL->getFieldName()}" type="hidden" value="{\App\Purifier::encodeHtml($RAW_VALUE)}" class="js-source-field" data-fieldinfo='{$FIELD_INFO}' />
		<div class="input-group {$WIDTHTYPE_GROUP} ">
			<div class="input-group-prepend">
				<label class="input-group-text js-icon-show p-1 pl-2 pr-2 u-fs-xlg">{$FIELD_MODEL->getDisplayValue($RAW_VALUE)}</label>
			</div>
			<input id="{$FIELD_NAME}_display" name="{$FIELD_MODEL->getFieldName()}_display" type="text" title="{$FIELD_VALUE}" class="form-control"
				tabindex="{$TABINDEX}" disabled="disabled" value="{$FIELD_VALUE}"
				data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
				data-fieldinfo='{$FIELD_INFO}'
				{if !empty($SPECIAL_VALIDATOR)}data-validator="{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}" {/if} />
			<div class="input-group-append u-cursor-pointer">
				<button class="btn btn-light js-clear-selection" type="button" tabindex="{$TABINDEX}" {if $IS_EDITABLE_READ_ONLY}disabled{/if}>
					<span id="{$MODULE_NAME}_editView_fieldName_{$FIELD_NAME}_clear" class="fas fa-times-circle" title="{\App\Language::translate('LBL_CLEAR', $MODULE_NAME)}"></span>
				</button>
				<button class="btn btn-light js-icon-select" type="button" tabindex="{$TABINDEX}" title="{\App\Language::translate('LBL_BROWSE_ASSETS', $MODULE_NAME)}" {if $IS_EDITABLE_READ_ONLY}disabled{/if}>
					<span class="far fa-folder-open"></span>
				</button>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-Base-Edit-Field-Icon -->
{/strip}
