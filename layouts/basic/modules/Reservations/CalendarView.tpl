{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" id="currentView" value="{$VIEW}"/>
	<input type="hidden" id="activity_view" value="{$CURRENT_USER->get('activity_view')}"/>
	<input type="hidden" id="date_format" value="{$CURRENT_USER->get('date_format')}"/>
	<input type="hidden" id="weekView" value="{$WEEK_VIEW}"/>
	<input type="hidden" id="dayView" value="{$DAY_VIEW}"/>
	<input value="{$ALL_DAY_SLOT}" type="hidden" id="allDaySlot"/>
	<div class="calendarViewContainer rowContent">
		<div class="d-flex flex-md-nowrap mt-2">
			<div class="btn-toolbar flex-nowrap mb-1 mb-sm-0 align-items-center">
				{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] CLASS='listViewMassActions' BTN_CLASS='btn-light'}
				<button class="ml-1 btn btn-light js-add u-h-fit" data-js="click">
					<span class="fas fa-plus mr-1"></span>
					{\App\Language::translate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}
				</button>
			</div>
		</div>
		<div class="o-calendar__container">
			<p class="m-0"><!-- Divider --></p>
			<div class="js-calendar__container" data-js="fullcalendar | offset"></div>
		</div>
	</div>
{/strip}
