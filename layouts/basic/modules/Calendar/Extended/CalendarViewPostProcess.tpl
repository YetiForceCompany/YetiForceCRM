{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Calendar-Extended-CalendarViewPostProcess {if $USER_MODEL->get('leftpanelhide')}leftPanelOpen {/if}siteBarRight calendarRightPanel col-xs-12 hideSiteBar"
		 data-showPanel="{if !AppConfig::module($MODULE, 'SHOW_RIGHT_PANEL')}0{else}1{/if}" id="rightPanel">
		<div class="btn btn-block toggleSiteBarRightButton hideToggleSiteBarRightButton hidden-xs hidden-sm"
			 title="{\App\Language::translate('LBL_RIGHT_PANEL_SHOW_HIDE', $MODULE)}">
			<span class="glyphicon glyphicon-chevron-left"></span>
		</div>
		<ul class="nav nav-tabs" id="rightPanelTab" role="tablist">
			<li class="nav-item active">
				<a class="nav-link" id="rightPanelEvent-tab" data-toggle="tab" href="#rightPanelEvent" role="tab"
				   aria-controls="event"
				   aria-selected="true">{\App\Language::translate('LBL_EVENTS', $MODULE)}</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" id="rightPanelFilter-tab" data-toggle="tab" href="#rightPanelFilter" role="tab"
				   aria-controls="filter" aria-selected="false">{\App\Language::translate('LBL_FILTER', $MODULE)}</a>
			</li>
		</ul>
		<div class="tab-content" id="rightPanelTabContent">
			<div class="tab-pane fade active in" id="rightPanelEvent" role="tabpanel"
				 aria-labelledby="rightPanelEvent-tab">
				<div class="qcForm"></div>
			</div>
			<div class="tab-pane fade" id="rightPanelFilter" role="tabpanel" aria-labelledby="rightPanelFilter-tab">
				<div class="filterButtons">
					<span class="btn btn-danger calendarFilters" id="meetingSwitchingDays"
						  data-type="Meeting">{strtoupper(\App\Language::translate('LBL_FILTER', $MODULE))}</span>
					<span class="btn btn-success calendarFilters" id="taskSwitchingDays" data-type="Task">
						{strtoupper(\App\Language::translate('LBL_TASK', $MODULE))}
					</span>
					<span class="btn btn-warning calendarFilters" id="phoneSwitchingDays"
						  data-type="Call">{strtoupper(\App\Language::translate('LBL_CALL', $MODULE))}</span>
				</div>
				<div style="border-bottom: 1px solid #ddd;">
					<div class="usersForm"></div>
					<div class="groupForm"></div>
				</div>
			</div>
		</div>
	</div>
{/strip}
