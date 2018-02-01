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
		<div class="btn btn-block toggleSiteBarRightButton hideToggleSiteBarRightButton d-none d-sm-none d-md-block hidden-sm" title="{\App\Language::translate('LBL_RIGHT_PANEL_SHOW_HIDE', $MODULE)}">
			<span class="fas fa-chevron-left"></span>
		</div>
		<div class="siteBarContent paddingTop10">
			{if $CALENDAR_FILTERS->isActive()}
				<div class="panel panel-primary calendarFilters">
					<div class="panel-heading quickWidgetHeader calendarRightPanel clearfix ">
						<h4 class="panel-title col-lg-6 col-md-12 col-xs-5 paddingLRZero float-left" title="{\App\Language::translate('LBL_CALENDAR_FILTERS', $MODULE)}">
							{\App\Language::translate('LBL_CALENDAR_FILTERS', $MODULE)}
						</h4>
					</div>
					<div class="panel-collapse">
						<div class="panel-body">
							{foreach item=FILTER key=index from=$CALENDAR_FILTERS->getFilters()}
								{if $FILTER->type == 'checkbox'}
									<div class="checkbox margin0px">
										<label>
											<input type="checkbox" value="{$FILTER->value}" id="filterField_{$FILTER->name}" title="{$FILTER->name}" data-search="{\App\Purifier::encodeHtml($FILTER->searchParams)}" class="filterField">{\App\Language::translate($FILTER->name, $MODULE)}
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
						<h4 class="panel-title col-lg-6 col-md-12 col-xs-5 paddingLRZero float-left" title="{\App\Language::translate($SIDEBARWIDGET->getLabel(), $MODULE)}">
							{\App\Language::translate($SIDEBARWIDGET->getLabel(), $MODULE)}
						</h4>
						<div class="col-lg-6 col-md-12 col-xs-5 paddingTop10-md paddingLRZero float-right">
							<button class="selectAllBtn btn btn-light btn-sm pull-left-md pull-right-lg pull-right-sm">
								<div class="selectAll hide">{\App\Language::translate('LBL_SELECT_ALL', $MODULE)}</div>
								<div class="deselectAll">{\App\Language::translate('LBL_DESELECT_ALL', $MODULE)}</div>
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
