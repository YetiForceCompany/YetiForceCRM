{*<!--
/************************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
************************************************************************************/
-->*}
{strip}
	<div class="tpl-DashBoardContents grid-stack">
		{assign var=COLUMNS value=2}
		{assign var=ROW value=1}
		{assign var=COLCOUNT value=1}
		{assign var=SPECIAL_WIDTGETS value=['ChartFilter', 'MiniList', 'Notebook', 'Rss']}
		{foreach from=$WIDGETS item=WIDGET name=count}
			{if $WIDGET->get('active') eq 0}
				{continue}
			{/if}
			{assign var=WIDGETDOMID value=$WIDGET->get('linkid')}
			{if in_array($WIDGET->getName(), $SPECIAL_WIDTGETS)}
				{assign var=WIDGETDOMID value=$WIDGET->get('linkid')|cat:'-':$WIDGET->get('widgetid')}
			{/if}
			<div class="grid-stack-item" data-gs-x="{$WIDGET->getPositionCol($COLCOUNT)}"
				 data-gs-y="{$WIDGET->getPositionCol($COLCOUNT)}" data-gs-width="{$WIDGET->getWidth()}"
				 data-gs-height="{$WIDGET->getHeight()}">
				<div id="{$WIDGETDOMID}" {if $smarty.foreach.count.index % $COLUMNS == 0 and $smarty.foreach.count.index != 0} {/if}
						{assign var=ROW value=$ROW+1}
						{assign var=COLCOUNT value=($smarty.foreach.count.index % $COLUMNS)+1}
					 class="grid-stack-item-content dashboardWidget dashboardWidget_{$smarty.foreach.count.index}"
					 data-url="{$WIDGET->getUrl()}"
					 data-mode="open" data-name="{$WIDGET->getName()}" data-cache="{$WIDGET->get('cache')}"
					 data-loader="widgetLoader">
				</div>
			</div>
		{/foreach}
		<input type="hidden" id=row value="{$ROW}"/>
		<input type="hidden" id=col value="{$COLCOUNT}"/>
	</div>
	</div> {*dashboardViewContainer closing tag*}
{/strip}
