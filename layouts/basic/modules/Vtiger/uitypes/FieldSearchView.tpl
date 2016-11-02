{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
   * Contributor(s): YetiForce.com
 ********************************************************************************/
-->*}
{strip}
    {assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var="LABEL" value=$FIELD_MODEL->getFieldInfo()}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUE value=$SEARCH_INFO['searchValue']}
	{else}
		{assign var=SEARCH_VALUE value=''}
	{/if}
	<div class="searchField">
	{if $MODULE_MODEL && $MODULE_MODEL->getAlphabetSearchField() eq $FIELD_MODEL->get('name')}
		<div class="input-group col-xs-12">
			<input type="text" name="{$FIELD_MODEL->get('name')}" class="listSearchContributor form-control" value="{$SEARCH_VALUE}" title='{$LABEL['label']}' data-fieldinfo='{$FIELD_INFO|escape}'/>
			<div  class="input-group-btn alphabetBtnContainer">
				{if $ALPHABET_VALUE}
					<button class=" btn btn-primary alphabetBtn" type="button">
						{$ALPHABET_VALUE}
					</button>
				{else}
					<button class=" btn btn-default alphabetBtn" type="button">
						<span class="glyphicon glyphicon-font"></span>
					</button>
				{/if}
				
			</div>
		</div>
	{else}
			<input type="text" name="{$FIELD_MODEL->get('name')}" class="listSearchContributor form-control" value="{$SEARCH_VALUE}" title='{$LABEL['label']}' data-fieldinfo='{$FIELD_INFO|escape}' {if !$FIELD_MODEL->isActiveSearchView()}disabled{/if}/>
	{/if}
    </div>
{/strip}
