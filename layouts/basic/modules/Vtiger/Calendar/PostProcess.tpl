{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Calendar-PostProcess -->
	<div class="js-calendar-right-panel {if $USER_MODEL->get('leftpanelhide')}leftPanelOpen {/if}siteBarRight calendarRightPanel col-xs-12 hideSiteBar"
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
					<li class="nav-item">
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
		<div class="tab-content" id="rightPanelTabContent">
			{if in_array('Events', $FILTERS)}
				<div class="tab-pane fade js-right-panel-event{if $DEFAULT_FILTER eq 'Events'} active show{/if}" id="rightPanelEvent" role="tabpanel"
					aria-labelledby="rightPanelEvent-tab" data-js="class: active">
					<div class="js-qc-form qc-form"></div>
				</div>
			{/if}
			{if in_array('Filter', $FILTERS)}
				<div class="tab-pane fade{if $DEFAULT_FILTER neq "Events"} active show{/if}" id="rightPanelFilter" role="tabpanel" aria-labelledby="rightPanelFilter-tab">
					<div class="o-calendar__tab--filters js-calendar__tab--filters d-flex flex-column">
						<div>
							{if \App\Config::module('Calendar', 'HIDDEN_DAYS_IN_CALENDAR_VIEW')}
								{assign var=HIDDEN_DAYS value=$HISTORY_PARAMS eq '' || !empty($HISTORY_PARAMS['hiddenDays'])}
								<div class="btn-group btn-group-toggle js-switch js-switch--switchingDays c-calendar-switch" data-toggle="buttons">
									<label class="btn btn-outline-primary c-calendar-switch__button js-switch--label-on{if $HIDDEN_DAYS} active{/if}">
										<input type="radio" name="options" data-on-text="{\App\Language::translate('LBL_WORK_DAYS', $MODULE_NAME)}" autocomplete="off"{if $HIDDEN_DAYS} checked{/if}>
										{\App\Language::translate('LBL_WORK_DAYS', $MODULE_NAME)}
									</label>
									<label class="btn btn-outline-primary c-calendar-switch__button js-switch--label-off{if !$HIDDEN_DAYS} active{/if}">
										<input type="radio" name="options" data-off-text="{\App\Language::translate('LBL_ALL', $MODULE_NAME)}" autocomplete="off"{if !$HIDDEN_DAYS} checked{/if}>
										{\App\Language::translate('LBL_ALL', $MODULE_NAME)}
									</label>
								</div>
							{/if}
							{if !empty($SHOW_TYPE)}
								{assign var=IS_TIME_CURRENT value=empty($HISTORY_PARAMS['time']) || $HISTORY_PARAMS['time'] eq 'current'}
								<div class="btn-group btn-group-toggle js-switch js-switch--showType c-calendar-switch" data-toggle="buttons">
									<label class="btn btn-outline-primary c-calendar-switch__button js-switch--label-on{if $IS_TIME_CURRENT} active{/if}">
										<input type="radio" name="options1" data-on-text="{\App\Language::translate('LBL_FILTER', $MODULE_NAME)}" autocomplete="off"{if $IS_TIME_CURRENT} checked{/if}>
										{\App\Language::translate('LBL_TO_REALIZE', $MODULE_NAME)}
									</label>
									<label class="btn btn-outline-primary c-calendar-switch__button js-switch--label-off{if !$IS_TIME_CURRENT} active{/if}">
										<input type="radio" name="options1" data-off-text="{\App\Language::translate('LBL_HISTORY', $MODULE_NAME)}" autocomplete="off"{if !$IS_TIME_CURRENT} checked{/if}>
										{\App\Language::translate('LBL_HISTORY', $MODULE_NAME)}
									</label>
								</div>
							{/if}
						</div>
						{foreach item=SIDEBARWIDGET key=index from=$LINKS}
							<div class="js-sidebar-filter-container position-relative mt-1 mb-1 {$SIDEBARWIDGET->get('linkclass')}" data-url="{$SIDEBARWIDGET->getUrl()}" data-js="perfectScrollbar | html | container"
							{if isset($SIDEBARWIDGET->get('linkdata'))}
								{foreach from=$SIDEBARWIDGET->get('linkdata') key=NAME item=DATA}
									data-{$NAME}="{$DATA}"
								{/foreach}
							{/if}>
								<div class="js-sidebar-filter-body"></div>
							</div>
						{/foreach}
					</div>
				</div>
			{/if}
		</div>
	</div>
	<!-- /tpl-Base-Calendar-PostProcess -->
{/strip}
