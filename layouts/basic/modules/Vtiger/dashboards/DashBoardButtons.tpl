{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="dashboardHeading col-xs-3 col-sm-8 col-md-6">
		<input type="hidden" name="selectedModuleName" value="{$MODULE_NAME}">
		<div class="marginLeftZero">
			<div class="pull-right">
				<div class="btn-toolbar">
					<div class="btn-group">
						{assign var="SPECIAL_WIDGETS" value=Settings_WidgetsManagement_Module_Model::getSpecialWidgets('Home')}
						{if $WIDGETS|count gt 0}
							<button class="btn btn-default addButton dropdown-toggle" data-toggle="dropdown">
								<p class="hidden-xs no-margin">
									<strong>{vtranslate('LBL_ADD_WIDGET')}</strong>
									<span class="caret"></span>
								</p>
								<span class="glyphicon glyphicon-th visible-xs-block"></span>
							</button>
							<ul class="dropdown-menu widgetsList pull-left addWidgetDropDown" style="min-width:100%;text-align:left;">
								<li class="visible-xs-block">
									<a href="#" class="addFilter" data-linkid="{$SPECIAL_WIDGETS['Mini List']->get('linkid')}" data-block-id="0" data-width="4" data-height="3">
										{vtranslate('LBL_ADD_FILTER')}
									</a>
								</li>
								{assign var="WIDGET" value=""}
								{foreach from=$WIDGETS item=WIDGET}
									<li><a class="pull-left" onclick="Vtiger_DashBoard_Js.addWidget(this, '{$WIDGET->getUrl()}')" href="javascript:void(0);"
										   data-linkid="{$WIDGET->get('linkid')}" data-name="{$WIDGET->getName()}" data-width="{$WIDGET->getWidth()}" data-height="{$WIDGET->getHeight()}" data-id="{$WIDGET->get('widgetid')}">
											{vtranslate($WIDGET->getTitle(), $MODULE_NAME)} </a>
										{if $WIDGET->get('deleteFromList')}
											<button data-widget-id="{$WIDGET->get('widgetid')}" class="removeWidgetFromList btn btn-xs btn-danger pull-right">
												<span class='glyphicon glyphicon-trash'></span>
											</button>
										{/if}
									</li>
								{/foreach}
							</ul>
						{else if $MODULE_PERMISSION}
							<button class="btn btn-default addButton dropdown-toggle" data-toggle="dropdown">
								<strong class="hidden-xs">{vtranslate('LBL_ADD_WIDGET')}</strong>
								<span class="hidden-xs caret"></span>
								<span class="glyphicon glyphicon-th visible-xs-block"></span>
							</button>
							<ul class="dropdown-menu widgetsList pull-left" style="min-width:100%;text-align:left;">
								<li class="visible-xs-block">
									<a href="#" class="addFilter" data-linkid="{$SPECIAL_WIDGETS['Mini List']->get('linkid')}" data-block-id="0" data-width="4" data-height="3">
										{vtranslate('LBL_ADD_FILTER')}
									</a>
								</li>
								<li class="hidden-xs">
									<a href="#">
										{vtranslate('LBL_NONE')}
									</a>
								</li>
							</ul>
						{/if}
					</div>
					{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardFilter')}
						<div class="btn-group hidden-xs">
							<a class="btn btn-default addFilter" data-linkid="{$SPECIAL_WIDGETS['Mini List']->get('linkid')}" data-block-id="0" data-width="4" data-height="4">
								<strong>{vtranslate('LBL_ADD_FILTER')}</strong>
							</a>
						</div>
					{/if}
					{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardChartFilter')}
						<div class="btn-group hidden-xs">
							<a class="btn btn-default addChartFilter" data-linkid="{$SPECIAL_WIDGETS['ChartFilter']->get('linkid')}" data-block-id="0" data-width="4" data-height="4">
								<strong>{vtranslate('LBL_ADD_CHART_FILTER')}</strong>
							</a>
						</div>
					{/if}
				</div>
			</div>
		</div>
	</div>
{/strip}
