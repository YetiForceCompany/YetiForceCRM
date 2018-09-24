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
	<div class="calendarViewContainer rowContent js-css-element-queries" data-js="css-element-queries">
		<div class="o-calendar__container u-overflow-y-auto pt-2" data-js="offset">
			<div class="d-flex justify-content-between">
				<div class="d-flex">
					<div class="btn-toolbar flex-nowrap mb-1 mb-sm-0 align-items-center mr-1">
						{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] CLASS='listViewMassActions w-100 u-remove-dropdown-icon u-text-ellipsis' BTN_CLASS='btn-light o-calendar__view-btn w-100'}
					</div>
					{if $CUSTOM_VIEWS|@count gt 0}
						<ul class="nav nav-pills u-w-fit js-calendar-extended-filter-tab" data-js="change"
							role="tablist">
							{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
								{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
									{if $CUSTOM_VIEW->isFeatured()}
										<li class="nav-item js-filter-tab c-tab--small font-weight-bold"
											data-cvid="{$CUSTOM_VIEW->getId()}" data-js="click">
											<a class="nav-link{if $VIEWID == $CUSTOM_VIEW->getId()} active{/if}"
											   href="#"
											   {if $CUSTOM_VIEW->get('color')}style="color: {$CUSTOM_VIEW->get('color')};"{/if}
											   data-toggle="tab" role="tab"
											   aria-selected="{if $VIEWID == $CUSTOM_VIEW->getId()}true{else}false{/if}">
												{\App\Language::translate($CUSTOM_VIEW->get('viewname'), $MODULE)}
												{if $CUSTOM_VIEW->get('description')}
													<span class="js-popover-tooltip fas fa-info-circle"
														  data-js="popover"
														  data-placement="auto right"
														  data-content="{\App\Purifier::encodeHtml($CUSTOM_VIEW->get('description'))}"></span>
												{/if}
											</a>
										</li>
									{/if}
								{/foreach}
							{/foreach}
						</ul>
					{/if}
				</div>
				<a class="o-calendar__clear-btn btn btn-warning d-none js-calendar-clear-filters" role="button"
				   data-js="class: d-none">
					<span class="fas fa-eraser mr-1"></span>
					<span class="o-calendar__clear-btn__text">{\App\Language::translate("LBL_REMOVE_FILTERING", $MODULE)}</span>
				</a>
			</div>
			<div class="js-calendar__container" id="calendarview"></div>
		</div>
	</div>
	<!-- /tpl-Calendar-Extended-CalendarView -->
{/strip}
