{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-MultiAttachment -->
	{assign var=TABINDEX value=$FIELD_MODEL->getTabIndex()}
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	<div class="border rounded form-control-plaintext text-center c-multi-attachment js-multi-attachment ">
		<input name="{$FIELD_MODEL->getFieldName()}_temp[]" type="file" class="d-none js-multi-attachment__file" tabindex="{$TABINDEX}" data-js="jQuery-file-upload"
			data-url="file.php?module={$FIELD_MODEL->getModuleName()}&action=MultiAttachment&field={$FIELD_MODEL->getFieldName()}{if $RECORD && !$RECORD->isNew()}&record={$RECORD->getId()}{/if}" accept="{$FIELD_MODEL->getUITypeModel()->getAcceptFormats()}" multiple>
		<input name="{$FIELD_MODEL->getFieldName()}" type="hidden" id="{$MODULE_NAME}_editView_fieldName_{$FIELD_MODEL->getFieldName()}" value="{$FIELD_VALUE}"
			data-validation-engine="validate[funcCall[Vtiger_MultiImage_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO}' class="js-multi-attachment__values "
			data-js="value" {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}>
		<label for="{$MODULE_NAME}_editView_fieldName_{$FIELD_MODEL->getFieldName()}" class="text-muted u-cursor-pointer mb-0" id="filepond--drop-label" aria-hidden="true">
			{\App\Language::translate('LBL_DRAG_AND_DROP_FILES', $MODULE_NAME)}
		</label>
		<div class="js-multi-attachment__result" data-js="container" data-name="{$FIELD_MODEL->getFieldName()}"></div>
		<div class="js-multi-attachment__progress progress d-none mt-2" data-js="container|css:display">
			<div class="js-multi-attachment__progress-bar progress-bar progress-bar-striped progress-bar-animated" data-js="css:width" role="progressbar"></div>
		</div>
	</div>
	<!-- /tpl-Base-Edit-Field-MultiAttachment -->
{/strip}
