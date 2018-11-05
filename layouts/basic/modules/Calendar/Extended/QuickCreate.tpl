{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Calendar-Extended-QuickCreate quick-calendar-modal">
		<input value="{AppConfig::module($MODULE, 'CALENDAR_VIEW')}" type="hidden" class="js-calendar-type"
			   data-js="value">
		<input type="hidden" id="showType" value="current"/>
		{foreach key=index item=cssModel from=$STYLES}
			<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}"/>
		{/foreach}
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		<div class="modelContainer modal quickCreateContainer" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-fullscreen modal-full" role="document">
				<div class="modal-content">
					<div class="modal-header col-12 m-0 align-items-center form-row d-flex justify-content-between py-2 js-modal-header" data-js="height">
						<div class="col-xl-6 col-12">
							<h5 class="modal-title form-row text-center text-xl-left mb-2 mb-xl-0">
								{if $RECORD}
									<span class="col-12">
										<span class="fas fa-edit mr-1"></span>
										<strong class="mr-1">{$MODAL_TITLE}</strong>
									</span>
								{else}
									<span class="col-12">
										<span class="fas fa-plus mr-1"></span>
										<strong class="mr-1">{$MODAL_TITLE}:</strong>
										<strong class="text-uppercase">
											<span class="userIcon-{$MODULE_NAME} mx-1"></span>{\App\Language::translate($SINGLE_MODULE, $MODULE_NAME)}
										</strong>
									</span>
								{/if}
							</h5>
						</div>
						<div class="col-xl-6 col-12 text-center text-xl-right">
							<button class="cancelLink btn btn-danger col-12 col-md-1 ml-0 ml-md-1" aria-hidden="true"
									data-dismiss="modal" type="button" title="{\App\Language::translate('LBL_CLOSE')}">
								<span class="fas fa-times"></span>
							</button>
						</div>
					</div>
					<div class="modal-body row no-gutters m-0 pt-0">
						<div class="col-8 pt-2">
							<input type="hidden" id="hiddenDays"
								   value="{\App\Purifier::encodeHtml(\App\Json::encode(AppConfig::module('Calendar', 'HIDDEN_DAYS_IN_CALENDAR_VIEW')))}"/>
							<input value="{$WEEK_COUNT}" type="hidden" id="weekCount"/>
							<input value="{$WEEK_VIEW}" type="hidden" id="weekView"/>
							<input value="{$DAY_VIEW}" type="hidden" id="dayView"/>
							<input value="{$ALL_DAY_SLOT}" type="hidden" id="allDaySlot"/>
							<div class="tpl-Calendar-Extended-CalendarViewPreProcess">
								<div class="o-calendar__container">
									<div class="js-calendar__container" data-js="fullcalendar | offset"></div>
								</div>
							</div>
						</div>
						<div class="col-4 pl-3">
							{include file=\App\Layout::getTemplatePath('Extended/EventForm.tpl', $MODULE_NAME)}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
