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
	<div class="{if $USER_MODEL->get('leftpanelhide')}leftPanelOpen {/if}siteBarRight calendarRightPanel col-xs-12 hideSiteBar" data-showPanel="{if !AppConfig::module($MODULE, 'SHOW_RIGHT_PANEL')}0{else}1{/if}" id="rightPanel">
		<div class="btn btn-block toggleSiteBarRightButton hideToggleSiteBarRightButton hidden-xs hidden-sm" title="{vtranslate('LBL_RIGHT_PANEL_SHOW_HIDE', $MODULE)}">
			<span class="glyphicon glyphicon-chevron-left"></span>
		</div>
		<div class="siteBarContent paddingTop10">
			{if $CALENDAR_FILTERS->isActive()}
				<div class="panel panel-primary calendarFilters">
					<div class="panel-heading quickWidgetHeader calendarRightPanel clearfix ">
						<h4 class="panel-title col-lg-6 col-md-12 col-xs-5 paddingLRZero pull-left" title="{vtranslate('LBL_CALENDAR_FILTERS', $MODULE)}">
							{vtranslate('LBL_CALENDAR_FILTERS', $MODULE)}
						</h4>
					</div>
					<div class="panel-collapse">
						<div class="panel-body">
							{foreach item=FILTER key=index from=$CALENDAR_FILTERS->getFilters()}
								{if $FILTER->type == 'checkbox'}
									<div class="checkbox margin0px">
										<label>
											<input type="checkbox" value="{$FILTER->value}" id="filterField_{$FILTER->name}" title="{$FILTER->name}" data-search="{Vtiger_Util_Helper::toSafeHTML($FILTER->searchParams)}" class="filterField">{vtranslate($FILTER->name, $MODULE)}
										</label>
									</div>
								{/if}
							{/foreach}
						</div>
					</div>
				</div>
			{/if}
			{foreach item=SIDEBARWIDGET key=index from=$QUICK_LINKS['SIDEBARWIDGETRIGHT']}
				<div class="panel panel-primary quickWidget">
					<div class="panel-heading quickWidgetHeader calendarRightPanel clearfix ">
						<h4 class="panel-title col-lg-6 col-md-12 col-xs-5 paddingLRZero pull-left" title="{vtranslate($SIDEBARWIDGET->getLabel(), $MODULE)}">
							{vtranslate($SIDEBARWIDGET->getLabel(), $MODULE)}
						</h4>
						<div class="col-lg-6 col-md-12 col-xs-5 paddingTop10-md paddingLRZero pull-right ">
							<button class="selectAllBtn btn btn-default btn-xs pull-left-md pull-right-lg pull-right-sm">
								<div class="selectAll hide">{vtranslate('LBL_SELECT_ALL', $MODULE)}</div>
								<div class="deselectAll">{vtranslate('LBL_DESELECT_ALL', $MODULE)}</div>
							</button>
						</div>
					</div>
					<div class="widgetContainer panel-collapse" id="{$MODULE}_sideBar_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARWIDGET->getLabel())}" data-url="{$SIDEBARWIDGET->getUrl()}">
						<div class="panel-body"></div>
					</div>
				</div>
			{/foreach}
		</div>
	</div>
</div>
</div>
</div>
{/strip}
