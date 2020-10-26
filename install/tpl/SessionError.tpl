{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-install-tpl-SessionError -->
<div class="container px-2 px-sm-3">
	<main class="main-container">
		<div class="inner-container">
			<form name="step{$STEP_NUMBER}" method="post" action="Install.php">
				<div class="row">
					<div class="col-12 text-center">
						<h2>{\App\Language::translate('LBL_ERROR_INSTALL', 'Install')}</h2>
					</div>
				</div>
				<hr>
				<div class="row">
					<div class="col-12">
						<h5>{\App\Language::translate('LBL_SESSION_ERROR_TITLE', 'Install')}</h5>
						<p>{\App\Language::translate('LBL_SESSION_ERROR_DESC','Install')}</p>
					</div>
				</div>
			</form>
		</div>
	</main>
</div>
{/strip}
