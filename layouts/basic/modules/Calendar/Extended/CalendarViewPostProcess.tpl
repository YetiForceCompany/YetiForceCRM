{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Calendar-Extended-CalendarViewPostProcess js-calendarRightPanel {if $USER_MODEL->get('leftpanelhide')}leftPanelOpen {/if}siteBarRight calendarRightPanel col-xs-12 hideSiteBar"
		 data-showPanel="{if !AppConfig::module($MODULE, 'SHOW_RIGHT_PANEL')}0{else}1{/if}" id="rightPanel"
		 data-js="class: hideSiteBar">
		<div class="btn btn-block js-toggleSiteBarRightButton toggleSiteBarRightButton hideToggleSiteBarRightButton d-none d-md-block"
			 title="{\App\Language::translate('LBL_RIGHT_PANEL_SHOW_HIDE', $MODULE)}" data-js="click">
			<span class="fas fa-chevron-left"></span>
		</div>
		<ul class="nav nav-tabs" id="rightPanelTab" role="tablist">
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
		<div class="tab-content" id="rightPanelTabContent">
			<div class="tab-pane fade active show js-rightPanelEvent" id="rightPanelEvent" role="tabpanel"
				 aria-labelledby="rightPanelEvent-tab" data-js="class: active">
				<div class="js-qcForm qcForm"></div>
			</div>
			<div class="tab-pane fade" id="rightPanelFilter" role="tabpanel" aria-labelledby="rightPanelFilter-tab">
				<div class="border-bottom">
					<div class="js-usersForm usersForm"></div>
					<div class="js-groupForm groupForm"></div>
				</div>
			</div>
		</div>
	</div>
{/strip}
