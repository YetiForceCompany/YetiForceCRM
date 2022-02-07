{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-MailIntegration-MessageCompose -->
	{assign var=CHECK_ALERT value=\App\YetiForce\Shop::checkAlert('YetiForceOutlook')}
	{if $CHECK_ALERT}
		<div class="alert alert-warning">
			<span class="yfi-premium mr-2 u-fs-2em color-red-600 float-left"></span>
			{\App\Language::translate($CHECK_ALERT, 'Settings::YetiForce')}
		</div>
	{/if}
	<div class="d-flex flex-column p-2">
		<label for="searchMail">{\App\Language::translate('LBL_SEARCH_AND_COPY_EMAIL', $MODULE_NAME)}</label>
		<input id="searchMail" type="text" class="form-control js-search-input" name="searchInput" placeholder="{\App\Language::translate('LBL_SEARCH_EMAIL', $MODULE_NAME)}">
	</div>
	<!-- /tpl-MailIntegration-MessageCompose -->
{/strip}
