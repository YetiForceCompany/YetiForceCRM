{*<!-- {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-MailIntegration-MessageCompose -->
	{assign var=CHECK_ALERT value=\App\YetiForce\Shop::checkAlert('YetiForceOutlook')}
	{if $CHECK_ALERT}
		<div class="alert alert-warning">
			<span class="yfi-premium mr-2 u-fs-2em color-red-600 float-left"></span>
			{\App\Language::translate($CHECK_ALERT, 'Settings::YetiForce')}
		</div>
	{else}
		<div class="d-flex flex-column p-2">
			<label for="searchMail">{\App\Language::translate('LBL_SEARCH_AND_COPY_EMAIL', $MODULE_NAME)}</label>
			<input id="searchMail" type="text" class="form-control js-search-input" name="searchInput" placeholder="{\App\Language::translate('LBL_SEARCH_EMAIL', $MODULE_NAME)}">
		</div>
	{/if}
	<!-- /tpl-MailIntegration-MessageCompose -->
{/strip}
