{*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
	<div class="tpl-Settings-Roles-Index">
		<div class="o-breadcrumb widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="clearfix treeView">
			<ul>
				<li data-role="{$ROOT_ROLE->getParentRoleString()}" data-roleid="{$ROOT_ROLE->getId()}">
					<div class="toolbar-handle">
						<a href="javascript:;" class="btn btn-light draggable droppable">{\App\Language::translate($ROOT_ROLE->getName(), $QUALIFIED_MODULE)}</a>
						<div class="toolbar" title="{\App\Language::translate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}">
							<button class="btn btn-success ml-1 js-upload-logo" type="button" data-js="click"
									data-url="index.php?module=Roles&parent=Settings&view=UploadLogo">
								<span class="fas fa-image"
									  title="{\App\Language::translate('LBL_SELECT',$QUALIFIED_MODULE)}"></span>
							</button>
							&nbsp;<a href="{$ROOT_ROLE->getCreateChildUrl()}" data-url="{$ROOT_ROLE->getCreateChildUrl()}" data-action="modal"><span class="fas fa-plus-circle"></span></a>
						</div>
					</div>
					{assign var="ROLE" value=$ROOT_ROLE}
					{include file=\App\Layout::getTemplatePath('RoleTree.tpl', 'Settings:Roles')}
				</li>
			</ul>
		</div>
	</div>
{/strip}
