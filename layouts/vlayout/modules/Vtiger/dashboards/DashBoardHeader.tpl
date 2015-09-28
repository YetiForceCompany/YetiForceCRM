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
<div class='dashboardHeading'>
	<div class="row">
		<div class="col-md-3">
			{if $DASHBOARDHEADER_TITLE}
				{*<h2 class="pull-left">{$DASHBOARDHEADER_TITLE}</h2>*}
			{/if}
		</div>
		<div class="col-md-9 h3">
			<div class="pull-right">
				<ul class="btn-toolbar">
					<li class="btn-group">
						{assign var="SPECIAL_WIDGETS" value=Settings_WidgetsManagement_Module_Model::getSpecialWidgets('Home')}
						{if $WIDGETS|count gt 0}
							<button class='btn btn-default addButton dropdown-toggle' data-toggle='dropdown'>
								<div class='mobileOff'>
									<strong>{vtranslate('LBL_ADD_WIDGET')}</strong>
									<span class="caret"></span>
								</div>
								<span class='glyphicon glyphicon-th mobileOn'></span>
							</button>
							<ul class="dropdown-menu widgetsList pull-right" style="min-width:100%;text-align:left;">
								<li class='mobileOn'>
									<a href='#' class='addFilter' data-linkid="{$SPECIAL_WIDGETS['Mini List']->get('linkid')}" data-block-id="0" data-width="4" data-height="3">
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
							<button class='btn btn-default addButton dropdown-toggle' data-toggle='dropdown'>
								<div class='mobileOff'>
									<strong>{vtranslate('LBL_ADD_WIDGET')}</strong>
									<span class="caret"></span>
								</div>
								<span class='glyphicon glyphicon-th mobileOn'></span>
							</button>
							<ul class="dropdown-menu widgetsList pull-right" style="min-width:100%;text-align:left;">
								<li class='mobileOn'>
									<a href='#' class='addFilter' data-linkid="{$SPECIAL_WIDGETS['Mini List']->get('linkid')}" data-block-id="0" data-width="4" data-height="3">
										{vtranslate('LBL_ADD_FILTER')}
									</a>
								</li>
							</ul>
						{/if}
					</li>
					{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardFilter')}
						<div class="btn-group mobileOff">
							<a class='btn btn-default addFilter' data-linkid="{$SPECIAL_WIDGETS['Mini List']->get('linkid')}" data-block-id="0" data-width="4" data-height="3">
								<strong>{vtranslate('LBL_ADD_FILTER')}</strong>
							</a>
						</div>
					{/if}
				</ul>
			</div>
		</div>
	</div>
</div>
{/strip}
