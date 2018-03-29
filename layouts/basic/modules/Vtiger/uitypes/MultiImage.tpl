{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	<input type="hidden" name="{$FIELD_MODEL->getFieldName()}"
		   id="{$MODULE_NAME}_editView_fieldName_{$FIELD_MODEL->getFieldName()}" value="{$FIELD_VALUE}"
		   data-validation-engine="validate[{if ($FIELD_MODEL->isMandatory() eq true)} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
		   data-fieldinfo='{$FIELD_INFO}'
		   {if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if}>
	<div class="border rounded px-2 pt-2 clearfix c-multi-image js-multi-image">
		<input class="d-none js-multi-image__file" type="file" name="{$FIELD_MODEL->getFieldName()}_temp[]"
			   data-js="jQuery-file-upload"
			   data-url="file.php?module={$FIELD_MODEL->getModuleName()}&action=MultiImage&field={$FIELD_MODEL->getFieldName()}&record={$RECORD->getId()}"
			   multiple>
		<input type="hidden" class="js-multi-image__values" data-js="value"
			   name="{$FIELD_MODEL->getFieldName()}[]" value="[]">
		<button type="button" class="align-top mb-2 mr-1 btn btn-sm btn-primary js-multi-image__file-btn"
				data-js="click">
			<i class="fa fa-plus"></i> {\App\Language::translate('BTN_ADD_FILE', $MODULE_NAME)}
		</button>
		<div class="js-multi-image__result" data-js="container" data-name="{$FIELD_MODEL->getFieldName()}"></div>
		<div class="js-multi-image__progress progress d-none my-2" data-js="container|css:display">
			<div class="js-multi-image__progress-bar progress-bar progress-bar-striped progress-bar-animated"
				 data-js="css:width"
				 role="progressbar"
				 style="width: 0%"></div>
		</div>
	</div>
{/strip}
