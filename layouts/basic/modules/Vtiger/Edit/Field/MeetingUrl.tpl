{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-MeetingUrl -->
	{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var="MEETING_SERVICE" value=\App\MeetingService::getInstance()}
	{assign var="PARAMS" value=$FIELD_MODEL->getFieldParams()}
	<div class="js-meeting-container">
		<div class="input-group {$WIDTHTYPE_GROUP}">
			<input id="{$MODULE_NAME}_editView_fieldName_{$FIELD_MODEL->getName()}" type="text" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}"
			   class="form-control js-meeting-val" name="{$FIELD_MODEL->getName()}" tabindex="{$FIELD_MODEL->getTabIndex()}"
			   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}{if $FIELD_MODEL->get('maximumlength')}maxSize[{$FIELD_MODEL->get('maximumlength')}],{/if}funcCall[Vtiger_Url_Validator_Js.invokeValidation]]" value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}"
			   data-fieldinfo='{$FIELD_INFO}'
			   {if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if} />
		{if $VIEW !== 'Detail' && $MEETING_SERVICE->isActive()}
			{assign var="EXP_FIELD" value=''}
			{if !empty($PARAMS['exp']) }
				{assign var="EXP_FIELD" value=$FIELD_MODEL->getModule()->getFieldByName($PARAMS['exp'])}
			{/if}
			<span class="input-group-append u-cursor-pointer">
				<button class="btn btn-light js-meeting-clear" type="button" tabindex="{$TABINDEX}" {if $FIELD_MODEL->isEditableReadOnly()}disabled="disabled"{/if}>
					<span id="{$MODULE_NAME}_editView_fieldName_{$FIELD_NAME}_clear" class="fas fa-times-circle" title="{\App\Language::translate('LBL_CLEAR', $MODULE_NAME)}"></span>
				</button>
				<button class="btn btn-light js-meeting-add js-popover-tooltip" type="button" tabindex="{$TABINDEX}" {if $FIELD_MODEL->isEditableReadOnly()}disabled="disabled"{/if}
					data-js="popover" data-trigger="hover focus" data-content="{\App\Language::translate("LBL_MEETING_AUTOGENERATE", $MODULE_NAME)}
					{if $EXP_FIELD && $EXP_FIELD->isViewable()}<br>{\App\Language::translateArgs("LBL_MEETING_AUTOGENERATE_INFO", $MODULE_NAME, $EXP_FIELD->getFullLabelTranslation($EXP_FIELD->getModule()))}{/if}"
					data-url="{$FIELD_MODEL->getUITypeModel()->getUrl($RECORD_ID)}" data-exp="{if !empty($PARAMS['exp'])}{\App\Purifier::encodeHtml($PARAMS['exp'])}{/if}">
					<span id="{$MODULE_NAME}_editView_fieldName_{$FIELD_NAME}_select" class="AdditionalIcon-VideoConference"></span>
				</button>
			</span>
		{/if}
	</div>
	<!-- /tpl-Base-Edit-Field-MeetingUrl -->
{/strip}
