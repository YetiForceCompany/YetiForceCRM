{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" id="input{$FIELD_MODEL->getFieldName()}" value="{$FIELD_MODEL->get('fieldvalue')}"
		   data-validation-engine="validate[{if ($FIELD_MODEL->isMandatory() eq true)} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
		   data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if}>
	<button type="button" class="marginBottom5 btn-primary btn btn-sm showModal" data-url="index.php?module={$FIELD_MODEL->getModuleName()}&view=FileUpload&inputName={$FIELD_MODEL->getFieldName()}&fileType=image" id="fileUpload_{$FIELD_MODEL->getFieldName()}" title="{\App\Language::translate('BTN_ADD_FILE', $MODULE_NAME)}">
		<span class="glyphicon glyphicon-plus"> {\App\Language::translate('BTN_ADD_FILE', $MODULE_NAME)}</span>
	</button>

	<div id="fileResult{$FIELD_MODEL->getFieldName()}">
		{if $RECORD}
			{assign var="RECORD_ID" value=$RECORD->getId()}
			{assign var="IMAGES" value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD_ID)}
		{else}
			{assign var="RECORD_ID" value=''}
			{assign var="IMAGES" value=[]}
		{/if}
		{foreach key=ITER item=IMAGE_INFO from=$IMAGES}
			<div class="multiImageContenDiv pull-left" title="{$IMAGE_INFO.name}">
				<div class="contentImage">
					<button type="button" class="btn btn-sm btn-default imageFullModal hide"><span class="glyphicon glyphicon-fullscreen"></span></button>
					<img src="{$FIELD_MODEL->getUITypeModel()->getImagePath($IMAGE_INFO.attachmentid, $RECORD_ID)}" class="multiImageListIcon"></div>
				<span class="btn btn-danger btn-xs multiImageDelete glyphicon glyphicon-trash" data-id="{$IMAGE_INFO.attachmentid}"></span>&nbsp;
			</div>
		{/foreach}
	</div>
{/strip}
