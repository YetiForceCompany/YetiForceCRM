{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Calendar-CalendarView -->
	<input value="{\App\Purifier::encodeHtml($CURRENT_USER->getDetail('activity_view'))}" type="hidden" id="activity_view" />
	<input value="{$CURRENT_USER->getDetail('date_format')}" type="hidden" id="date_format" />
	<input value="current" type="hidden" id="showType" />
	<input value="workDays" type="hidden" id="switchingDays" />
	<input value="{$EVENT_CREATE}" type="hidden" id="eventCreate" />
	<input value="{$EVENT_EDIT}" type="hidden" id="eventEdit" />
	<input value="{$WEEK_COUNT}" type="hidden" id="weekCount" />
	<input value="{$WEEK_VIEW}" type="hidden" id="weekView" />
	<input value="{$DAY_VIEW}" type="hidden" id="dayView" />
	<input value="{$ALL_DAY_SLOT}" type="hidden" id="allDaySlot" />
	<input value="{\App\Purifier::encodeHtml(\App\Json::encode(\App\Config::module('Calendar', 'HIDDEN_DAYS_IN_CALENDAR_VIEW')))}"
		type="hidden" id="hiddenDays" />
	<input value="{\App\Purifier::encodeHtml(\App\Json::encode($HISTORY_PARAMS))}" type="hidden" id="historyParams" />
	<input value="{\App\Purifier::encodeHtml(\App\Config::module('Calendar', 'SHOW_EDIT_FORM'))}" type="hidden" id="showEditForm" />
	<div class="calendarViewContainer rowContent js-css-element-queries js-calendar-container" data-js="css-element-queries|container">
		<div class="o-calendar__container mt-2" data-js="offset">
			<div class="d-none js-calendar__header-buttons">
				<div class="js-calendar__view-btn mb-1 mb-sm-0 mr-1">
					{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] CLASS='listViewMassActions u-remove-dropdown-icon' BTN_CLASS='btn-light o-calendar__view-btn d-flex align-items-center'}
				</div>
				<div class="o-calendar__filter js-calendar__filter-container" data-js="clone container">
					{if $CUSTOM_VIEWS|@count gt 0}
						<ul class="nav nav-pills u-w-fit js-calendar__extended-filter-tab" data-js="change"
							role="tablist">
							{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
								{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
									{if $CUSTOM_VIEW->isFeatured()}
										<li class="nav-item js-filter-tab c-tab--small font-weight-bold"
											data-cvid="{$CUSTOM_VIEW->getId()}" data-js="click">
											<a class="nav-link{if !empty($HISTORY_PARAMS['cvid']) && $HISTORY_PARAMS['cvid'] eq {$CUSTOM_VIEW->getId()}} active show{/if}"
												href="#"
												{if $CUSTOM_VIEW->get('color')}style="color: {$CUSTOM_VIEW->get('color')};" {/if}
												data-toggle="tab" role="tab"
												aria-selected="{if !empty($HISTORY_PARAMS['cvid']) && $HISTORY_PARAMS['cvid'] eq {$CUSTOM_VIEW->getId()}}true{else}false{/if}">
												{\App\Language::translate($CUSTOM_VIEW->get('viewname'), $MODULE_NAME)}
												{if $CUSTOM_VIEW->get('description')}
													<span class="js-popover-tooltip ml-1" data-toggle="popover"
														data-placement="top"
														data-content="{\App\Purifier::encodeHtml($CUSTOM_VIEW->get('description'))}" data-js="popover">
														<span class="fas fa-info-circle"></span>
													</span>
												{/if}
											</a>
										</li>
									{/if}
								{/foreach}
							{/foreach}
						</ul>
					{/if}
					<a class="o-calendar__clear-btn btn btn-warning d-none ml-1 js-calendar__clear-filters js-popover-tooltip" role="button" data-content="{\App\Language::translate("LBL_REMOVE_FILTERING", $MODULE_NAME)}"
						data-js="class: d-none | popover">
						<span class="fas fa-eraser" title="{\App\Language::translate("LBL_REMOVE_FILTERING", $MODULE_NAME)}"></span>
					</a>
				</div>
			</div>
			<div class="js-calendar__container" data-js="fullcalendar | offset"></div>
		</div>
	</div>
	<!-- /tpl-Base-Calendar-CalendarView -->
{/strip}
