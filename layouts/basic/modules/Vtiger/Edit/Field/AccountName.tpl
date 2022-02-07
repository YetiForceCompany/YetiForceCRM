{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-AccountName -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	{assign var=EXPLODED_FIELD_VALUE value=$FIELD_MODEL->getUITypeModel()->parseName()}
	{assign var=MAXIMUM_LENGTH value=$FIELD_MODEL->get('maximumlength')}
	{assign var=PARAMS value=$FIELD_MODEL->getFieldParams()}
	{assign var=LEGAL_FORM value=''}
	{if $RECORD}
		{assign var=LEGAL_FORM value=$RECORD->get('legal_form')}
	{/if}
	<div class="row">
		<div class="col px-0 {if $LEGAL_FORM === 'PLL_NATURAL_PERSON'}d-none{/if} js-account-name" data-js="container">
			{include file=\App\Layout::getTemplatePath('Edit/Field/Base.tpl', $MODULE_NAME)}
		</div>
		<div class="col pl-0 {if $LEGAL_FORM !== 'PLL_NATURAL_PERSON'}d-none{/if} js-first-name" data-js="container">
			<input value="{$EXPLODED_FIELD_VALUE['first']}" class="form-control" type="text" title="{\App\Language::translate('First Name', $MODULE_NAME)}" {' '}
				data-validation-engine="validate[{if $MAXIMUM_LENGTH/2}maxSize[{round($MAXIMUM_LENGTH/2)}],{/if} funcCall[Vtiger_InputMask_Validator_Js.invokeValidation]]" {if $FIELD_MODEL->isReadOnly() || $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if}{' '}
				data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator="{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}" {/if}{' '}
				{if isset($PARAMS['mask'])}data-inputmask="'mask': {\App\Purifier::encodeHtml(\App\Json::encode($PARAMS['mask']))}" {/if}{' '}
				tabindex="{$FIELD_MODEL->getTabIndex()}" placeholder="{\App\Language::translate('First Name', $MODULE_NAME)}" />
		</div>
		<div class="col px-0 {if $LEGAL_FORM !== 'PLL_NATURAL_PERSON'}d-none{/if} js-last-name" data-js="container">
			<input value="{$EXPLODED_FIELD_VALUE['last']}" class="form-control" type="text" title="{\App\Language::translate('Last Name', $MODULE_NAME)}" {' '}
				data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}{if $MAXIMUM_LENGTH/2}maxSize[{round($MAXIMUM_LENGTH/2)}],{/if} funcCall[Vtiger_InputMask_Validator_Js.invokeValidation]]" {if $FIELD_MODEL->isReadOnly() || $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if}{' '}
				data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator="{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}" {/if}{' '}
				{if isset($PARAMS['mask'])}data-inputmask="'mask': {\App\Purifier::encodeHtml(\App\Json::encode($PARAMS['mask']))}" {/if}{' '}
				tabindex="{$FIELD_MODEL->getTabIndex()}" placeholder="{\App\Language::translate('Last Name', $MODULE_NAME)}" />
		</div>
	</div>
	<!-- /tpl-Base-Edit-Field-AccountName -->
{/strip}
