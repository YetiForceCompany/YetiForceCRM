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
	<!-- tpl-Settings-ModuleManager-ListContents -->
	<div id="moduleManagerContents">
		<div class="o-breadcrumb widget_header row mb-2">
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
					{if \App\Config::main('systemMode') !== 'demo'}
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
				<table class="table table-bordered table-with-flex table-sm position-relative">
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
			<br />
			<table class="table table-bordered table-with-flex table-sm">
				<tr>
					{foreach item=MODULE_MODEL key=MODULE_ID from=$ALL_MODULES}
						{assign var=ITEM_NAME value=$MODULE_MODEL->get('name')}
						{assign var=MODULE_ACTIVE value=$MODULE_MODEL->isActive()}
					</tr>
					<tr class="c-module-table-row col-sm-12 col-lg-6 col-xl-4 float-left p-0">
						<td class="d-flex w-100 align-items-center flex-nowrap">
							<div class="mx-md-2 u-h-fit">
								<input type="checkbox" value="" name="moduleStatus" aria-label="{\App\Language::translate($ITEM_NAME, $ITEM_NAME)}" data-module="{$ITEM_NAME}" data-module-translation="{\App\Language::translate($ITEM_NAME, $ITEM_NAME)}" {if $MODULE_MODEL->isActive()}checked{/if} />
							</div>
							<div class="text-center text-md-left p-1 {if !$MODULE_ACTIVE}dull {/if}">
								<span class="fa-2x yfm-{$ITEM_NAME}"></span>
							</div>
							<div class="text-center u-ellipsis-in-flex text-md-left p-1 {if !$MODULE_ACTIVE}dull {/if}">
								<h5 class="m-0 u-text-ellipsis--no-hover text-left u-font-weight-450" title="{\App\Language::translate($ITEM_NAME, $ITEM_NAME)}"></h5>{\App\Language::translate($ITEM_NAME, $ITEM_NAME)}</h5>
							</div>
							<div class="d-flex flex-row align-items-center ml-auto mr-md-1">
								{if !empty($ICONS[$MODULE_MODEL->get('premium')])}
									<span class="{$ICONS[$MODULE_MODEL->get('premium')]} js-popover-tooltip" data-content="{\App\Language::translate('LBL_PREMIUM_MODULE', $QUALIFIED_MODULE)}"></span>
								{/if}
								{if $MODULE_MODEL->isExportable()}
									<form class="" method="POST" action="index.php?module=ModuleManager&parent=Settings&action=ModuleExport&mode=exportModule&forModule={$ITEM_NAME}">
										<button type="submit" class="btn btn-primary btn-sm ml-0 ml-md-2 js-popover-tooltip" data-content="{\App\Language::translate('LBL_EXPORT_MODULE', $QUALIFIED_MODULE)}">
											<i class="far fa-arrow-alt-circle-down"></i>
										</button>
									</form>
								{/if}
								{if $MODULE_MODEL->get('customized')}
									<button type="button" aria-label="{\App\Language::translate('LBL_DELETE_MODULE', $QUALIFIED_MODULE)}" class="deleteModule btn btn-danger btn-sm ml-1 ml-md-2 js-popover-tooltip" name="{$ITEM_NAME}" data-content="{\App\Language::translate('LBL_DELETE_MODULE', $QUALIFIED_MODULE)}">
										<span class="fas fa-trash-alt"></span>
									</button>
								{/if}
								{assign var=SETTINGS_LINKS value=$MODULE_MODEL->getSettingLinks()}
								{if !in_array($ITEM_NAME, $RESTRICTED_MODULES_LIST) && (count($SETTINGS_LINKS) > 0)}
									<div class="btn-group-sm d-flex justify-content-end ml-1 ml-md-2 u-remove-dropdown-icon {if !$MODULE_ACTIVE}d-none{/if}" role="group">
										<button class="btn dropdown-toggle btn-outline-secondary js-popover-tooltip" aria-label="{\App\Language::translate('LBL_SETTINGS', $QUALIFIED_MODULE)}" data-toggle="dropdown" data-content="{\App\Language::translate('LBL_SETTINGS', $QUALIFIED_MODULE)}">
											<strong><span class="fas fa-cog"></span></strong>
										</button>
										<div class="dropdown-menu">
											{foreach item=SETTINGS_LINK from=$SETTINGS_LINKS}
												<a class="dropdown-item" href="{$SETTINGS_LINK['linkurl']}">
													<span class="{$SETTINGS_LINK['linkicon']} mr-2"></span>{\App\Language::translate($SETTINGS_LINK['linklabel'], $ITEM_NAME)}
												</a>
											{/foreach}
										</div>
									</div>
								{/if}
						</td>
					{/foreach}
				</tr>
			</table>
		</div>
	</div>
	<!-- /tpl-Settings-ModuleManager-ListContents -->
{/strip}
