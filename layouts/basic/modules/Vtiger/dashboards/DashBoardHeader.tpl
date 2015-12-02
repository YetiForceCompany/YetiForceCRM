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
	<div class="row marginLeftZero">
		<div class="pull-left">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{if $DASHBOARDHEADER_TITLE}
				{*<h2 class="pull-left">{$DASHBOARDHEADER_TITLE}</h2>*}
			{/if}
		</div>
	</div>
	<hr class="col-xs-12">
</div>

<div class='dashboardHeading'>
	<div class="row marginLeftZero">
		<div class="pull-left">
				<div class="btn-toolbar">
					<div class="btn-group listViewMassActions modOn_{$MODULE}">
						{if count($QUICK_LINKS['SIDEBARLINK']) gt 0}
							<button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
								<span class="glyphicon glyphicon-list" aria-hidden="true"></span>
								&nbsp;&nbsp;<span class="caret"></span>
							</button>
							<ul class="dropdown-menu">
								{foreach item=SIDEBARLINK from=$QUICK_LINKS['SIDEBARLINK']}
									{assign var=SIDE_LINK_URL value=decode_html($SIDEBARLINK->getUrl())}
									{assign var="EXPLODED_PARSE_URL" value=explode('?',$SIDE_LINK_URL)}
									{assign var="COUNT_OF_EXPLODED_URL" value=count($EXPLODED_PARSE_URL)}
									{if $COUNT_OF_EXPLODED_URL gt 1}
										{assign var="EXPLODED_URL" value=$EXPLODED_PARSE_URL[$COUNT_OF_EXPLODED_URL-1]}
									{/if}
									{assign var="PARSE_URL" value=explode('&',$EXPLODED_URL)}
									{assign var="CURRENT_LINK_VIEW" value='view='|cat:$CURRENT_VIEW}
									{assign var="LINK_LIST_VIEW" value=in_array($CURRENT_LINK_VIEW,$PARSE_URL)}
									{assign var="CURRENT_MODULE_NAME" value='module='|cat:$MODULE}
									{assign var="IS_LINK_MODULE_NAME" value=in_array($CURRENT_MODULE_NAME,$PARSE_URL)}
									<li>
										<a class="quickLinks" href="{$SIDEBARLINK->getUrl()}">
											{vtranslate($SIDEBARLINK->getLabel(), $MODULE)}
										</a>
									</li>
									{/foreach}
							</ul>
						{/if}
					</div>
					<div class="btn-group">
						{assign var="SPECIAL_WIDGETS" value=Settings_WidgetsManagement_Module_Model::getSpecialWidgets('Home')}
						{if $WIDGETS|count gt 0}
							<button class='btn btn-default addButton dropdown-toggle' data-toggle='dropdown'>
								<p class='hidden-xs no-margin'>
									<strong>{vtranslate('LBL_ADD_WIDGET')}</strong>
									<span class="caret"></span>
								</p>
								<span class='glyphicon glyphicon-th visible-xs-block'></span>
							</button>
							<ul class="dropdown-menu widgetsList pull-left" style="min-width:100%;text-align:left;">
								<li class='visible-xs-block'>
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
									<strong class="hidden-xs">{vtranslate('LBL_ADD_WIDGET')}</strong>
									<span class="hidden-xs caret"></span>
								<span class='glyphicon glyphicon-th visible-xs-block'></span>
							</button>
							<ul class="dropdown-menu widgetsList pull-left" style="min-width:100%;text-align:left;">
								<li class='visible-xs-block'>
									<a href='#' class='addFilter' data-linkid="{$SPECIAL_WIDGETS['Mini List']->get('linkid')}" data-block-id="0" data-width="4" data-height="3">
										{vtranslate('LBL_ADD_FILTER')}
									</a>
								</li>
							</ul>
						{/if}
					</div>
					{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardFilter')}
						<div class="btn-group hidden-xs">
							<a class='btn btn-default addFilter' data-linkid="{$SPECIAL_WIDGETS['Mini List']->get('linkid')}" data-block-id="0" data-width="4" data-height="3">
								<strong>{vtranslate('LBL_ADD_FILTER')}</strong>
							</a>
						</div>
					{/if}
				</div>
		</div>
	</div>
</div>
{/strip}
