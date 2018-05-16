{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="dashboardHeading d-flex ml-auto my-2 pr-1">
		<input type="hidden" name="selectedModuleName" value="{$MODULE_NAME}">
		{assign var="SPECIAL_WIDGETS" value=Settings_WidgetsManagement_Module_Model::getSpecialWidgets('Home')}
		{if $WIDGETS|count gt 0}
			<button class="btn btn-outline-secondary addButton dropdown-toggle" data-toggle="dropdown">
				<strong class="d-none d-md-inline"><span
							class="fas fa-plus mr-1"></span>{\App\Language::translate('LBL_ADD_WIDGET')}</strong>
			</button>
			<ul class="dropdown-menu widgetsList addWidgetDropDown">
				{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardFilter')}
					<li class="dropdown-item d-none d-block d-sm-block d-md-none">
						<a href="#" class="addFilter" data-linkid="{$SPECIAL_WIDGETS['Mini List']->get('linkid')}"
						   data-block-id="0" data-width="4" data-height="4">
							{\App\Language::translate('LBL_ADD_FILTER')}
						</a>
					</li>
				{/if}
				{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardChartFilter')}
					<li class="dropdown-item d-none d-block d-sm-block d-md-none">
						<a class="addChartFilter" data-linkid="{$SPECIAL_WIDGETS['ChartFilter']->get('linkid')}"
						   data-block-id="0" data-width="4" data-height="4" href="#" role="button">
							{\App\Language::translate('LBL_ADD_CHART_FILTER')}
						</a>
					</li>
				{/if}
				{assign var="WIDGET" value=""}
				{foreach from=$WIDGETS item=WIDGET}
					<li class="dropdown-item">
						<a class="pl-1 dropdown-item"
						   onclick="Vtiger_DashBoard_Js.addWidget(this, '{$WIDGET->getUrl()}')"
						   href="javascript:void(0);"
						   data-linkid="{$WIDGET->get('linkid')}" data-name="{$WIDGET->getName()}"
						   data-width="{$WIDGET->getWidth()}" data-height="{$WIDGET->getHeight()}"
						   data-id="{$WIDGET->get('widgetid')}">
							{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}
						</a>
						{if $WIDGET->get('deleteFromList')}
							<button data-widget-id="{$WIDGET->get('widgetid')}"
									class="removeWidgetFromList btn btn-danger btn-sm m-1 p-1">
								<span class='fas fa-trash-alt'></span>
							</button>
						{/if}
					</li>
				{/foreach}
			</ul>
		{elseif $MODULE_PERMISSION}
			<button class="btn btn-outline-secondary addButton dropdown-toggle" data-toggle="dropdown">
				<strong class="d-none d-md-inline"><span
							class="fas fa-plus mr-1"></span>{\App\Language::translate('LBL_ADD_WIDGET')}</strong>
			</button>
			<ul class="dropdown-menu widgetsList addWidgetDropDown">
				{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardFilter')}
					<li class="dropdown-item d-block d-md-none">
						<a href="#" class="addFilter" data-linkid="{$SPECIAL_WIDGETS['Mini List']->get('linkid')}"
						   data-block-id="0" data-width="4" data-height="4">
							{\App\Language::translate('LBL_ADD_FILTER')}
						</a>
					</li>
				{/if}
				{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardChartFilter')}
					<li class="dropdown-item d-block d-md-none">
						<a class="addChartFilter" data-linkid="{$SPECIAL_WIDGETS['ChartFilter']->get('linkid')}"
						   data-block-id="0" data-width="4" data-height="4"
						   href="#" role="button">
							{\App\Language::translate('LBL_ADD_CHART_FILTER')}
						</a>
					</li>
				{/if}
				{assign var="WIDGET" value=""}
				{foreach from=$WIDGETS item=WIDGET}
					<li class="dropdown-item">
						{if $WIDGET->get('deleteFromList')}
							<button data-widget-id="{$WIDGET->get('widgetid')}"
									class="removeWidgetFromList btn btn-sm btn-danger">
								<span class='fas fa-trash-alt'></span>
							</button>
						{/if}
						<a onclick="Vtiger_DashBoard_Js.addWidget(this, '{$WIDGET->getUrl()}')"
						   href="javascript:void(0);"
						   data-linkid="{$WIDGET->get('linkid')}" data-name="{$WIDGET->getName()}"
						   data-width="{$WIDGET->getWidth()}" data-height="{$WIDGET->getHeight()}"
						   data-id="{$WIDGET->get('widgetid')}">
							{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}
						</a>
					</li>
				{/foreach}
				<li class="dropdown-item d-none d-md-block pl-1">
					<a href="#">{\App\Language::translate('LBL_NONE')}</a>
				</li>
			</ul>
		{/if}
		{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardFilter')}
			<button class="btn btn-outline-secondary addFilter d-none d-md-block ml-1"
					data-linkid="{$SPECIAL_WIDGETS['Mini List']->get('linkid')}" data-block-id="0" data-width="4"
					data-height="4">
				<strong><span class="fas fa-filter mr-1"></span>{\App\Language::translate('LBL_ADD_FILTER')}</strong>
			</button>
		{/if}
		{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardChartFilter')}
			<button class="btn btn-outline-secondary addChartFilter d-none d-md-block ml-1"
					data-linkid="{$SPECIAL_WIDGETS['ChartFilter']->get('linkid')}" data-block-id="0" data-width="4"
					data-height="4">
				<strong><span class="fas fa-chart-pie mr-1"></span>{\App\Language::translate('LBL_ADD_CHART_FILTER')}
				</strong>
			</button>
		{/if}
	</div>
{/strip}
