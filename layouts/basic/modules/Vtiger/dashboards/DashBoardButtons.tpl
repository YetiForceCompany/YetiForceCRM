{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="dashboardHeading col-xs-3 col-sm-8 col-md-6">
		<input type="hidden" name="selectedModuleName" value="{$MODULE_NAME}">
		<div class="marginLeftZero">
			<div class="float-right">
				<div class="btn-toolbar">
					<div class="btn-group">
						{assign var="SPECIAL_WIDGETS" value=Settings_WidgetsManagement_Module_Model::getSpecialWidgets('Home')}
						{if $WIDGETS|count gt 0}
							<button class="btn btn-outline-secondary addButton dropdown-toggle" style="padding:7px 8px;" data-toggle="dropdown">
								<strong>{\App\Language::translate('LBL_ADD_WIDGET')}</strong>
							</button>
							<ul class="dropdown-menu widgetsList float-left addWidgetDropDown">
								{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardFilter')}
									<li class="d-none d-block d-sm-block d-md-none">
										<a href="#" class="addFilter float-left" data-linkid="{$SPECIAL_WIDGETS['Mini List']->get('linkid')}" data-block-id="0" data-width="4" data-height="4" style="height:30px;width:100%;margin:0;padding:5px;">
											{\App\Language::translate('LBL_ADD_FILTER')}
										</a>
									</li>
								{/if}
								{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardChartFilter')}
									<li class="d-none d-block d-sm-block d-md-none">
										<a class="addChartFilter float-left" data-linkid="{$SPECIAL_WIDGETS['ChartFilter']->get('linkid')}" data-block-id="0" data-width="4" data-height="4" style="height:30px;width:100%;margin:0;padding:5px;">
											{\App\Language::translate('LBL_ADD_CHART_FILTER')}
										</a>
									</li>
								{/if}
								{assign var="WIDGET" value=""}
								{foreach from=$WIDGETS item=WIDGET}
									<li>
										{if $WIDGET->get('deleteFromList')}
											<button data-widget-id="{$WIDGET->get('widgetid')}" class="removeWidgetFromList btn btn-sm btn-danger float-left" style="height:25px;margin:2px;">
												<span class='fas fa-trash-alt'></span>
											</button>
										{/if}
										<a class="float-left" onclick="Vtiger_DashBoard_Js.addWidget(this, '{$WIDGET->getUrl()}')" href="javascript:void(0);"
										   data-linkid="{$WIDGET->get('linkid')}" data-name="{$WIDGET->getName()}" data-width="{$WIDGET->getWidth()}" data-height="{$WIDGET->getHeight()}" data-id="{$WIDGET->get('widgetid')}" style="height:30px;width:100%;margin: 0;padding:5px;">
											{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}
										</a>
									</li>
								{/foreach}
							</ul>
						{else if $MODULE_PERMISSION}
							<button class="btn btn-outline-secondary addButton dropdown-toggle" data-toggle="dropdown">
								<strong>{\App\Language::translate('LBL_ADD_WIDGET')}</strong>
							</button>
							<ul class="dropdown-menu widgetsList float-left addWidgetDropDown">
								{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardFilter')}
									<li class="d-none d-block d-sm-block d-md-none">
										<a href="#" class="addFilter float-left" data-linkid="{$SPECIAL_WIDGETS['Mini List']->get('linkid')}" data-block-id="0" data-width="4" data-height="4" style="height:30px;width:100%;margin:0;padding:5px;">
											{\App\Language::translate('LBL_ADD_FILTER')}
										</a>
									</li>
								{/if}
								{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardChartFilter')}
									<li class="d-none d-block d-sm-block d-md-none">
										<a class="addChartFilter float-left" data-linkid="{$SPECIAL_WIDGETS['ChartFilter']->get('linkid')}" data-block-id="0" data-width="4" data-height="4" style="height:30px;width:100%;margin:0;padding:5px;">
											{\App\Language::translate('LBL_ADD_CHART_FILTER')}
										</a>
									</li>
								{/if}
								{assign var="WIDGET" value=""}
								{foreach from=$WIDGETS item=WIDGET}
									<li>
										{if $WIDGET->get('deleteFromList')}
											<button data-widget-id="{$WIDGET->get('widgetid')}" class="removeWidgetFromList btn btn-sm btn-danger float-left" style="height:25px;margin:2px;">
												<span class='fas fa-trash-alt'></span>
											</button>
										{/if}
										<a class="float-left" onclick="Vtiger_DashBoard_Js.addWidget(this, '{$WIDGET->getUrl()}')" href="javascript:void(0);"
										   data-linkid="{$WIDGET->get('linkid')}" data-name="{$WIDGET->getName()}" data-width="{$WIDGET->getWidth()}" data-height="{$WIDGET->getHeight()}" data-id="{$WIDGET->get('widgetid')}" style="height:30px;width:90%;margin:0;padding:5px;">
											{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}
										</a>
									</li>
								{/foreach}
								<li class="d-none d-sm-none d-md-block">
									<a href="#">
										{\App\Language::translate('LBL_NONE')}
									</a>
								</li>
							</ul>
						{/if}
					</div>
					{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardFilter')}
						<div class="btn-group d-none d-sm-none d-md-block ml-1">
							<a class="btn btn-outline-secondary addFilter" data-linkid="{$SPECIAL_WIDGETS['Mini List']->get('linkid')}" data-block-id="0" data-width="4" data-height="4">
								<strong>{\App\Language::translate('LBL_ADD_FILTER')}</strong>
							</a>
						</div>
					{/if}
					{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardChartFilter')}
						<div class="btn-group d-none d-sm-none d-md-block ml-1">
							<a class="btn btn-outline-secondary addChartFilter" data-linkid="{$SPECIAL_WIDGETS['ChartFilter']->get('linkid')}" data-block-id="0" data-width="4" data-height="4">
								<strong>{\App\Language::translate('LBL_ADD_CHART_FILTER')}</strong>
							</a>
						</div>
					{/if}
				</div>
			</div>
		</div>
	</div>
{/strip}
