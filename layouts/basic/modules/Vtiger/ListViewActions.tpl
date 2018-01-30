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
		<div class="pull-right paddingLeft5px">
			{assign var=COLOR value=AppConfig::search('LIST_ENTITY_STATE_COLOR')}
			<input type="hidden" id="entityState" value="{if $VIEW_MODEL->has('entityState')}{$VIEW_MODEL->get('entityState')}{else}Active{/if}">
			<div class="dropdown dropdownEntityState">
				<button class="btn btn-default dropdown-toggle" type="button" id="dropdownEntityState" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
					{if $VIEW_MODEL->get('entityState') == 'Archived'}
						<span class="fas fa-archive"></span>
					{elseif $VIEW_MODEL->get('entityState') == 'Trash'}
						<span class="fas fa-trash-alt"></span>
					{elseif $VIEW_MODEL->get('entityState') == 'All'}
						<span class="glyphicon glyphicon-menu-hamburger"></span>
					{else}
						<span class="fas fa-undo-alt"></span>
					{/if}
				</button>
				<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownEntityState">
					<li {if $COLOR['Active']}style="border-color: {$COLOR['Active']};"{/if}>
						<a href="#" data-value="Active"><span class="fas fa-undo-alt"></span>&nbsp;&nbsp;{\App\Language::translate('LBL_ENTITY_STATE_ACTIVE')}</a>
					</li>
					<li {if $COLOR['Archived']}style="border-color: {$COLOR['Archived']};"{/if}>
						<a href="#" data-value="Archived"><span class="fas fa-archive"></span>&nbsp;&nbsp;{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}</a>
					</li>
					<li {if $COLOR['Trash']}style="border-color: {$COLOR['Trash']};"{/if}>
						<a href="#" data-value="Trash"><span class="fas fa-trash-alt"></span>&nbsp;&nbsp;{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}</a>
					</li>
					<li>
						<a href="#" data-value="All"><span class="glyphicon glyphicon-menu-hamburger"></span>&nbsp;&nbsp;{\App\Language::translate('LBL_ALL')}</a>
					</li>
				</ul>
			</div>
		</div>
	{/if}
	<div class="listViewActions pull-right paginationDiv paddingLeft5px">
        {if (method_exists($MODULE_MODEL,'isPagingSupported') && ($MODULE_MODEL->isPagingSupported()  eq true)) || !method_exists($MODULE_MODEL,'isPagingSupported')}
			{include file=\App\Layout::getTemplatePath('Pagination.tpl', $MODULE)}
        {/if}
	</div>
	<div class="clearfix"></div>
	<input type="hidden" id="recordsCount" value="" />
	<input type="hidden" id="selectedIds" name="selectedIds" />
	<input type="hidden" id="excludedIds" name="excludedIds" />
{/strip}
