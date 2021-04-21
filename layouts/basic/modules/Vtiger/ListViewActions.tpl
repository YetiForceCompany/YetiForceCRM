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
	<div class="listViewActions paginationDiv pl-1 d-flex justify-content-end">
		{if (method_exists($MODULE_MODEL,'isPagingSupported') && ($MODULE_MODEL->isPagingSupported()  eq true)) || !method_exists($MODULE_MODEL,'isPagingSupported')}
			{include file=\App\Layout::getTemplatePath('Pagination.tpl', $MODULE)}
		{/if}
	</div>
	{if $PARENT_MODULE !== 'Settings' && $VIEW_MODEL}
		<div class="pl-1">
			{assign var=COLOR value=App\Config::search('LIST_ENTITY_STATE_COLOR')}
			<input type="hidden" id="entityState" value="{if $VIEW_MODEL->has('entityState')}{$VIEW_MODEL->get('entityState')}{else}Active{/if}">
			<div class="dropdown dropdownEntityState u-remove-dropdown-icon">
				<button class="btn btn-light dropdown-toggle" type="button" id="dropdownEntityState" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
					{if $VIEW_MODEL->get('entityState') == 'Archived'}
						<span class="js-icon fas fa-archive" data-js="click | attr"></span>
						<span class="sr-only">{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}</span>
					{elseif $VIEW_MODEL->get('entityState') == 'Trash'}
						<span class="js-icon fas fa-trash-alt" data-js="click | attr"></span>
						<span class="sr-only">{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}</span>
					{elseif $VIEW_MODEL->get('entityState') == 'All'}
						<span class="js-icon fas fa-bars" data-js="click | attr"></span>
						<span class="sr-only">{\App\Language::translate('LBL_ALL')}</span>
					{else}
						<span class="js-icon fas fa-undo-alt" data-js="click | attr"></span>
						<span class="sr-only">{\App\Language::translate('LBL_ENTITY_STATE_ACTIVE')}</span>
					{/if}
				</button>
				<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownEntityState">
					<li {if $COLOR['Active']}style="border-color: {$COLOR['Active']};"{/if}>
						<a class="dropdown-item{if !$VIEW_MODEL->get('entityState') || $VIEW_MODEL->get('entityState') == 'Active'} active{/if}" href="#" data-value="Active"><span class="fas fa-undo-alt js-icon" data-js="click | attr"></span><span class="ml-2">{\App\Language::translate('LBL_ENTITY_STATE_ACTIVE')}</span></a>
					</li>
					<li {if $COLOR['Archived']}style="border-color: {$COLOR['Archived']};"{/if}>
						<a class="dropdown-item{if $VIEW_MODEL->get('entityState') == 'Archived'} active{/if}" href="#" data-value="Archived"><span class="fas fa-archive js-icon" data-js="click | attr"></span><span class="ml-2">{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}</span></a>
					</li>
					<li {if $COLOR['Trash']}style="border-color: {$COLOR['Trash']};"{/if}>
						<a class="dropdown-item{if $VIEW_MODEL->get('entityState') == 'Trash'} active{/if}" href="#" data-value="Trash"><span class="fas fa-trash-alt js-icon" data-js="click | attr"></span><span class="ml-2">{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}</span></a>
					</li>
					<li>
						<a class="dropdown-item{if $VIEW_MODEL->get('entityState') == 'All'} active{/if}" href="#" data-value="All"><span class="fas fa-bars js-icon" data-js="click | attr"></span><span class="ml-2">{\App\Language::translate('LBL_ALL')}</span></a>
					</li>
				</ul>
			</div>
		</div>
	{/if}
{/strip}
