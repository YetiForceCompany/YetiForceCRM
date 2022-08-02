{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-Index -->
	{assign var=WEBSERVICE_APPS value=Settings_WebserviceApps_Module_Model::getServers()}
	<div id="layoutEditorContainer">
		<input id="selectedModuleName" type="hidden" value="{$SELECTED_MODULE_NAME}" />
		<div class="o-breadcrumb widget_header row">
			<div class="col-md-6">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
			<div class="float-right col-md-6 m-auto">
				<div class="float-right col-md-6">
					<select class="select2 form-control" name="layoutEditorModules">
						{foreach item=MODULE_NAME from=$SUPPORTED_MODULES}
							<option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_MODULE_NAME} selected {/if}>{App\Language::translate($MODULE_NAME, $MODULE_NAME)}</option>
						{/foreach}
					</select>
				</div>
				{if $SELECTED_MODULE_MODEL->isTypeChangeAllowed()}
					<div class="float-right">
						<div class="btn-group">
							<button class="btn btn-outline-primary{if !$IS_INVENTORY} active{else} js-switch--inventory{/if}" type="button" data-js="click" {if $CHANGE_MODULE_TYPE_DISABLED}disabled="disabled" {/if} data-value="{Vtiger_Module_Model::STANDARD_TYPE}" autocomplete="off">
								{App\Language::translate('LBL_BASIC_MODULE',$QUALIFIED_MODULE)}
							</button>
							<button class="btn btn-outline-primary{if $IS_INVENTORY} active{else} js-switch--inventory{/if}" type="button" data-js="click" {if $CHANGE_MODULE_TYPE_DISABLED}disabled="disabled" {/if} data-value="{Vtiger_Module_Model::ADVANCED_TYPE}" autocomplete="off">
								{App\Language::translate('LBL_ADVANCED_MODULE',$QUALIFIED_MODULE)}</button>
						</div>
					</div>
				{/if}
			</div>
		</div>
		<hr>
		<div class="alert alert-block alert-warning mb-2">
			<span class="fas fa-exclamation-triangle u-fs-xlg mr-2 float-left"></span>
			<span class="text-left">{\App\Language::translate('LBL_EDIT_MAY_AFFECT_STABILITY_DESC', $QUALIFIED_MODULE)}</span>
		</div>
		<div class="contents tabbable">
			<ul class="nav nav-tabs layoutTabs massEditTabs" role="tablist">
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB === 'detailViewLayout'}active{/if}" id="detailViewLayoutTab" data-toggle="tab" role="tab" href="#detailViewLayout" aria-selected="{if $ACTIVE_TAB === 'detailViewLayout'}true{else}false{/if}">
						<strong>{App\Language::translate('LBL_DETAILVIEW_LAYOUT', $QUALIFIED_MODULE)}</strong>
					</a>
				</li>
				{if $IS_INVENTORY}
					<li class="nav-item inventoryNav">
						<a class="nav-link {if $ACTIVE_TAB === 'inventoryViewLayout'}active{/if}" id="inventoryViewLayoutTab" data-toggle="tab" role="tab" href="#inventoryViewLayout" aria-selected="{if $ACTIVE_TAB === 'inventoryViewLayout'}true{else}false{/if}">
							<strong>{App\Language::translate('LBL_MANAGING_AN_ADVANCED_BLOCK', $QUALIFIED_MODULE)}</strong>
						</a>
					</li>
				{/if}
				{foreach item=SERVER key=SERVER_ID from=$WEBSERVICE_APPS}
					{if $SERVER['type'] === 'WebservicePremium'}
						<li class="nav-item">
							<a class="nav-link {if $ACTIVE_TAB === "webserviceApps{$SERVER_ID}" }active{/if}" id="webserviceAppsTab{$SERVER_ID}" data-toggle="tab" role="tab" href="#webserviceApps{$SERVER_ID}" aria-selected="{if $ACTIVE_TAB === "webserviceApps{$SERVER_ID}" }true{else}false{/if}">
								<strong>{\App\Purifier::encodeHTML($SERVER['name'])} ({\App\Language::translate($SERVER['type'], 'Settings:WebserviceApps')})</strong>
							</a>
						</li>
					{/if}
				{/foreach}
			</ul>
			<div class="tab-content layoutContent pt-3 pb-2 themeTableColor overflowVisible">
				<div class="tab-pane fade {if $ACTIVE_TAB === 'detailViewLayout'}active show{/if}" id="detailViewLayout" role="tabpanel" aria-labelledby="detailViewLayoutTab">
					{include file=\App\Layout::getTemplatePath('Tabs/DetailViewLayout.tpl', $QUALIFIED_MODULE)}
				</div>
				{if $IS_INVENTORY}
					<div class="tab-pane mt-0 fade {if $ACTIVE_TAB === 'inventoryViewLayout'}active show{/if}" id="inventoryViewLayout" role="tabpanel" aria-labelledby="inventoryViewLayoutTab">
						{include file=\App\Layout::getTemplatePath('Tabs/Inventory.tpl', $QUALIFIED_MODULE)}
					</div>
				{/if}
				{foreach item=SERVER key=SERVER_ID from=$WEBSERVICE_APPS}
					{if $SERVER['type'] === 'WebservicePremium'}
						<div class="tab-pane mt-0 fade {if $ACTIVE_TAB === "webserviceApps{$SERVER_ID}" }active show{/if}" id="webserviceApps{$SERVER_ID}" role="tabpanel" aria-labelledby="#webserviceAppsTab{$SERVER_ID}">
							{include file=\App\Layout::getTemplatePath('Tabs/WebserviceApps.tpl', $QUALIFIED_MODULE)}
						</div>
					{/if}
				{/foreach}
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-LayoutEditor-Index -->
{/strip}
