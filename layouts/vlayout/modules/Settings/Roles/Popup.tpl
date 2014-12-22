{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
<div id="popupPageContainer" class="popupContainer" style="min-height: 600px">
	<div class="popupContainer padding1per">
		<div class="row-fluid">
			<div class="span6">
				<span><h3>{vtranslate('LBL_ASSIGN_ROLE',"Settings:Roles")}</h3></span>
			</div>
			<div class="span6 pull-right">
				<span class="logo pull-right"><img src="{$COMPANY_LOGO->get('imagepath')}" title="{$COMPANY_LOGO->get('title')}" alt="{$COMPANY_LOGO->get('alt')}"/>
			</div>
		</div>
		<hr>
	</div>
	<div class="popupContainer row-fluid">
		<div class="clearfix treeView">
			<ul>
				<li data-role="{$ROOT_ROLE->getParentRoleString()}" data-roleid="{$ROOT_ROLE->getId()}">
					<div>
						<a href="javascript:;" class="btn btn-inverse">{$ROOT_ROLE->getName()}</a>
					</div>
					{assign var="ROLE" value=$ROOT_ROLE}
					{include file=vtemplate_path("RoleTree.tpl", "Settings:Roles")}
				</li>
			</ul>
		</div>
	</div>
</div>

<script type="text/javascript">
	jQuery('body').ready(function(){
		Settings_Roles_Js.initPopupView()
	});
</script>
{/strip}