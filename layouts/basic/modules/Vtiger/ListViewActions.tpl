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
	<div class="listViewActions pull-right paginationDiv paddingLeft5px">
        {if (method_exists($MODULE_MODEL,'isPagingSupported') && ($MODULE_MODEL->isPagingSupported()  eq true)) || !method_exists($MODULE_MODEL,'isPagingSupported')}
			<div class="">
				{include file='Pagination.tpl'|@vtemplate_path:$MODULE}
			</div>
        {/if}

	</div>
	<div class="clearfix"></div>
	<input type="hidden" id="recordsCount" value=""/>
	<input type="hidden" id="selectedIds" name="selectedIds" />
	<input type="hidden" id="excludedIds" name="excludedIds" />
{/strip}
