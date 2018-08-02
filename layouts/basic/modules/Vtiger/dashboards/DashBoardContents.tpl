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
	<div class="tpl-DashBoardContents">
		<div class="grid-stack">
			{assign var=COLUMNS value=3}
			{assign var=ROW value=0}
			{assign var=COLCOUNT value=0}
			{assign var=SPECIAL_WIDTGETS value=['ChartFilter', 'MiniList', 'Notebook', 'Rss']}
			{foreach from=$WIDGETS item=WIDGET name=count}
				{if $WIDGET->get('active') eq 0}
					{continue}
				{/if}
				{assign var=WIDGETDOMID value=$WIDGET->get('linkid')}
				{if in_array($WIDGET->getName(), $SPECIAL_WIDTGETS)}
					{assign var=WIDGETDOMID value=$WIDGET->get('linkid')|cat:'-':$WIDGET->get('widgetid')}
				{/if}
				{if $smarty.foreach.count.index > 2}
					{assign var=ROW value=4}
				{elseif $smarty.foreach.count.index > 5}
					{assign var=ROW value=8}
				{elseif $smarty.foreach.count.index > 8}
					{assign var=ROW value=12}
				{/if}
				{if $smarty.foreach.count.index == 1 || $smarty.foreach.count.index == 4|| $smarty.foreach.count.index == 7}
					{assign var=COLCOUNT value=4}
				{elseif $smarty.foreach.count.index == 2 || $smarty.foreach.count.index == 5|| $smarty.foreach.count.index == 8}
					{assign var=COLCOUNT value=8}
				{elseif $smarty.foreach.count.index % 3 == 0}
					{assign var=COLCOUNT value=0}
				{/if}
				<div class="grid-stack-item"
					 data-gs-y="{$WIDGET->getPositionRow($ROW)}" data-gs-width="{$WIDGET->getWidth()}"
					 data-gs-x="{$WIDGET->getPositionCol($COLCOUNT)}"
					 data-gs-height="{$WIDGET->getHeight()}">
					<div id="{$WIDGETDOMID}" {if $smarty.foreach.count.index % $COLUMNS == 0 and $smarty.foreach.count.index != 0} {/if}

						 class="grid-stack-item-content dashboardWidget dashboardWidget_{$smarty.foreach.count.index}"
						 data-url="{$WIDGET->getUrl()}"
						 data-mode="open" data-name="{$WIDGET->getName()}" data-cache="{$WIDGET->get('cache')}"
						 data-loader="widgetLoader">
					</div>
				</div>
			{/foreach}
			<input type="hidden" id="row" value="{$ROW}"/>
			<input type="hidden" id="col" value="{$COLCOUNT}"/>
		</div>
	</div>
	</div> {*dashboardViewContainer closing tag*}
{/strip}

