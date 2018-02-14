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
	{if $PARENT_MODULE !== 'Settings' && $VIEW_MODEL}
		<div>
			{assign var=COLOR value=AppConfig::search('LIST_ENTITY_STATE_COLOR')}
			<input type="hidden" id="entityState" value="{if $VIEW_MODEL->has('entityState')}{$VIEW_MODEL->get('entityState')}{else}Active{/if}">
			<div class="dropdown dropdownEntityState">
				<button class="btn btn-light dropdown-toggle" type="button" id="dropdownEntityState" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
					{if $VIEW_MODEL->get('entityState') == 'Archived'}
						<span class="fas fa-archive"></span>
					{elseif $VIEW_MODEL->get('entityState') == 'Trash'}
						<span class="fas fa-trash-alt"></span>
					{elseif $VIEW_MODEL->get('entityState') == 'All'}
						<span class="fas fa-bars"></span>
					{else}
						<span class="fas fa-undo-alt"></span>
					{/if}
				</button>
				<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownEntityState">
					<a class="dropdown-item" data-value="Active" href="#" {if $COLOR['Active']}style="border-color: {$COLOR['Active']};"{/if}><span class="fas fa-undo-alt"></span>&nbsp;&nbsp;{\App\Language::translate('LBL_ENTITY_STATE_ACTIVE')}</a>
					<a class="dropdown-item" data-value="Archived" href="#" {if $COLOR['Archived']}style="border-color: {$COLOR['Archived']};"{/if}><span class="fas fa-archive"></span>&nbsp;&nbsp;{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}</a>
					<a class="dropdown-item" data-value="Trash" href="#" {if $COLOR['Trash']}style="border-color: {$COLOR['Trash']};{/if}"><span class="fas fa-trash-alt"></span>&nbsp;&nbsp;{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}</a>
					<a class="dropdown-item" data-value="All" href="#"><span class="fas fa-bars"></span>&nbsp;&nbsp;{\App\Language::translate('LBL_ALL')}</a>
				</div>
			</div>
		</div>
	{/if}
	<div class="listViewActions paginationDiv mr-1">
        {if (method_exists($MODULE_MODEL,'isPagingSupported') && ($MODULE_MODEL->isPagingSupported()  eq true)) || !method_exists($MODULE_MODEL,'isPagingSupported')}
			{include file=\App\Layout::getTemplatePath('Pagination.tpl', $MODULE)}
        {/if}
	</div>
	<input type="hidden" id="recordsCount" value="" />
	<input type="hidden" id="selectedIds" name="selectedIds" />
	<input type="hidden" id="excludedIds" name="excludedIds" />
{/strip}
