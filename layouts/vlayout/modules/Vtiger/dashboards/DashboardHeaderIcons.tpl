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
{if $SETTING_EXIST}
<a name="dfilter">
	<i class='icon-cog' border='0' align="absmiddle" title="{vtranslate('LBL_FILTER')}" alt="{vtranslate('LBL_FILTER')}"/>
</a>
{/if}
<a href="javascript:void(0);" name="drefresh" data-url="{$WIDGET->getUrl()}&content=data">
	<i class="icon-refresh" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_REFRESH')}" alt="{vtranslate('LBL_REFRESH')}"></i>
</a>
{if !$WIDGET->isDefault()}
	<a name="dclose" class="widget" data-url="{$WIDGET->getDeleteUrl()}">
		<i class="icon-remove" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_REMOVE')}" alt="{vtranslate('LBL_REMOVE')}"></i>
	</a>
{/if}