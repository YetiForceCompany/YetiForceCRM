{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Wapro-ListView-CustomHeader -->
	{if !\App\YetiForce\Register::isRegistered()}
		<div class="col-md-12">
			<div class="alert alert-danger">
				<span class="yfi yfi-yeti-register-alert color-red-600 u-fs-5x mr-4 float-left"></span>
				<h1 class="alert-heading">{\App\Language::translate('LBL_YETIFORCE_NOT_REGISTRATION_TITLE',$QUALIFIED_MODULE)}</h1>
				{\App\Language::translate('LBL_YETIFORCE_NOT_REGISTRATION_DESC',$QUALIFIED_MODULE)}
			</div>
		</div>
	{else}
		{assign var=CHECK_ALERT value=\App\YetiForce\Shop::checkAlert('YetiForceWaproERP')}
		{if $CHECK_ALERT}
			<div class="alert alert-warning">
				<span class="yfi-premium mr-2 u-fs-2em color-red-600 float-left"></span>
				{\App\Language::translate($CHECK_ALERT, 'Settings::YetiForce')} <a class="btn btn-primary btn-sm" href="index.php?parent=Settings&module=YetiForce&view=Shop&product=YetiForceWaproERP&mode=showProductModal"><span class="yfi yfi-shop mr-2"></span>{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}</a>
			</div>
		{/if}
	{/if}
	{if !in_array('sqlsrv',PDO::getAvailableDrivers())}
		<div class="alert alert-danger">
			<h1 class="alert-heading"><span class="fas fa-exclamation-triangle mr-2"></span>{\App\Language::translateArgs('ERR_NO_REQUIRED_LIBRARY',$QUALIFIED_MODULE,'PDO_SQLSRV')}</h1>
			{\App\Language::translateArgs('ERR_NO_REQUIRED_LIBRARY_DESC',$QUALIFIED_MODULE,'PDO_SQLSRV (PDO Microsoft SQL Server Driver for PHP)')}
		</div>
	{/if}
	{if !Settings_Wapro_Activation_Model::check()}
		<div class="alert alert-danger">
			<form action='index.php' method="POST" enctype="multipart/form-data">
				<input type="hidden" name="module" value="{$MODULE_NAME}" />
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" name="action" value="Activation" />
				<span class="mdi mdi-alert-outline mr-2 u-fs-3x float-left"></span>
				{\App\Language::translateArgs('LBL_FUNCTIONALITY_HAS_NOT_YET_BEEN_ACTIVATED', $QUALIFIED_MODULE, 'Wapro ERP')}
				<button type="submit" class="btn btn-primary btn-sm ml-3">
					<span class="mdi mdi-check mr-2 float-left"></span>
					{\App\Language::translate('LBL_ACTIVATE_FUNCTIONALITY', $QUALIFIED_MODULE)}
				</button>
			</form>
		</div>
	{/if}
	<!-- /tpl-Settings-Wapro-ListView-CustomHeader -->
{/strip}
