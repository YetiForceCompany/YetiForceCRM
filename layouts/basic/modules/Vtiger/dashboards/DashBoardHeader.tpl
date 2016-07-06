{*<!--
/************************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
************************************************************************************/
-->*}
{strip}
	<div class="widget_header row">
		<div class="col-xs-9 col-sm-6">
			<div class="btn-group listViewMassActions modOn_{$MODULE} pull-left paddingRight10">
				{include file='ButtonViewLinks.tpl'|@vtemplate_path LINKS=$QUICK_LINKS['SIDEBARLINK'] BTN_GROUP=false}
			</div>
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
		<div class="dashboardHeading col-xs-3 col-sm-6">
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
								<ul class="dropdown-menu widgetsList pull-left" style="min-width:100%;text-align:left;">
									<li class="visible-xs-block">
										<a href="#" class="addFilter" data-linkid="{$SPECIAL_WIDGETS['Mini List']->get('linkid')}" data-block-id="0" data-width="4" data-height="3">
											{vtranslate('LBL_ADD_FILTER')}
										</a>
									</li>
									{assign var="WIDGET" value=""}
									{foreach from=$WIDGETS item=WIDGET}
										<li><a onclick="Vtiger_DashBoard_Js.addWidget(this, '{$WIDGET->getUrl()}')" href="javascript:void(0);"
											   data-linkid="{$WIDGET->get('linkid')}" data-name="{$WIDGET->getName()}" data-width="{$WIDGET->getWidth()}" data-height=	"{$WIDGET->getHeight()}" data-id="{$WIDGET->get('widgetid')}">
												{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</a>
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
								<a class="btn btn-default addFilter" data-linkid="{$SPECIAL_WIDGETS['Mini List']->get('linkid')}" data-block-id="0" data-width="4" data-height="3">
									<strong>{vtranslate('LBL_ADD_FILTER')}</strong>
								</a>
							</div>
						{/if}
						{if $USER_PRIVILEGES_MODEL->isAdminUser()}
							<div class="btn-group hidden-xs">
								<a class="btn btn-default addChartFilter" data-linkid="{$SPECIAL_WIDGETS['ChartFilter']->get('linkid')}" data-block-id="0" data-width="4" data-height="3">
									<strong>{vtranslate('LBL_ADD_CHART_FILTER')}</strong>
								</a>
							</div>
						{/if}
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
