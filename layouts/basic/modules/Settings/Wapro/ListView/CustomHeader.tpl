{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Wapro-ListView-CustomHeader -->
	{if !in_array('sqlsrv',PDO::getAvailableDrivers())}
		<div class="alert alert-danger">
			<h1 class="alert-heading"><span class="fas fa-exclamation-triangle mr-2"></span>{\App\Language::translate('LBL_NO_REQUIRED_LIBRARY',$QUALIFIED_MODULE)}</h1>
			{\App\Language::translate('LBL_NO_REQUIRED_LIBRARY_DESC',$QUALIFIED_MODULE)}
		</div>
	{/if}
	<!-- /tpl-Settings-Wapro-ListView-CustomHeader -->
{/strip}
