{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" id="currentView" value="{$VIEW}"/>
	<input type="hidden" id="activity_view" value="{\App\Purifier::encodeHtml($CURRENT_USER->get('activity_view'))}"/>
	<input type="hidden" id="date_format" value="{$CURRENT_USER->get('date_format')}"/>
	<input type="hidden" id="showType" value="current"/>
	<input type="hidden" id="switchingDays" value="workDays"/>
	<input value="{$EVENT_CREATE}" type="hidden" id="eventCreate"/>
	<input type="hidden" id="weekView" value="{$WEEK_VIEW}"/>
	<input type="hidden" id="dayView" value="{$DAY_VIEW}"/>
	<input value="{$ALL_DAY_SLOT}" type="hidden" id="allDaySlot"/>
	<input type="hidden" id="hiddenDays"
		   value="{\App\Purifier::encodeHtml(\App\Json::encode(AppConfig::module('Calendar', 'HIDDEN_DAYS_IN_CALENDAR_VIEW')))}"/>
	<input type="hidden" id="activityStateLabels" value="{\App\Purifier::encodeHtml($ACTIVITY_STATE_LABELS)}"/>
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
		<div class="o-calendar__container">
			<div class="js-calendar__container" data-js="fullcalendar | offset"></div>
		</div>
	</div>
{/strip}
