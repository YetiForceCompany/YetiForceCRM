{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-Extended-CalendarView -->
	<input value="{$VIEW}" type="hidden" id="currentView"/>
	<input value="{\App\Purifier::encodeHtml($CURRENT_USER->get('activity_view'))}" type="hidden" id="activity_view"/>
	<input value="{$CURRENT_USER->get('hour_format')}" type="hidden" id="time_format"/>
	<input value="{$CURRENT_USER->get('start_hour')}" type="hidden" id="start_hour"/>
	<input value="{$CURRENT_USER->get('end_hour')}" type="hidden" id="end_hour"/>
	<input value="{$CURRENT_USER->get('date_format')}" type="hidden" id="date_format"/>
	<input value="current" type="hidden" id="showType"/>
	<input value="workDays" type="hidden" id="switchingDays"/>
	<input value="{$EVENT_LIMIT}" type="hidden" id="eventLimit"/>
	<input value="{$WEEK_VIEW}" type="hidden" id="weekView"/>
	<input value="{$DAY_VIEW}" type="hidden" id="dayView"/>
	<input value="{\App\Purifier::encodeHtml(\App\Json::encode(\AppConfig::module('Calendar', 'HIDDEN_DAYS_IN_CALENDAR_VIEW')))}"
		   type="hidden" id="hiddenDays"/>
	<input value="{\App\Purifier::encodeHtml($ACTIVITY_STATE_LABELS)}" type="hidden" id="activityStateLabels"/>
	<div class="calendarViewContainer rowContent">
		<div class="d-flex flex-md-nowrap mt-2">
			<div class="btn-toolbar flex-nowrap mb-1 mb-sm-0 align-items-center">
				{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] CLASS='listViewMassActions' BTN_CLASS='btn-light'}
				<button class="ml-1 btn btn-light js-add u-h-fit" data-js="click">
					<span class="fas fa-plus mr-1"></span>
					{\App\Language::translate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}
				</button>
			</div>
			<div class="ml-2 w-100">
				<div class="alert alert-info d-none mb-0" id="moduleCacheAlert" role="alert">
					<div class="d-flex">
						<div class="mr-auto align-self-center">
							{\App\Language::translate('LBL_CACHE_SELECTED_FILTERS', $MODULE_NAME)}
						</div>
						<button type="button"
								class="btn btn-warning btn-sm cacheClear px-2">{\App\Language::translate('LBL_CACHE_CLEAR', $MODULE_NAME)}</button>
						<button type="button" class="close px-2 pb-1" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
				</div>
			</div>
		</div>
		<div class="row no-gutters" id="datesColumn">
			<div class="col-sm-1 d-none d-sm-block">
				<div class="dateList">
				</div>
				<div class="subDateList">
				</div>
			</div>
			<div class="o-calendar-container col-sm-11">
				<div id="calendarview"></div>
			</div>
		</div>
	</div>
{/strip}
