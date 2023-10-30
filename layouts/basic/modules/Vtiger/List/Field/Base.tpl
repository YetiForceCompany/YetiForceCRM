{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<!-- tpl-List-Field-Base -->
	{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUE value=$SEARCH_INFO['searchValue']}
	{else}
		{assign var=SEARCH_VALUE value=''}
	{/if}
	<div class="searchField {if isset($CLASS_SIZE)}{$CLASS_SIZE}{/if} u-min-w-150pxr">
		{if !empty($MODULE_MODEL) && $MODULE_MODEL->getAlphabetSearchField() eq $FIELD_MODEL->getName()}
			<div class="input-group col-12 px-0">
				<input type="text" name="{$FIELD_MODEL->getName()}" {if !empty($FIELD_MODEL->get('source_field_name'))} data-source-field-name="{$FIELD_MODEL->get('source_field_name')}" data-module-name="{$FIELD_MODEL->getModuleName()}" {/if} class="listSearchContributor form-control" value="{$SEARCH_VALUE}" title='{\App\Language::translate($FIELD_MODEL->getName(), $FIELD_MODEL->getModuleName())}' data-fieldinfo='{$FIELD_INFO|escape}' />
				<div class="input-group-append alphabetBtnContainer">
					{if empty($ALPHABET_VALUE)}
						<button class=" btn btn-outline-secondary alphabetBtn" type="button">
							<span class="fas fa-font" aria-hidden="true"></span>
							<span class="sr-only">{\App\Language::translate('LBL_ALPHABETIC_FILTERING')}</span>
						</button>
					{else}
						<button class=" btn btn-primary alphabetBtn" type="button">{$ALPHABET_VALUE}</button>
					{/if}
				</div>
			</div>
		{else}
			<div class="input-group">
				<input type="text" name="{$FIELD_MODEL->getName()}" {if !empty($FIELD_MODEL->get('source_field_name'))} data-source-field-name="{$FIELD_MODEL->get('source_field_name')}" data-module-name="{$FIELD_MODEL->getModuleName()}"
						{/if} class="listSearchContributor form-control" value="{$SEARCH_VALUE}" title='{\App\Language::translate($FIELD_MODEL->getName(), $FIELD_MODEL->getModuleName())}' data-fieldinfo='{$FIELD_INFO|escape}' {if !$FIELD_MODEL->searchLockedEmptyFields() || !$FIELD_MODEL->isActiveSearchView()}disabled{/if} />
					{if !empty($MODULE_MODEL) && isset($MODULE_MODEL->isentitytype)}
						<div class="input-group-append">
							<div class="input-group-text px-1">
								<input type="checkbox" class="js-empty-value" {if !$FIELD_MODEL->searchLockedEmptyFields()}checked{/if} {if !$FIELD_MODEL->isActiveSearchView()}disabled{/if}>
							</div>
						</div>
					{/if}
				</div>
			{/if}
		</div>
		<!-- /tpl-List-Field-Base -->
	{/strip}
