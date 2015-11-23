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
	<div id="toggleRightPanelButton" class="btn btn-block toggleRightPanelButton hideRightPanelButton" title="{vtranslate('LBL_RIGHT_PANEL_SHOW_HIDE', 'Vtiger')}">
		<span id="tRightPanelButtonImage" class="glyphicon glyphicon-chevron-left"></span>
	</div>
	<div class="panel-group calendarRightPanel paddingRightZero rightPanelOpen move-action" id="rightPanel">
		{foreach item=SIDEBARWIDGET key=index from=$QUICK_LINKS['SIDEBARWIDGETRIGHT']}
			<div class="panel panel-dark quickWidget">
				<div class="panel-heading quickWidgetHeader calendarRightPanel clearfix">
					<h4 class="panel-title col-xs-7 paddingLRZero pull-left" title="{vtranslate($SIDEBARWIDGET->getLabel(), $MODULE)}">
						<a data-toggle="collapse" href="#{$MODULE}_sideBar_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARWIDGET->getLabel())}" data-label="{$SIDEBARWIDGET->getLabel()}"
						   data-widget-url="{$SIDEBARWIDGET->getUrl()}">
							<span class="pull-left"><img class="imageElement" alt="{vtranslate('LBL_SHIFT_BLOCK')}" data-rightimage="{vimage_path('rightArrowWhite.png')}" data-downimage="{vimage_path('downArrowWhite.png')}" src="{vimage_path('rightArrowWhite.png')}" />&nbsp;</span>{vtranslate($SIDEBARWIDGET->getLabel(), $MODULE)}
						</a>
					</h4>
					<div class="pull-right">
						{$SHIFT_BLOCK_SHOW="{$SIDEBARWIDGET->getLabel()}_BLOCK_SHIFT"}
						<input id="{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARWIDGET->getLabel())}" title="{vtranslate('LBL_SHIFT_BLOCK', $MODULE)}" class="switchBtn label switchsParent" data-handle-width="35" type="checkbox" data-size="mini" data-label-width="5" data-on-text="{vtranslate('LBL_ON_SWITCH',$MODULE)}" data-off-text="{vtranslate('LBL_OFF_SWITCH',$MODULE)}" checked >&nbsp;
						<a href="javascript:void(0);" name="drefresh" class="btn btn-default btn-xs refreshCalendar cursorPointer">
							<span class="glyphicon glyphicon-refresh icon-white" hspace="2" border="0" title="{vtranslate('LBL_REFRESH')}" alt="{vtranslate('LBL_REFRESH')}"></span>
						</a>
					</div>
					<div class="loadingImg hide pull-right">
						<div class="loadingWidgetMsg"><strong>{vtranslate('LBL_LOADING_WIDGET', $MODULE)}</strong></div>
					</div>
				</div>
				<div class="widgetContainer panel-collapse collapse" id="{$MODULE}_sideBar_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARWIDGET->getLabel())}" data-url="{$SIDEBARWIDGET->getUrl()}">
					<div class="panel-body">
					</div>
				</div>
			</div>
		{/foreach}
	</div>
</div>
</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function () {
		Calendar_CalendarView_Js.registerWidget();
	});
</script>
{/strip}
