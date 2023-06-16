{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Calendar-PostProcess -->
	<div class="js-calendar-right-panel {if $USER_MODEL->get('leftpanelhide')}leftPanelOpen {/if}siteBarRight calendarRightPanel col-xs-12 hideSiteBar pr-0"
		data-showPanel="{if !App\Config::module($MODULE_NAME, 'SHOW_RIGHT_PANEL')}0{else}1{/if}"
		data-js="class: hideSiteBar">
		{assign var=DEFAULT_FILTER value=current($FILTERS)}
		<div class="o-calendar__panel__tabs">
			<div class="btn btn-block js-toggle-site-bar-right-button toggleSiteBarRightButton hideToggleSiteBarRightButton d-none d-lg-block"
				title="{\App\Language::translate('LBL_RIGHT_PANEL_SHOW_HIDE', $MODULE_NAME)}" data-js="click">
				<span class="fas fa-chevron-left"></span>
			</div>
			<ul class="nav nav-pills js-show-sitebar{if count($FILTERS) neq 2} d-none{/if}" id="rightPanelTab" role="tablist" data-js="click">
				{if in_array('Events', $FILTERS)}
					<li class="nav-item" role="tab">
						<a class="nav-link js-right-panel-event-link{if $DEFAULT_FILTER eq "Events"} active show{/if}" id="rightPanelEvent-tab" data-toggle="tab"
							href="#rightPanelEvent" role="tab"
							aria-controls="event"
							aria-selected="true" data-js="trigger">
							{\App\Language::translate('LBL_EVENTS', $MODULE_NAME)}
						</a>
					</li>
				{/if}
				{if in_array('Filter', $FILTERS)}
					<li class="nav-item">
						<a class="nav-link{if $DEFAULT_FILTER neq 'Events'} active show{/if}" id="rightPanelFilter-tab" data-toggle="tab" href="#rightPanelFilter" role="tab"
							aria-controls="filter" aria-selected="false">{\App\Language::translate('LBL_FILTER', $MODULE_NAME)}</a>
					</li>
				{/if}
			</ul>
		</div>
		<div class="tab-content u-overflow-y-auto" id="rightPanelTabContent">
			{if in_array('Events', $FILTERS)}
				<div class="tab-pane fade js-right-panel-event{if $DEFAULT_FILTER eq 'Events'} active show{/if}" id="rightPanelEvent" role="tabpanel"
					aria-labelledby="rightPanelEvent-tab" data-js="class: active">
					<div class="js-qc-form qc-form px-1"></div>
				</div>
			{/if}
			{if in_array('Filter', $FILTERS)}
				<div class="tab-pane fade{if $DEFAULT_FILTER neq "Events"} active show{/if}" id="rightPanelFilter" role="tabpanel" aria-labelledby="rightPanelFilter-tab">
					<div class="o-calendar__tab--filters js-calendar__tab--filters d-flex flex-column">
						{foreach item=SIDEBARWIDGET key=index from=$LINKS}
							<div class="js-sidebar-filter-container position-relative mt-1 mb-1 {$SIDEBARWIDGET->get('linkclass')}">
								{include file=\App\Layout::getTemplatePath($SIDEBARWIDGET->get('template'), $MODULE_NAME) FILTER_DATA=$SIDEBARWIDGET->get('filterData') HISTORY_USERS=$SIDEBARWIDGET->get('historyUsers')}
							</div>
						{/foreach}
					</div>
				</div>
			{/if}
		</div>
	</div>
	<!-- /tpl-Base-Calendar-PostProcess -->
{/strip}
