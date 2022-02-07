{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-MailIntegration-LoginInframe -->
	{assign var=CHECK_ALERT value=\App\YetiForce\Shop::checkAlert('YetiForceOutlook')}
	{if $CHECK_ALERT}
		<div class="alert alert-warning">
			<span class="yfi-premium mr-2 u-fs-2em color-red-600 float-left"></span>
			{\App\Language::translate($CHECK_ALERT, 'Settings::YetiForce')}
		</div>
	{/if}
	<iframe id="js-iframe" width="100%" height="100%" frameborder="0" class="w-100 h-100 position-absolute" data-view="login" allowfullscreen="true" allow="geolocation;microphone;camera" src-a="index.php" data-js="iframe"></iframe>
	<!-- /tpl-MailIntegration-LoginInframe -->
{/strip}
