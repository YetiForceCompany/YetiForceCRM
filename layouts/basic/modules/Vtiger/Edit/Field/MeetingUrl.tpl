{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-MeetingUrl -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var="MEETING_SERVICE" value=\App\MeetingService::getInstance()}
	{assign var="PARAMS" value=$FIELD_MODEL->getFieldParams()}
	{assign var=TABINDEX value=$FIELD_MODEL->getTabIndex()}
	{function MEETING_INFO PARAMS=[]}
		{assign var="DEPENDENT_TEXT" value=[]}
		{assign var="MEETING_DEPENDENT_FIELDS" value=[]}
		{if !empty($PARAMS['exp']) && $FIELD_MODEL->getModule()->getFieldByName($PARAMS['exp'])}
			{assign var="MEETING_DEPENDENT_FIELDS" value=[$FIELD_MODEL->getModule()->getFieldByName($PARAMS['exp'])]}
		{/if}
		{if !empty($PARAMS['roomName']) && $FIELD_MODEL->getModule()->getFieldByName($PARAMS['roomName'])}
			{assign var="MEETING_DEPENDENT_FIELDS" value=array_merge($MEETING_DEPENDENT_FIELDS, [$FIELD_MODEL->getModule()->getFieldByName($PARAMS['roomName'])])}
		{/if}
		{foreach from=$MEETING_DEPENDENT_FIELDS item=DEPEND_FIELD}
			{if $DEPEND_FIELD->isViewable()}
				{assign var="DEPENDENT_TEXT" value=array_merge($DEPENDENT_TEXT, [$DEPEND_FIELD->getFullLabelTranslation($DEPEND_FIELD->getModule())])}
			{/if}
		{/foreach}
		{if $DEPENDENT_TEXT}
			<br>{\App\Language::translateArgs("LBL_MEETING_AUTOGENERATE_INFO", $DEPEND_FIELD->getModuleName(), implode(', ',$DEPENDENT_TEXT))}
		{/if}
	{/function}
	{if !isset($RECORD_ID)}
		{assign var="RECORD_ID" value=0}
	{/if}
	<div class="js-meeting-container">
		<div class="input-group {$WIDTHTYPE_GROUP}">
			<input id="{$MODULE_NAME}_editView_fieldName_{$FIELD_MODEL->getName()}" type="text" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}"
				class="form-control js-meeting-val" name="{$FIELD_MODEL->getName()}" tabindex="{$TABINDEX}"
				data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}{if $FIELD_MODEL->get('maximumlength')}maxSize[{$FIELD_MODEL->get('maximumlength')}],{/if}funcCall[Vtiger_Url_Validator_Js.invokeValidation]]" value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}"
				data-fieldinfo='{$FIELD_INFO}'
				{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if} />
			{if $VIEW !== 'Detail' && $MEETING_SERVICE->isActive()}
				<span class="input-group-append u-cursor-pointer">
					<button class="btn btn-light js-meeting-clear" type="button" tabindex="{$TABINDEX}" {if $FIELD_MODEL->isEditableReadOnly()}disabled="disabled" {/if}>
						<span id="{$MODULE_NAME}_editView_fieldName_{$FIELD_NAME}_clear" class="fas fa-times-circle" title="{\App\Language::translate('LBL_CLEAR', $MODULE_NAME)}"></span>
					</button>
					<button class="btn btn-light js-meeting-add js-popover-tooltip" type="button" aria-label="{\App\Language::translate("LBL_MEETING_AUTOGENERATE", $MODULE_NAME)}" tabindex="{$TABINDEX}" {if $FIELD_MODEL->isEditableReadOnly()}disabled="disabled" {/if}
						data-js="popover" data-trigger="hover" data-content="{\App\Language::translate("LBL_MEETING_AUTOGENERATE", $MODULE_NAME)}{MEETING_INFO PARAMS=$PARAMS}"
						data-url="{$FIELD_MODEL->getUITypeModel()->getUrl($RECORD_ID)}"
						data-exp-field="{if !empty($PARAMS['exp'])}{\App\Purifier::encodeHtml($PARAMS['exp'])}{/if}"
						data-room-name="{if !empty($PARAMS['roomName'])}{\App\Purifier::encodeHtml($PARAMS['roomName'])}{/if}"
						data-domain="{$MEETING_SERVICE->get('url')}">
						<span id="{$MODULE_NAME}_editView_fieldName_{$FIELD_NAME}_select" class="AdditionalIcon-VideoConference"></span>
					</button>
				</span>
			{/if}
		</div>
	</div>
	<!-- /tpl-Base-Edit-Field-MeetingUrl -->
{/strip}
