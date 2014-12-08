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
<div class="quickWidgetContainer accordion">
	{assign var=val value=1}
	{foreach item=SIDEBARWIDGET key=index from=$QUICK_LINKS['SIDEBARWIDGET']}
		<div class="quickWidget">
			<div class="accordion-heading accordion-toggle quickWidgetHeader" data-target="#{$MODULE}_sideBar_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARWIDGET->getLabel())}"
					data-toggle="collapse" data-parent="#quickWidgets" data-label="{$SIDEBARWIDGET->getLabel()}"
					data-widget-url="{$SIDEBARWIDGET->getUrl()}" >
				<span class="pull-left"><img class="imageElement" data-rightimage="{vimage_path('rightArrowWhite.png')}" data-downimage="{vimage_path('downArrowWhite.png')}" src="{vimage_path('rightArrowWhite.png')}" /></span>
				<h5 class="title widgetTextOverflowEllipsis pull-right" title="{vtranslate($SIDEBARWIDGET->getLabel(), $MODULE)}">{vtranslate($SIDEBARWIDGET->getLabel(), $MODULE)}</h5>
				<div class="loadingImg hide pull-right">
					<div class="loadingWidgetMsg"><strong>{vtranslate('LBL_LOADING_WIDGET', $MODULE)}</strong></div>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="widgetContainer accordion-body collapse" id="{$MODULE}_sideBar_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARWIDGET->getLabel())}" data-url="{$SIDEBARWIDGET->getUrl()}">
			</div>
		</div>
	{/foreach}
</div>
{/strip}