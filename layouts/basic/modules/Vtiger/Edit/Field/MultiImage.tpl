{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-MultiImage -->
	{assign var=TABINDEX value=$FIELD_MODEL->getTabIndex()}
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	<div class="border rounded c-multi-image__container-{$WIDTHTYPE} p-2 d-flex align-items-center clearfix c-multi-image js-multi-image">
		<input name="{$FIELD_MODEL->getFieldName()}_temp[]" type="file" class="d-none js-multi-image__file" tabindex="{$TABINDEX}" data-js="jQuery-file-upload"
			data-url="file.php?module={$FIELD_MODEL->getModuleName()}&action=MultiImage&field={$FIELD_MODEL->getFieldName()}{if $RECORD && !$RECORD->isNew()}&record={$RECORD->getId()}{/if}" accept="{$FIELD_MODEL->getUITypeModel()->getAcceptFormats()}" multiple>
		<input name="{$FIELD_MODEL->getFieldName()}" type="hidden" id="{$MODULE_NAME}_editView_fieldName_{$FIELD_MODEL->getFieldName()}" value="{$FIELD_VALUE}"
			data-validation-engine="validate[funcCall[Vtiger_MultiImage_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO}' class="js-multi-image__values"
			data-js="value" {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}>
		<button type="button" class="align-top d-inline mr-1 btn btn-sm btn-primary js-multi-image__file-btn c-multi-image__btn-{$WIDTHTYPE}" tabindex="{$TABINDEX}" data-js="click">
			<i class="fa fa-plus"></i>&nbsp;<span>{\App\Language::translate('BTN_ADD_FILE', $MODULE_NAME)}</span>
		</button>
		<div class="d-inline js-multi-image__result d-flex align-items-center c-multi-image__result-{$WIDTHTYPE}" data-js="container" data-name="{$FIELD_MODEL->getFieldName()}"></div>
		<div class="js-multi-image__progress progress d-none my-2" data-js="container|css:display">
			<div class="js-multi-image__progress-bar progress-bar progress-bar-striped progress-bar-animated" data-js="css:width" role="progressbar"></div>
		</div>
	</div>
	<!-- /tpl-Base-Edit-Field-MultiImage -->
{/strip}
