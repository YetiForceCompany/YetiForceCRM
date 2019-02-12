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
	<div class="" id="moduleManagerContents">
		<div class="widget_header row mb-2">
			<div class="col-md-7 d-flex align-items-center">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
			<div class="col-md-5">
				<span class="btn-toolbar float-lg-right mt-1">
					<span class="btn-group mr-sm-0 mr-lg-1 c-btn-block-md-down">
						<button class="btn btn-success createModule c-btn-block-md-down" type="button">
							<span class="fas fa-desktop"></span>&nbsp;&nbsp;
							<strong>{\App\Language::translate('LBL_CREATE_MODULE', $QUALIFIED_MODULE)}</strong>
						</button>
					</span>
					{if \AppConfig::main('systemMode') !== 'demo'}
						<span class="btn-group c-btn-block-md-down mt-1 mt-lg-0">
							<button class="btn btn-primary c-btn-block-md-down" type="button" onclick='window.location.href = "{$IMPORT_USER_MODULE_URL}"'>
								<span class="fas fa-download"></span>&nbsp;&nbsp;
								<strong>{\App\Language::translate('LBL_IMPORT_ZIP', $QUALIFIED_MODULE)}</strong>
							</button>
						</span>
					{/if}
				</span>
			</div>
		</div>
		<div class="contents">
			<div class="js-scrollbar position-relative" data-js="container">
				<table class="table table-bordered table-sm position-relative">
					<thead>
					<tr class="blockHeader">
						<th>
							<span>{\App\Language::translate('LBL_LIBRARY_NAME', $QUALIFIED_MODULE)}</span>
						</th>
						<th>
							<span>{\App\Language::translate('LBL_LIBRARY_DIR', $QUALIFIED_MODULE)}</span>
						</th>
						<th>
							<span>{\App\Language::translate('LBL_LIBRARY_URL', $QUALIFIED_MODULE)}</span>
						</th>
						<th>
							<span>{\App\Language::translate('LBL_LIBRARY_STATUS', $QUALIFIED_MODULE)}</span>
						</th>
						<th>
							<span>{\App\Language::translate('LBL_LIBRARY_ACTION', $QUALIFIED_MODULE)}</span>
						</th>
					</tr>
					</thead>
					<tbody>
					{foreach key=NAME item=LIBRARY from=Settings_ModuleManager_Library_Model::getAll()}
						<tr>
							<td><strong>{$NAME}</strong></td>
							<td class="text-nowrap">{$LIBRARY['dir']}</td>
							<td class="text-nowrap"><a href="{$LIBRARY['url']}">{$LIBRARY['url']}</a></td>
							<td>
								{if $LIBRARY['status'] == 1}
									<span class="badge badge-success bigLabel">
										{\App\Language::translate('LBL_LIBRARY_DOWNLOADED', $QUALIFIED_MODULE)}
										<span class="far fa-check-circle ml-1"></span>
									</span>
								{elseif $LIBRARY['status'] == 2}
									<span class="badge badge-warning bigLabel">
										{\App\Language::translate('LBL_LIBRARY_NEEDS_UPDATING', $QUALIFIED_MODULE)}
										<span class="fas fa-info-circle ml-1"></span>
									</span>
								{else}
									<span class="badge badge-danger bigLabel">
										{\App\Language::translate('LBL_LIBRARY_NO_DOWNLOAD', $QUALIFIED_MODULE)}
										<span class="fas fa-ban ml-1"></span>
									</span>
								{/if}
							</td>
							<td class="d-flex align-items-center justify-content-center p-2">
								<span class="btn-group">
									{if $LIBRARY['status'] === 0}
										<form method="POST" action="index.php?module=ModuleManager&parent=Settings&action=Library&mode=download&name={$NAME}">
											<button type="submit" class="btn btn-primary btn-sm">
												<span class="fas fa-download mr-1"></span>
												<strong>{\App\Language::translate('BTN_LIBRARY_DOWNLOAD', $QUALIFIED_MODULE)}</strong>
											</button>
										</form>
									{else}
										<form method="POST"
											  action="index.php?module=ModuleManager&parent=Settings&action=Library&mode=update&name={$NAME}">
										<button type="submit" class="btn btn-primary btn-sm">
											<span class="fas fa-redo-alt mr-1"></span>
											<strong>{\App\Language::translate('BTN_LIBRARY_UPDATE', $QUALIFIED_MODULE)}</strong>
										</button>
									</form>
									{/if}
								</span>
							</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
			<br/>
			<table class="table table-bordered table-sm">
				<tr>
					{foreach item=MODULE_MODEL key=MODULE_ID from=$ALL_MODULES}
					{assign var=MODULE_NAME value=$MODULE_MODEL->get('name')}
					{assign var=MODULE_ACTIVE value=$MODULE_MODEL->isActive()}
				</tr>
				<tr class=" col-sm-12 col-lg-6 col-xl-4 float-left p-0">
					<td class="d-flex justify-content-center">
						<div class="form-row px-3 w-100 align-items-center justify-content-center">
							<div class="col-1 col-md-1 text-center float-left p-1">
								<input type="checkbox" value="" name="moduleStatus" data-module="{$MODULE_NAME}" data-module-translation="{\App\Language::translate($MODULE_NAME, $MODULE_NAME)}" {if $MODULE_MODEL->isActive()}checked{/if} />
							</div>
							<div class="col-2 col-sm-2 col-md-2 text-center text-md-left p-1 {if !$MODULE_ACTIVE}dull {/if}">
								<span class="fa-2x userIcon-{$MODULE_NAME}"></span>
							</div>
							<div class="col-9 col-sm-6 col-md-4 text-center text-md-left p-1 {if !$MODULE_ACTIVE}dull {/if}">
								<h5 class="m-0 u-text-ellipsis text-left">{\App\Language::translate($MODULE_NAME, $MODULE_NAME)}</h5>
							</div>
							<div class="col-12 col-sm-2 col-md-5 p-1 form-row align-items-md-center justify-content-end">
								{if $MODULE_MODEL->isExportable()}
									<form class="c-btn-block-sm-down" method="POST" action="index.php?module=ModuleManager&parent=Settings&action=ModuleExport&mode=exportModule&forModule={$MODULE_NAME}">
										<button type="submit" class="btn btn-primary btn-sm float-right ml-0 ml-md-2 c-btn-block-sm-down mb-1 mb-md-0"><i class="far fa-arrow-alt-circle-down"></i></button>
									</form>
								{/if}
								{if $MODULE_MODEL->get('customized')}
									<button type="button" class="deleteModule btn btn-danger btn-sm float-right ml-0 ml-md-2 c-btn-block-sm-down mb-1 mb-md-0" name="{$MODULE_NAME}"><span class="fas fa-trash-alt"></span> </button>
								{/if}
								{assign var=SETTINGS_LINKS value=$MODULE_MODEL->getSettingLinks()}
								{if !in_array($MODULE_NAME, $RESTRICTED_MODULES_LIST) && (count($SETTINGS_LINKS) > 0)}
									<div class="btn-group-sm d-flex justify-content-end ml-0 ml-md-2 c-btn-block-sm-down u-remove-dropdown-icon {if !$MODULE_ACTIVE}d-none{/if}" role="group">
										<button class="btn dropdown-toggle btn-outline-secondary c-btn-block-sm-down" data-toggle="dropdown">
											<strong><span class="fas fa-cog"></span></strong>
										</button>
										<div class="dropdown-menu float-right">
											{foreach item=SETTINGS_LINK from=$SETTINGS_LINKS}
												<a class="dropdown-item" href="{$SETTINGS_LINK['linkurl']}"><span class="  {$SETTINGS_LINK['linkicon']} mr-2"></span>{\App\Language::translate($SETTINGS_LINK['linklabel'], $MODULE_NAME)}
												</a>
											{/foreach}
										</div>
									</div>
								{/if}
							</div>
						</td>
					{/foreach}
				</tr>
			</table>
		</div>
	</div>
{/strip}
