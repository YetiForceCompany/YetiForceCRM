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
		<div class="widget_header row">
			<div class="col-md-7">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
				{if isset($SELECTED_PAGE)}
					{\App\Language::translate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
				{/if}
			</div>
			<div class="col-md-5">
				<span class="btn-toolbar pull-right margin0px">
					<span class="btn-group">
						<button class="btn btn-success createModule" type="button">
							<span class="glyphicon glyphicon-blackboard" aria-hidden="true"></span>&nbsp;&nbsp;
							<strong>{\App\Language::translate('LBL_CREATE_MODULE', $QUALIFIED_MODULE)}</strong>
						</button>
					</span>
					{if \AppConfig::main('systemMode') !== 'demo'}
						<span class="btn-group">
							<button class="btn btn-primary" type="button" onclick='window.location.href = "{$IMPORT_USER_MODULE_URL}"'>
								<span class="glyphicon glyphicon-import" aria-hidden="true"></span>&nbsp;&nbsp;
								<strong>{\App\Language::translate('LBL_IMPORT_ZIP', $QUALIFIED_MODULE)}</strong>
							</button>
						</span>
					{/if}
				</span>
			</div>
		</div>
		<div class="contents">
			<table class="table tableRWD table-bordered table-condensed themeTableColor confTable footable-loaded footable">
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
							<td>{$LIBRARY['dir']}</td>
							<td><a href="{$LIBRARY['url']}">{$LIBRARY['url']}</a></td>
							<td>
								{if $LIBRARY['status'] == 1}
									<span class="label label-success bigLabel">
										{\App\Language::translate('LBL_LIBRARY_DOWNLOADED', $QUALIFIED_MODULE)}&nbsp;&nbsp;
										<span class="glyphicon glyphicon-ok-circle" aria-hidden="true"></span>
									</span>
								{elseif $LIBRARY['status'] == 2}
									<span class="label label-warning bigLabel">
										{\App\Language::translate('LBL_LIBRARY_NEEDS_UPDATING', $QUALIFIED_MODULE)}&nbsp;&nbsp;
										<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
									</span>
								{else}
									<span class="label label-danger bigLabel">
										{\App\Language::translate('LBL_LIBRARY_NO_DOWNLOAD', $QUALIFIED_MODULE)}&nbsp;&nbsp;
										<span class="glyphicon glyphicon-ban-circle" aria-hidden="true"></span>
									</span>
								{/if}
							</td>
							<td class="text-center">
								<span class="btn-group">
									{if $LIBRARY['status'] === 0}
										<a class="btn btn-primary btn-sm" href="index.php?module=ModuleManager&parent=Settings&action=Library&mode=download&name={$NAME}">
											<span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>&nbsp;&nbsp;
											<strong>{\App\Language::translate('BTN_LIBRARY_DOWNLOAD', $QUALIFIED_MODULE)}</strong>
										</a>
									{else}
										<a class="btn btn-primary btn-sm" href="index.php?module=ModuleManager&parent=Settings&action=Library&mode=update&name={$NAME}">
											<span class="glyphicon glyphicon-repeat" aria-hidden="true"></span>&nbsp;&nbsp;
											<strong>{\App\Language::translate('BTN_LIBRARY_UPDATE', $QUALIFIED_MODULE)}</strong>
										</a>
									{/if}
								</span>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
			<br />
			{assign var=COUNTER value=0}
			<table class="table table-bordered">
				<tr>
					{foreach item=MODULE_MODEL key=MODULE_ID from=$ALL_MODULES}
						{assign var=MODULE_NAME value=$MODULE_MODEL->get('name')}
						{assign var=MODULE_ACTIVE value=$MODULE_MODEL->isActive()}
						{if $COUNTER eq 2}
						</tr><tr>
							{assign var=COUNTER value=0}
						{/if}
						<td class="opacity col-md-6">
							<div class="moduleManagerBlock">
								<div class="col-md-1 col-xs-2">
									<input type="checkbox" value="" name="moduleStatus" data-module="{$MODULE_NAME}" data-module-translation="{\App\Language::translate($MODULE_NAME, $MODULE_NAME)}" {if $MODULE_MODEL->isActive()}checked{/if} />
								</div>
								<div class="col-md-1 col-xs-2 moduleImage {if !$MODULE_ACTIVE}dull {/if}">
									<span class="fa-2x userIcon-{$MODULE_NAME}"></span>
								</div>
								<div class="col-xs-8 col-md-4 moduleName {if !$MODULE_ACTIVE}dull {/if}">
									<h4 class="no-margin">{\App\Language::translate($MODULE_NAME, $MODULE_NAME)}</h4>
								</div>
								<div class="col-md-6 col-xs-12">
									{if $MODULE_MODEL->get('customized')}
										<button class="deleteModule btn btn-danger btn-xs pull-right marginLeft10" name="{$MODULE_NAME}">{\App\Language::translate('LBL_DELETE')}</button>
									{/if}
									{if $MODULE_MODEL->isExportable()}
										<a class="btn btn-primary btn-xs pull-right marginLeft10" href="index.php?module=ModuleManager&parent=Settings&action=ModuleExport&mode=exportModule&forModule={$MODULE_NAME}"><i class="glyphicon glyphicon-download"></i></a>
									{/if}
									{assign var=SETTINGS_LINKS value=$MODULE_MODEL->getSettingLinks()}
									{if !in_array($MODULE_NAME, $RESTRICTED_MODULES_LIST) && (count($SETTINGS_LINKS) > 0)}
										<div class="btn-group pull-right actions {if !$MODULE_ACTIVE}hide{/if}">
											<button class="btn dropdown-toggle btn-default" data-toggle="dropdown">
												<strong>{\App\Language::translate('LBL_SETTINGS', $QUALIFIED_MODULE)}</strong>&nbsp;<i class="caret"></i>
											</button>
											<ul class="dropdown-menu pull-right">
												{foreach item=SETTINGS_LINK from=$SETTINGS_LINKS}
													<li>
														<a {if stripos($SETTINGS_LINK['linkurl'], 'javascript:')===0} onclick='{$SETTINGS_LINK['linkurl']|substr:strlen("javascript:")};'{else} onclick='window.location.href = "{$SETTINGS_LINK['linkurl']}"'{/if}>{\App\Language::translate($SETTINGS_LINK['linklabel'], $MODULE_NAME)}</a>
													</li>
												{/foreach}
											</ul>
										</div>
									{/if}
								</div>
								{assign var=COUNTER value=$COUNTER+1}
						</td>
					{/foreach}
				</tr>
			</table>
		</div>
	</div>
{/strip}
