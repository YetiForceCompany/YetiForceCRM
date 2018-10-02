{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Calendar-Extended-CalendarViewPostProcess js-calendarRightPanel {if $USER_MODEL->get('leftpanelhide')}leftPanelOpen {/if}siteBarRight calendarRightPanel col-xs-12 hideSiteBar"
		 data-showPanel="{if !AppConfig::module($MODULE, 'SHOW_RIGHT_PANEL')}0{else}1{/if}" id="rightPanel"
		 data-js="class: hideSiteBar">
		<div class="o-calendar__panel__tabs">
			<div class="btn btn-block js-toggleSiteBarRightButton toggleSiteBarRightButton hideToggleSiteBarRightButton d-none d-lg-block"
				 title="{\App\Language::translate('LBL_RIGHT_PANEL_SHOW_HIDE', $MODULE)}" data-js="click">
				<span class="fas fa-chevron-left"></span>
			</div>
			<ul class="nav nav-pills js-show-sitebar" id="rightPanelTab" role="tablist" data-js="click">
				<li class="nav-item">
					<a class="nav-link js-rightPanelEventLink active show" id="rightPanelEvent-tab" data-toggle="tab"
					   href="#rightPanelEvent" role="tab"
					   aria-controls="event"
					   aria-selected="true" data-js="trigger">
						{\App\Language::translate('LBL_EVENTS', $MODULE)}
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" id="rightPanelFilter-tab" data-toggle="tab" href="#rightPanelFilter" role="tab"
					   aria-controls="filter" aria-selected="false">{\App\Language::translate('LBL_FILTER', $MODULE)}</a>
				</li>
			</ul>
		</div>
		<div class="tab-content" id="rightPanelTabContent">
			<div class="tab-pane fade active show js-rightPanelEvent" id="rightPanelEvent" role="tabpanel"
				 aria-labelledby="rightPanelEvent-tab" data-js="class: active">
				<div class="js-qcForm qcForm"></div>
			</div>
			<div class="tab-pane fade" id="rightPanelFilter" role="tabpanel" aria-labelledby="rightPanelFilter-tab">
				<div class="o-calendar__tab--filters js-calendar__tab--filters border-bottom d-flex flex-column">
					<div class="btn-group btn-group-toggle js-switch js-switch--showType c-calendar-switch" data-toggle="buttons">
						<label class="btn btn-outline-primary c-calendar-switch__button js-switch--label-on active">
							<input type="radio" name="options" data-on-text="{\App\Language::translate('LBL_FILTER', $MODULE)}" autocomplete="off" checked>
							{\App\Language::translate('LBL_TO_REALIZE', $MODULE)}
						</label>
						<label class="btn btn-outline-primary c-calendar-switch__button js-switch--label-off">
							<input type="radio" name="options" data-off-text="{\App\Language::translate('LBL_HISTORY', $MODULE)}" autocomplete="off">
							{\App\Language::translate('LBL_HISTORY', $MODULE)}
						</label>
					</div>
					{if \AppConfig::module('Calendar', 'HIDDEN_DAYS_IN_CALENDAR_VIEW')}
						<div class="btn-group btn-group-toggle js-switch js-switch--switchingDays c-calendar-switch" data-toggle="buttons">
							<label class="btn btn-outline-primary c-calendar-switch__button js-switch--label-on active">
								<input type="radio" name="options" data-on-text="{\App\Language::translate('LBL_WORK_DAYS', $MODULE)}" autocomplete="off" checked>
								{\App\Language::translate('LBL_WORK_DAYS', $MODULE)}
							</label>
							<label class="btn btn-outline-primary c-calendar-switch__button js-switch--label-off">
								<input type="radio" name="options" data-off-text="{\App\Language::translate('LBL_ALL', $MODULE)}
" autocomplete="off" checked>
								{\App\Language::translate('LBL_ALL', $MODULE)}
							</label>
						</div>
					{/if}
					<div class="js-usersForm usersForm position-relative" data-js="perfectScrollbar | html | container"></div>
					<div class="js-groupForm groupForm position-relative" data-js="perfectScrollbar | html | container | class: u-min-h-30per"></div>
				</div>
			</div>
		</div>
	</div>
{/strip}
