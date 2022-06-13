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
		{assign var=CHECK_ALERT value=\App\YetiForce\Shop::checkAlert('YetiForceMagento')}
		{if $CHECK_ALERT}
			<div class="alert alert-warning">
				<span class="yfi-premium mr-2 u-fs-2em color-red-600 float-left"></span>
				{\App\Language::translate($CHECK_ALERT, 'Settings::YetiForce')} <a class="btn btn-primary btn-sm" href="index.php?parent=Settings&module=YetiForce&view=Shop&product=YetiForceMagento&mode=showProductModal"><span class="yfi yfi-shop mr-2"></span>{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}</a>
			</div>
		{/if}
	{/if}
	{if !in_array('sqlsrv',PDO::getAvailableDrivers())}
		<div class="alert alert-danger">
			<h1 class="alert-heading"><span class="fas fa-exclamation-triangle mr-2"></span>{\App\Language::translate('LBL_NO_REQUIRED_LIBRARY',$QUALIFIED_MODULE)}</h1>
			{\App\Language::translate('LBL_NO_REQUIRED_LIBRARY_DESC',$QUALIFIED_MODULE)}
		</div>
	{/if}
	<!-- /tpl-Settings-Wapro-ListView-CustomHeader -->
{/strip}
