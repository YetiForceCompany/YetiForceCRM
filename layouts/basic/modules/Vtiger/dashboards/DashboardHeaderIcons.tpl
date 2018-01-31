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
{if isset($SETTING_EXIST)}
	<a class="btn btn-xs btn-default" name="dfilter">
		<span class='icon-cog' border='0' align="absmiddle" title="{\App\Language::translate('LBL_FILTER')}" alt="{\App\Language::translate('LBL_FILTER')}"></span>
	</a>&nbsp;
{/if}
<a class="btn btn-xs btn-default" href="javascript:void(0);" name="drefresh" data-url="{$WIDGET->getUrl()}&content=data">
	<span class="fas fa-sync-alt" hspace="2" border="0" align="absmiddle" title="{\App\Language::translate('LBL_REFRESH')}" alt="{\App\Language::translate('LBL_REFRESH')}"></span>
</a>
{if !$WIDGET->isDefault()}
	&nbsp;
	<a name="dclose" class="widget btn btn-xs btn-default" data-url="{$WIDGET->getDeleteUrl()}">
		<span class="fas fa-times" hspace="2" border="0" align="absmiddle" title="{\App\Language::translate('LBL_CLOSE')}" alt="{\App\Language::translate('LBL_CLOSE')}"></span>
	</a>
{/if}
{/strip}
