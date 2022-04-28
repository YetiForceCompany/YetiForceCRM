{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-QuickCreate -->
	<div class="c-calendar-quickcreate quick-calendar-modal">
		<input value="{App\Config::module($MODULE, 'CALENDAR_VIEW')}" type="hidden" class="js-calendar-type" data-js="value">
		<input type="hidden" id="showType" value="current" />
		{foreach key=index item=cssModel from=$STYLES}
			<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" />
		{/foreach}
		<div class="modelContainer modal quickCreateContainer" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-fullscreen modal-full" role="document">
				<div class="modal-content">
					<div class="modal-header col-12 m-0 align-items-center form-row d-flex justify-content-between py-2 js-modal-header" data-js="height">
						<div class="col-xl-6 col-12">
							<h5 class="modal-title form-row text-center text-xl-left mb-2 mb-xl-0">
								<div class="js-modal-title__container col-12">
									<div class="js-modal-title--add {if $RECORD} d-none{/if}">
										<span class="fas fa-plus mr-1"></span>
										<strong class="mr-1">{\App\Language::translate('LBL_QUICK_CREATE', $MODULE_NAME)}:</strong>
										<strong class="text-uppercase">
											<span class="yfm-{$MODULE_NAME} mx-1"></span>{\App\Language::translate($SINGLE_MODULE, $MODULE_NAME)}
										</strong>
									</div>
									<div class="js-modal-title--status d-none">
										<span class="fas fa-question-circle mr-1"></span>
										<strong class="mr-1">{\App\Language::translate('LBL_SET_RECORD_STATUS', $MODULE_NAME)}</strong>
									</div>
									<div class="js-modal-title--edit{if !$RECORD} d-none{/if}">
										<span class="yfi yfi-full-editing-view mr-1"></span>
										<strong class="mr-1">{\App\Language::translate('LBL_EDIT_EVENT',$MODULE_NAME)}</strong>
									</div>
								</div>
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
						<div class="col col-lg-8 pt-2">
							<input type="hidden" id="switchingDays" value="workDays" />
							<input type="hidden" id="hiddenDays"
								value="{\App\Purifier::encodeHtml(\App\Json::encode(App\Config::module('Calendar', 'HIDDEN_DAYS_IN_CALENDAR_VIEW')))}" />
							<input value="{$WEEK_COUNT}" type="hidden" id="weekCount" />
							<input value="{$WEEK_VIEW}" type="hidden" id="weekView" />
							<input value="{$DAY_VIEW}" type="hidden" id="dayView" />
							<input value="{$ALL_DAY_SLOT}" type="hidden" id="allDaySlot" />
							<div class="c-calendar-view">
								<div class="o-calendar__container">
									<div class="js-calendar__container" data-js="fullcalendar | offset"></div>
								</div>
							</div>
						</div>
						<div class="js-calendar-right-panel col col-lg-4 pl-3" data-js="container | html">
							<div class="js-qc-form">
								{include file=\App\Layout::getTemplatePath('Calendar/EventForm.tpl', $MODULE_NAME)}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Calendar-QuickCreate -->
{/strip}
