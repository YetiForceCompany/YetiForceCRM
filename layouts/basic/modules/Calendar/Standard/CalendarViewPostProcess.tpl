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
	<div class="tpl-CalendarViewPostProcess {if $USER_MODEL->get('leftpanelhide')}c-menu--open {/if}siteBarRight calendarRightPanel col-12 hideSiteBar"
		 data-showPanel="{if !AppConfig::module($MODULE, 'SHOW_RIGHT_PANEL')}0{else}1{/if}" id="rightPanel">
		<div class="btn btn-block toggleSiteBarRightButton hideToggleSiteBarRightButton d-none d-lg-block"
			 title="{\App\Language::translate('LBL_RIGHT_PANEL_SHOW_HIDE', $MODULE)}">
			<span class="fas fa-chevron-left"></span>
		</div>
		<div class="siteBarContent pt-4">
			{if $CALENDAR_FILTERS->isActive()}
				<div class="c-panel bg-primary calendarFilters">
					<div class="c-panel__header quickWidgetHeaderc px-3">
						<h5 class="card-title h6 text-white o-label-container col-xl-5"
							title="{\App\Language::translate('LBL_CALENDAR_FILTERS', $MODULE)}">
							{\App\Language::translate('LBL_CALENDAR_FILTERS', $MODULE)}
						</h5>
					</div>
					<div class="panel-collapse">
						<div class="card-body">
							{foreach item=FILTER key=index from=$CALENDAR_FILTERS->getFilters()}
								{if $FILTER->type == 'checkbox'}
									<div class="checkbox m-0">
										<label>
											<input type="checkbox" value="{$FILTER->value}"
												   id="filterField_{$FILTER->name}" title="{$FILTER->name}"
												   data-search="{\App\Purifier::encodeHtml($FILTER->searchParams)}"
												   class="filterField">{\App\Language::translate($FILTER->name, $MODULE)}
										</label>
									</div>
								{/if}
							{/foreach}
						</div>
					</div>
				</div>
			{/if}
			{foreach item=SIDEBARWIDGET key=index from=$QUICK_LINKS['SIDEBARWIDGETRIGHT']}
				<div class="js-toggle-panel c-panel border-info quickWidget" data-js="click">
					<div class="c-panel__header quickWidgetHeader bg-info">
						<div class="form-row align-items-center px-4">
							<div class="card-title h6 text-white o-label-container col-xl-5"
								 title="{\App\Language::translate($SIDEBARWIDGET->getLabel(), $MODULE)}">
								{\App\Language::translate($SIDEBARWIDGET->getLabel(), $MODULE)}
							</div>
							<div class="o-btn-container col-xl-7">
								<button class="selectAllBtn btn btn-light btn-sm">
									<div class="selectAll d-none">{\App\Language::translate('LBL_SELECT_ALL', $MODULE)}</div>
									<div class="deselectAll">{\App\Language::translate('LBL_DESELECT_ALL', $MODULE)}</div>
								</button>
							</div>
						</div>
					</div>
					<div class="widgetContainer panel-collapse {$SIDEBARWIDGET->get('linkclass')}"
						 id="{$MODULE}_sideBar_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARWIDGET->getLabel())}"
						 data-url="{$SIDEBARWIDGET->getUrl()}">
						<div class="card-body"></div>
					</div>
				</div>
			{/foreach}
		</div>
	</div>
{/strip}
