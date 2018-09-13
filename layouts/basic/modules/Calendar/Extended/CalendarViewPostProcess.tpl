{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}

{strip}
	<div class="{if $USER_MODEL->get('leftpanelhide')}leftPanelOpen {/if}siteBarRight calendarRightPanel col-xs-12 hideSiteBar"
		 data-showPanel="{if !AppConfig::module($MODULE, 'SHOW_RIGHT_PANEL')}0{else}1{/if}" id="rightPanel">
		<div class="btn btn-block toggleSiteBarRightButton hideToggleSiteBarRightButton hidden-xs hidden-sm"
			 title="{\App\Language::translate('LBL_RIGHT_PANEL_SHOW_HIDE', $MODULE)}">
			<span class="glyphicon glyphicon-chevron-left"></span>
		</div>

		<ul class="nav nav-tabs" id="rightPanelTab" role="tablist">
			<li class="nav-item active">
				<a class="nav-link" id="rightPanelEvent-tab" data-toggle="tab" href="#rightPanelEvent" role="tab"
				   aria-controls="event"
				   aria-selected="true">Wydarzenia <!-- todo: tlumaczenie --></a>
			</li>
			<li class="nav-item">
				<a class="nav-link" id="rightPanelFilter-tab" data-toggle="tab" href="#rightPanelFilter" role="tab"
				   aria-controls="filter" aria-selected="false">Filtruj <!-- todo: tlumaczenie --></a>
			</li>
		</ul>
		<div class="tab-content" id="rightPanelTabContent">
			<div class="tab-pane fade active in" id="rightPanelEvent" role="tabpanel"
				 aria-labelledby="rightPanelEvent-tab">
				<div class="qcForm"></div>
			</div>
			<div class="tab-pane fade" id="rightPanelFilter" role="tabpanel" aria-labelledby="rightPanelFilter-tab">
				<div class="filterButtons">
					<span class="btn btn-danger calendarFilters" id="meetingSwitchingDays"
						  data-type="Meeting">SPOTKANIE</span>
					<span class="btn btn-success calendarFilters" id="taskSwitchingDays" data-type="Task">ZADANIE</span>
					<span class="btn btn-warning calendarFilters" id="phoneSwitchingDays"
						  data-type="Call">TELEFON</span>
				</div>
				<div style="border-bottom: 1px solid #ddd;">
					<div class="usersForm"></div>
					<div class="groupForm"></div>
				</div>
			</div>
		</div>
	</div>
{/strip}
