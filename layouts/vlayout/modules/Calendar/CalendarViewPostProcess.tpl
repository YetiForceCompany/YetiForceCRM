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
	<div id="toggleRightPanelButton" class="toggleRightPanelButton" title="{vtranslate('LBL_RIGHT_PANEL_SHOW_HIDE', 'Vtiger')}">
		<span id="tRightPanelButtonImage" class="icon-chevron-right"></span>
	</div>
	</div>
	<div class="span2 row-fluid marginLeftZero" id="rightPanel" style="min-height:550px; ">
	{foreach item=SIDEBARWIDGET key=index from=$QUICK_LINKS['SIDEBARWIDGETRIGHT']}
		<div class="quickWidget">
			<div class="accordion-heading accordion-toggle quickWidgetHeader" style="background: #737373; padding:10px " data-target="#{$MODULE}_sideBar_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARWIDGET->getLabel())}"
					data-toggle="collapse" data-parent="#quickWidgets" data-label="{$SIDEBARWIDGET->getLabel()}"
					data-widget-url="{$SIDEBARWIDGET->getUrl()}">
				<span class="pull-left"><img class="imageElement" alt="{vtranslate('LBL_SHIFT_BLOCK')}" data-rightimage="{vimage_path('rightArrowWhite.png')}" data-downimage="{vimage_path('downArrowWhite.png')}" src="{vimage_path('rightArrowWhite.png')}" /></span>
				<h5 class="title widgetTextOverflowEllipsis pull-left" title="{vtranslate($SIDEBARWIDGET->getLabel(), $MODULE)}" style="color:white">&nbsp;&nbsp;{vtranslate($SIDEBARWIDGET->getLabel(), $MODULE)}</h5>
				<div class="pull-right">
					{$SHIFT_BLOCK_SHOW="{$SIDEBARWIDGET->getLabel()}_BLOCK_SHIFT"}
					<input id="{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARWIDGET->getLabel())}" title="{vtranslate('LBL_SHIFT_BLOCK', $MODULE)}" class="switchBtn label switchsParent" type="checkbox" data-size="mini" data-label-width="5" data-handle-width="57">&nbsp;&nbsp;
					<a href="javascript:void(0);" name="drefresh" class="refreshCalendar cursorPointer ">
						<span class="icon-refresh icon-white" hspace="2" border="0" style="vertical-align: middle" title="{vtranslate('LBL_REFRESH')}" alt="{vtranslate('LBL_REFRESH')}"></span>
					</a>
				</div>
				<div class="loadingImg hide pull-right">
					<div class="loadingWidgetMsg"><strong>{vtranslate('LBL_LOADING_WIDGET', $MODULE)}</strong></div>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="widgetContainer accordion-body collapse" id="{$MODULE}_sideBar_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARWIDGET->getLabel())}" data-url="{$SIDEBARWIDGET->getUrl()}" style=" padding-top: 5px;">
			</div>
		</div>
	{/foreach}
	</div>
</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
	Calendar_CalendarView_Js.registerWidget();
});
</script>
{/strip}