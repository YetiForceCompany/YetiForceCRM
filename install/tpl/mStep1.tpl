{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="main-container">
		<div class="inner-container">
			<h2>{\App\Language::translate('LBL_MIGRATION_HEADER', 'Install')}</h2>
			<form class="" name="step1" method="post" action="Install.php">
				<input type="hidden" name="mode" value="mStep2">
				<input type="hidden" name="lang" value="{$LANG}">
				<div class="row">
					<div>
						<div class="col-md-10 inner-container">
							<p>{\App\Language::translate('LBL_DESCRIPTION_CONDITIONS', 'Install')}</p>
						</div>
						<div class="float-right col-md-2">
							<input type="checkbox" id="checkBox3" name="checkBox3"  required><div class="chkbox"></div> {\App\Language::translate('LBL_ACCEPT', 'Install')}</a>
						</div>
						<div class="clearfix"></div><hr><br>
					</div>
				</div>
				<div class="row">
					<div>
						<div class="button-container">
							<input id="agree" type="submit" class="btn btn-sm btn-primary" value="{\App\Language::translate('LBL_NEXT', 'Install')}">
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
{/strip}
