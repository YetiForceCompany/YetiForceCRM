{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=ACCESSIBLE_USERS value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
	{assign var=ACCESSIBLE_GROUPS value=\App\Fields\Owner::getInstance()->getAccessibleGroups()}
	{assign var=CURRENTUSERID value=$CURRENTUSER->getId()}
	<div class="tpl-dashboards-Calendar">
		<div class="dashboardWidgetHeader">
			<div class="row">
				<div class="col-sm-8">
					<h5 class="dashboardTitle h6"
						 title="{App\Purifier::encodeHtml(App\Language::translate($WIDGET->getTitle(), $MODULE_NAME))}">
						<strong>&nbsp;&nbsp;{\App\Language::translate($WIDGET->getTitle(),$MODULE_NAME)}</strong></h5>
				</div>
				<div class="col-sm-4">
					<div class="box float-right">
						{if \App\Privilege::isPermitted('Calendar', 'CreateView')}
							<a class="btn btn-light btn-sm" role="button" onclick="Vtiger_Header_Js.getInstance().quickCreateModule('Calendar'); return false;">
								<span class='fas fa-plus' title="{\App\Language::translate('LBL_ADD_RECORD')}"></span>
							</a>
						{/if}
						{include file=\App\Layout::getTemplatePath('dashboards/DashboardHeaderIcons.tpl', $MODULE_NAME)}
					</div>
				</div>
			</div>
			<hr class="widgetHr"/>
			<div class="row">
				<div class="col-sm-6">
					{if AppConfig::module('Calendar','DASHBOARD_CALENDAR_WIDGET_FILTER_TYPE') == 'list'}
						<div class="input-group input-group-sm">
						<span class="input-group-prepend">
							<span class="input-group-text">
								<span class="fas fa-filter iconMiddle margintop3"></span>
							</span>
						</span>
							<select class="widgetFilter form-control customFilter" name="customFilter"
									title="{\App\Language::translate('LBL_CUSTOM_FILTER')}">
								{assign var=CUSTOM_VIEWS value=CustomView_Record_Model::getAllByGroup('Calendar')}
								{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
									<optgroup
											label='{\App\Language::translate('LBL_CV_GROUP_'|cat:strtoupper($GROUP_LABEL))}'>
										{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
											<option value="{$CUSTOM_VIEW->get('cvid')}" {if $DATA['customFilter'] eq $CUSTOM_VIEW->get('cvid')} selected {/if}>{\App\Language::translate($CUSTOM_VIEW->get('viewname'), 'Calendar')}</option>
										{/foreach}
									</optgroup>
								{/foreach}
							</select>
						</div>
					{/if}
					{if AppConfig::module('Calendar','DASHBOARD_CALENDAR_WIDGET_FILTER_TYPE') == 'switch'}
						{assign var=CURRENT_STATUS value=Calendar_Module_Model::getComponentActivityStateLabel('current')}
						{assign var=HISTORY_STATUS value=Calendar_Module_Model::getComponentActivityStateLabel('history')}
						<div class="btn-group btn-group-toggle" data-toggle="buttons">
							<label class="btn btn-sm btn-outline-primary active">
								<input class="js-switch--calendar" type="radio" name="options" id="option1"
									   data-js="change"
									   data-on-text="{\App\Language::translate('LBL_TO_REALIZE')}" autocomplete="
							   off" checked
								> {\App\Language::translate('LBL_TO_REALIZE')}
							</label>
							<label class="btn btn-sm btn-outline-primary">
								<input class="js-switch--calendar" type="radio" name="options" id="option2"
									   data-js="change"
									   data-off-text="{\App\Language::translate('History')}" autocomplete="
							   off"
								> {\App\Language::translate('History')}
							</label>
						</div>
						<input type="hidden" value="current" data-current="{implode(',',$CURRENT_STATUS)}"
							   data-history="{implode(',',$HISTORY_STATUS)}" class="widgetFilterSwitch">
					{/if}
				</div>
				<div class="col-sm-6">
					{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
				</div>
			</div>
			<div class="row marginTop2">
				<div class="col-sm-12">
					<div class="headerCalendar pinUnpinShortCut row">
						<div class="col-2">
							<button class="btn btn-light btn-sm" data-type="fc-prev-button">
								<span class="fas fa-chevron-left" title="{\App\Language::translate('LBL_PREVIOUS')}"></span>
							</button>
						</div>
						<div class="col-8 month textAlignCenter paddingRightZero"></div>
						<div class="col-2">
							<button class="btn btn-light btn-sm  float-right" data-type="fc-next-button">
								<span class="fas fa-chevron-right" title="{\App\Language::translate('LBL_NEXT')}"></span>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="dashboardWidgetContent dashboardWidgetCalendar">
			{include file=\App\Layout::getTemplatePath('dashboards/CalendarContents.tpl', $MODULE_NAME) WIDGET=$WIDGET}
		</div>
	</div>
{/strip}
