{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<!-- tpl-Settings-SystemWarnings-YetiForce-Registration -->
{strip}
	<h6 class="h3">
		{App\Language::translate('LBL_REGISTRATION','Settings:SystemWarnings')}
	</h6>
	<p>{\App\Language::translateArgs('LBL_YETIFORCE_REGISTRATION_ERROR', $MODULE_NAME, "<a href=\"index.php?module=Companies&parent=Settings&view=List&displayModal=online\">{\App\Language::translate('LBL_YETIFORCE_REGISTRATION_CHECK_STATUS', $MODULE_NAME)}</a>")}</p>
	<form class="validateForm" method="post" action="index.php">
		<div class="float-right mr-2">
			<a href="index.php?module=Companies&parent=Settings&view=List&displayModal=online" target="_blank"
				class="btn btn-success mr-1">
				<span class="fas fa-check mr-1"></span>
				{App\Language::translate('LBL_COMPANY_DATA','Settings:SystemWarnings')}
			</a>
			<button type="button" class="btn btn-danger cancel">
				<span class="fas fa-ban mr-1"></span>
				{App\Language::translate('LBL_REMIND_LATER','Settings:SystemWarnings')}
			</button>
		</div>
	</form>
{/strip}
<!-- /tpl-Settings-SystemWarnings-YetiForce-Registration -->
