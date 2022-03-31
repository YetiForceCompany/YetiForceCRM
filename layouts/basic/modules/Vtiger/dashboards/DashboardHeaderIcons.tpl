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
	{if isset($SETTING_EXIST)}
		<a class="btn btn-sm btn-light" name="dfilter">
			<span class='icon-cog' title="{\App\Language::translate('LBL_FILTER')}"></span>
		</a>
		&nbsp;
	{/if}
	<button class="btn btn-sm btn-light js-widget-refresh" title="{\App\Language::translate('LBL_REFRESH')}" data-url="{$WIDGET->getUrl()}&content=data" data-js="click">
		<span class="fas fa-sync-alt"></span>
	</button>
	{if !$WIDGET->isDefault()}
		<button class="btn btn-sm btn-light js-widget-remove" title="{\App\Language::translate('LBL_CLOSE')}" data-url="{$WIDGET->getDeleteUrl()}" data-js="click">
			<span class="fas fa-times"></span>
		</button>
	{/if}
{/strip}
