{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Calendar-Extended-QuickCreate quickCalendarModal">
		<input value="{AppConfig::module($MODULE, 'CALENDAR_VIEW')}" type="hidden" class="js-calendar-type"
			   data-js="value">
		{foreach key=index item=cssModel from=$STYLES}
			<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}"/>
		{/foreach}
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		<div class="tpl-QuickCreate modelContainer modal quickCreateContainer" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-fullscreen modal-full" role="document">
				<div class="modal-content">
					<div class="modal-header col-12 m-0 align-items-center form-row d-flex justify-content-between pb-1">
						<div class="col-xl-6 col-12">
							<h5 class="modal-title form-row text-center text-xl-left mb-2 mb-xl-0">
								<span class="col-12">
									<span class="fas fa-plus mr-1"></span>
									<strong class="mr-1">{\App\Language::translate('LBL_QUICK_CREATE', $MODULE)}
										:</strong>
									<strong class="text-uppercase"><span
												class="userIcon-{$MODULE} mx-1"></span>{\App\Language::translate($SINGLE_MODULE, $MODULE)}</strong>
								</span>
							</h5>
						</div>
						<div class="col-xl-6 col-12 text-center text-xl-right">
							<button class="cancelLink btn btn-danger col-12 col-md-1 ml-0 ml-md-1" aria-hidden="true"
									data-dismiss="modal" type="button" title="{\App\Language::translate('LBL_CLOSE')}">
								<span class="fas fa-times"></span>
							</button>
						</div>
					</div>
					<div class="modal-body d-flex m-0">
						<div class="col-8">
							<input type="hidden" id="hiddenDays"
								   value="{\App\Purifier::encodeHtml(\App\Json::encode(AppConfig::module('Calendar', 'HIDDEN_DAYS_IN_CALENDAR_VIEW')))}"/>
							<input type="hidden" id="start_hour" value="{$CURRENT_USER->get('start_hour')}"/>
							<input type="hidden" id="end_hour" value="{$CURRENT_USER->get('end_hour')}"/>
							<input value="{$EVENT_LIMIT}" type="hidden" id="eventLimit"/>
							<input value="{$WEEK_VIEW}" type="hidden" id="weekView"/>
							<input value="{$DAY_VIEW}" type="hidden" id="dayView"/>
							<div class="tpl-Calendar-Extended-CalendarViewPreProcess">
								<div class="o-calendar__container js-calendar__container" data-js="offset">
									<div id="calendarview"></div>
								</div>
							</div>
						</div>
						<div class="col-4 py-2">
							{include file=\App\Layout::getTemplatePath('Extended/EventForm.tpl', $MODULE)}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
