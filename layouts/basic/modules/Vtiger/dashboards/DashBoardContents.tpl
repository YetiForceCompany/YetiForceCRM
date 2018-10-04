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
	<div class="tpl-DashBoardContents px-sm-1 d-flex flex-row">
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
				<div class="grid-stack-item js-css-element-queries"
					 data-gs-y="{$WIDGET->getPosition($ROW, 'row')}" data-gs-width="{$WIDGET->getWidth()}"
					 data-gs-x="{$WIDGET->getPosition($COLCOUNT, 'col')}"
					 data-gs-height="{$WIDGET->getHeight()}" data-js="css-element-queries">
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
		<div class="o-tablet-scroll__container mx-1 d-none" data-js="class: d-none">
			<div class="o-tablet-scroll__content js-tablet-scroll position-fixed u-hide-underneath border" data-js="scroll | parent">
				<div class="o-tablet-scroll__icons d-flex flex-column u-hide-underneath px-1">
					<span class="fas fa-arrow-up"></span>
					<span class="far fa-hand-pointer my-2"></span>
					<span class="fas fa-arrow-down"></span>
				</div>
			</div>
		</div>
	</div>
	</div> {*dashboardViewContainer closing tag*}
{/strip}

