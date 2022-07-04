{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-Phone -->
	{assign var=TABINDEX value=$FIELD_MODEL->getTabIndex()}
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var="PARAMS" value=$FIELD_MODEL->getFieldParams()}
	{assign var="NUMBER" value=$FIELD_MODEL->get('fieldvalue')}
	{if $RECORD}
		{assign var="PHONE_FIELDS" value=$FIELD_MODEL->getUITypeModel()->getRelatedFields($RECORD)}
	{/if}
	<div>
		{if \App\Config::main('phoneFieldAdvancedVerification',false)}
			{if $NUMBER}
				{assign var="PHONE_DETAIL" value=App\Fields\Phone::getDetails($NUMBER)}
				{assign var="COUNTRY" value=$PHONE_DETAIL['country']}
			{else}
				{assign var="PHONE_DETAIL" value=[]}
				{if !\App\Config::component('Phone', 'defaultPhoneCountry')}
					{assign var="COUNTRY" value=\App\Language::getLanguageRegion()}
				{else}
					{assign var="COUNTRY" value=''}
				{/if}
			{/if}
			{assign var="FIELD_NAME_EXTRA" value=$FIELD_MODEL->getFieldName()|cat:'_extra'}
			{assign var="FIELD_MODEL_EXTRA" value=$FIELD_MODEL->getModule()->getFieldByName($FIELD_NAME_EXTRA)}
			{assign var=PICKLIST_VALUES value=App\Fields\Country::getAll('phone')}
			{assign var=IS_LAZY value=count($PICKLIST_VALUES) > \App\Config::performance('picklistLimit')}
			<div class="form-row">
				<div class="col-md-12">
					<div class="input-group {$WIDTHTYPE_GROUP} phoneGroup mb-1">
						<div class="input-group-prepend m-0 p-0">
							<select name="{$FIELD_MODEL->getFieldName()}_country" tabindex="{$TABINDEX}"
								{if $IS_LAZY} data-select-lazy="true" {/if}
								id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}_dropDown_{\App\Layout::getUniqueId()}" class="select2 phoneCountryList" data-template-result="prependDataTemplate" data-template-selection="prependDataTemplate" required="required" data-dropdown-auto-width="true" {if $FIELD_MODEL->isEditableReadOnly()} readonly="readonly" {/if}>
								{foreach key=KEY item=ROW from=$PICKLIST_VALUES}
									{assign var=TRANSLATE value=\App\Language::translateSingleMod($ROW['name'],'Other.Country')}
									<option value="{$KEY}" {if $COUNTRY === $KEY} selected {/if} title="{$TRANSLATE}" data-template="<span><span class='fi fi-{$KEY|lower} mr-2'></span>{$TRANSLATE}</span>">{$TRANSLATE}</option>
								{/foreach}
							</select>
						</div>
						{if $PHONE_DETAIL && (isset($PHONE_DETAIL['geocoding']) || isset($PHONE_DETAIL['carrier']))}
							{assign var="TITLE" value=$PHONE_DETAIL['geocoding']|cat:' '|cat:$PHONE_DETAIL['carrier']}
						{else}
							{assign var="TITLE" value=\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}
						{/if}
						{if $PHONE_DETAIL && isset($PHONE_DETAIL['number'])}
							{assign var="NUMBER" value=$PHONE_DETAIL['number']}
						{/if}
						<input name="{$FIELD_MODEL->getFieldName()}" class="form-control" value="{$NUMBER}" id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}"
							title="{$TITLE}" placeholder="{$TITLE}" type="text" tabindex="{$TABINDEX}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-advanced-verification="1" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if} {if isset($PARAMS['mask'])}data-inputmask="'mask': {\App\Purifier::encodeHtml(\App\Json::encode($PARAMS['mask']))}" {/if} />
						{if !empty($PHONE_FIELDS)}
							<div class="input-group-append m-0 p-0">
								<button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<span class="sr-only">Toggle Dropdown</span>
								</button>
								<div class="dropdown-menu">
									{foreach from=$PHONE_FIELDS item=PHONE_FIELD key=key}
										<a class="dropdown-item js-phone-change" href="#" data-value="{\App\Purifier::encodeHtml($PHONE_FIELD->get('fieldvalue'))}">{$PHONE_FIELD->getFullLabelTranslation()}</a>
									{/foreach}
								</div>
							</div>
						{/if}
					</div>
				</div>
			</div>
		{else}
			<input name="{$FIELD_MODEL->getFieldName()}" tabindex="{$TABINDEX}" value="{\App\Purifier::encodeHtml($NUMBER)}" id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" placeholder="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" type="text" class="form-control" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}{if $FIELD_MODEL->get('maximumlength')}maxSize[{$FIELD_MODEL->get('maximumlength')}],{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-advanced-verification="0" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if} {if isset($PARAMS['mask'])}data-inputmask="'mask': {\App\Purifier::encodeHtml(\App\Json::encode($PARAMS['mask']))}" {/if} />
		{/if}
	</div>
	<!-- /tpl-Base-Edit-Field-Phone -->
{/strip}
