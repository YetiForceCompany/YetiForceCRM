{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Password-Encryption -->
	<div class="verticalScroll">
		<div class="o-breadcrumb widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div>
			<ul id="tabs" class="nav nav-tabs my-2 mr-0 js-nav-container" data-tabs="tabs">
				<li class="nav-item">
					<a class="nav-link active" href="#settingsEncryption" data-toggle="tab" data-url="index.php?module=Password&parent=Settings&view=Encryption&mode=settingsEncryption">
						<span class="mdi mdi-lock-question mr-2 u-fs-lg"></span>{\App\Language::translate('LBL_SETTINGS_ENCRYPTION', $QUALIFIED_MODULE)}
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link " href="#moduleEncryption" data-toggle="tab" data-url="index.php?module=Password&parent=Settings&view=Encryption&mode=moduleEncryption">
						<span class="mdi mdi mdi-lock-plus mr-2 u-fs-lg"></span>{\App\Language::translate('LBL_MODULES_ENCRYPTION', $QUALIFIED_MODULE)}
					</a>
				</li>
			</ul>
		</div>
		<div class="tab-content">
			<div class="tab-pane fade show active js-tab-container">
				{include file=\App\Layout::getTemplatePath('EncryptionSettingsTab.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-Password-Encryption -->
{/strip}
