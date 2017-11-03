{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	{if $PARENT_MODULE !== 'Settings'}
		<div class="pull-right paddingLeft5px">
			<select class="select2 entityState" id="entityState">
				<option value="Active" {if $LIST_VIEW_MODEL->get('entityState') === 'Active'}selected{/if}>{\App\Language::translate('LBL_ACTIVEPLU')}</option>
				<option value="Deleted" {if $LIST_VIEW_MODEL->get('entityState') === 'Deleted'}selected{/if}>{\App\Language::translate('LBL_DELETED')}</option>
				<option value="Archived" {if $LIST_VIEW_MODEL->get('entityState') === 'Archived'}selected{/if}>{\App\Language::translate('LBL_ARCHIVED')}</option>
				<option value="All" {if $LIST_VIEW_MODEL->get('entityState') === 'All'}selected{/if}>{\App\Language::translate('LBL_ALL')}</option>
			</select>
		</div>
	{/if}
	<div class="listViewActions pull-right paginationDiv paddingLeft5px">
        {if (method_exists($MODULE_MODEL,'isPagingSupported') && ($MODULE_MODEL->isPagingSupported()  eq true)) || !method_exists($MODULE_MODEL,'isPagingSupported')}
			{include file=\App\Layout::getTemplatePath('Pagination.tpl', $MODULE)}
        {/if}
	</div>
	<div class="clearfix"></div>
	<input type="hidden" id="recordsCount" value=""/>
	<input type="hidden" id="selectedIds" name="selectedIds" />
	<input type="hidden" id="excludedIds" name="excludedIds" />
{/strip}
