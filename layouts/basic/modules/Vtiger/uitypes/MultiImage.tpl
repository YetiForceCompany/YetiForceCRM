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
	<div class="border rounded p-2 clearfix c-multi-image">
		<button class="fileinput-button btn btn-sm btn-primary mr-1 mb-1 float-left c-multi-image__file-btn">
			<input class="c-multi-image__file" type="file" name="{$FIELD_MODEL->getFieldName()}_temp[]"
				   data-url="file.php?module={$FIELD_MODEL->getModuleName()}&action=MultiImage&field={$FIELD_MODEL->getFieldName()}"
				   multiple>
			<i class="fa fa-plus"></i> {\App\Language::translate('BTN_ADD_FILE', $MODULE_NAME)}
		</button>
		<input type="hidden" class="c-multi-image__values" name="{$FIELD_MODEL->getFieldName()}[]" value="[]">
		<div class="c-multi-image__result" data-name="{$FIELD_MODEL->getFieldName()}">
			{if $RECORD}
				{assign var="RECORD_ID" value=$RECORD->getId()}
				{assign var="IMAGES" value=$FIELD_VALUE}
			{else}
				{assign var="RECORD_ID" value=''}
				{assign var="IMAGES" value=[]}
			{/if}
			{foreach key=ITER item=IMAGE_INFO from=$IMAGES}
				<div class="c-multi-image__preview d-inline-block m-1"
					 data-title="{$IMAGE_INFO.name}"
					 data-toggle="popover"
					 data-content="<img src='{$FIELD_MODEL->getUITypeModel()->getImagePath($IMAGE_INFO.attachmentid, $RECORD_ID)}' class='w-100' />">
					<div class="c-multi-image__preview-body">
						<img class="c-multi-image__preview-img border rounded"
							 src=src="{$FIELD_MODEL->getUITypeModel()->getImagePath($IMAGE_INFO.attachmentid, $RECORD_ID)}"
							 tabindex="0">
						<button type="button" class="btn btn-sm btn-danger" aria-label="Close"
								onclick="App.Fields.MultiImage.destroyPreview(this)" tabindex="0">
							<span aria-hidden="true"><i class="fa fa-trash-alt"></i></span>
						</button>
					</div>
				</div>
			{/foreach}
		</div>
		<div class="c-multi-image__progress progress d-none my-2">
			<div class="c-multi-image__progress-bar progress-bar progress-bar-striped progress-bar-animated"
				 role="progressbar"
				 style="width: 0%"></div>
		</div>
	</div>
{/strip}
