{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="tpl-Settings-Updates-Index">
	<div class="o-breadcrumb widget_header row">
		<div class="col-md-7">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
		{if \App\YetiForce\Register::isRegistered() && \App\Config::main('systemMode') !== 'demo' && \App\Security\AdminAccess::isPermitted('ModuleManager')}
			<div class="col-md-5 align-items-center d-flex justify-content-end">
				<a class="btn btn-success btn-sm addMenu" role="button" href="{Settings_ModuleManager_Module_Model::getUserModuleImportUrl()}">
					<span class="fa fa-plus u-mr-5px" title="{\App\Language::translate('LBL_IMPORT_UPDATE', $QUALIFIED_MODULE)}"></span>
					<span class="sr-only">{\App\Language::translate('LBL_IMPORT_UPDATE', $QUALIFIED_MODULE)}</span>
					<strong>{\App\Language::translate('LBL_IMPORT_UPDATE', $QUALIFIED_MODULE)}</strong>
				</a>
			</div>
		{/if}
	</div>
	{if !\App\YetiForce\Register::isRegistered()}
		<div class="col-md-12">
			<div class="alert alert-danger">
				<span class="yfi yfi-yeti-register-alert color-red-600 u-fs-5x mr-4 float-left"></span>
				<h1 class="alert-heading">{\App\Language::translate('LBL_YETIFORCE_NOT_REGISTRATION_TITLE',$QUALIFIED_MODULE)}</h1>
				{\App\Language::translate('LBL_YETIFORCE_NOT_REGISTRATION_DESC',$QUALIFIED_MODULE)}
			</div>
		</div>
	{/if}
	<hr class="mt-1 mb-2">
	{if $TO_INSTALL}
		<table class="table tableRWD table-bordered table-sm themeTableColor">
			<thead>
				<tr>
					<th colspan="5" class="text-center">{\App\Language::translate('LBL_AVAILABLE_UPGRADE_PACKAGES', $QUALIFIED_MODULE)}</th>
				</tr>
				<tr>
					<th>{\App\Language::translate('LBL_NAME_PACKAGES', $QUALIFIED_MODULE)}</th>
					<th>{\App\Language::translate('LBL_FROM_VERSION', $QUALIFIED_MODULE)}</th>
					<th>{\App\Language::translate('LBL_TO_VERSION', $QUALIFIED_MODULE)}</th>
					<th>{\App\Language::translate('LBL_PACKAGE_VERSION', $QUALIFIED_MODULE)}</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$TO_INSTALL item=ITEM}
					<tr>
						<td>{\App\Purifier::encodeHtml($ITEM['label'])}</td>
						<td>{\App\Purifier::encodeHtml($ITEM['fromVersion'])}</td>
						<td>{\App\Purifier::encodeHtml($ITEM['toVersion'])}</td>
						<td>{\App\Purifier::encodeHtml($ITEM['version'])}</td>
						<td class="text-center">
							{if \App\Config::main('systemMode') !== 'demo' && \App\Security\AdminAccess::isPermitted('ModuleManager')}
								{if \App\YetiForce\Updater::isDownloaded($ITEM)}
									<a class="btn btn-success btn-sm addMenu" role="button" href="index.php?module=ModuleManager&parent=Settings&view=ModuleImport&mode=importUserModuleStep2&upgradePackage={\App\Purifier::encodeHtml($ITEM['hash'])}">
										<span class="fas fa-download u-mr-5px" title="{\App\Language::translate('LBL_INSTALL_PACKAGE', $QUALIFIED_MODULE)}"></span>
										<span class="sr-only">{\App\Language::translate('LBL_INSTALL_PACKAGE', $QUALIFIED_MODULE)}</span>
										<strong>{\App\Language::translate('LBL_INSTALL_PACKAGE', $QUALIFIED_MODULE)}</strong>
									</a>
								{else}
									<a class="btn btn-primary btn-sm addMenu" role="button" href="index.php?parent=Settings&module=Updates&view=Index&download={\App\Purifier::encodeHtml($ITEM['hash'])}">
										<span class="fas fa-download u-mr-5px" title="{\App\Language::translate('LBL_DOWNLOAD_PACKAGE', $QUALIFIED_MODULE)}"></span>
										<span class="sr-only">{\App\Language::translate('LBL_DOWNLOAD_PACKAGE', $QUALIFIED_MODULE)}</span>
										<strong>{\App\Language::translate('LBL_DOWNLOAD_PACKAGE', $QUALIFIED_MODULE)}</strong>
									</a>
								{/if}
							{else}
								<a href="{$ITEM['url']}" class="btn btn-primary btn-sm" target="_blank" rel="noreferrer noopener">{\App\Language::translate('LBL_DOWNLOAD_PACKAGE', $QUALIFIED_MODULE)}</a>
							{/if}
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
		<hr class="mt-1 mb-2">
	{/if}
	<table class="table tableRWD table-bordered table-sm themeTableColor">
		<thead>
			<tr>
				<th colspan="6" class="text-center">{\App\Language::translate('LBL_INSTALLED_PACKAGES', $QUALIFIED_MODULE)}</th>
			</tr>
			<tr class="blockHeader">
				<th colspan="1" class="mediumWidthType">
					<span>{\App\Language::translate('LBL_TIME', $QUALIFIED_MODULE)}</span>
				</th>
				<th colspan="1" class="mediumWidthType">
					<span>{\App\Language::translate('LBL_USER', $QUALIFIED_MODULE)}</span>
				</th>
				<th colspan="1" class="mediumWidthType">
					<span>{\App\Language::translate('LBL_NAME_PACKAGES', $QUALIFIED_MODULE)}</span>
				</th>
				<th colspan="1" class="mediumWidthType">
					<span>{\App\Language::translate('LBL_FROM_VERSION', $QUALIFIED_MODULE)}</span>
				</th>
				<th colspan="1" class="mediumWidthType">
					<span>{\App\Language::translate('LBL_TO_VERSION', $QUALIFIED_MODULE)}</span>
				</th>
				<th colspan="1" class="mediumWidthType">
					<span>{\App\Language::translate('LBL_RESULT', $QUALIFIED_MODULE)}</span>
				</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$INSTALLED key=key item=foo}
				<tr>
					<td width="16%">
						<label class="marginRight5px">{$foo.time}</label>
					</td>
					<td width="16%">
						<label class="marginRight5px">{\App\Purifier::encodeHtml($foo.user)}</label>
					</td>
					<td width="16%">
						<label class="marginRight5px">{\App\Purifier::encodeHtml($foo.name)}</label>
					</td>
					<td width="16%">
						<label class="marginRight5px">{\App\Purifier::encodeHtml($foo.from_version)}</label>
					</td>
					<td width="16%">
						<label class="marginRight5px">{\App\Purifier::encodeHtml($foo.to_version)}</label>
					</td>
					<td width="16%">
						<label class="marginRight5px">
							{if $foo.result eq 1}
								{\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}
							{else}
								{\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}
							{/if}
						</label>
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>
