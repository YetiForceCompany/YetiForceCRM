{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if $ERRORTEXT neq ''}
		<div class="main-container">
			<div class="inner-container">
				<div>
					<h3>{\Install\Language::translate('LBL_MIGRATION_ERROR', 'Install')}</h3>
				</div>
				<div>
					<h5>{\Install\Language::translate($ERRORTEXT, 'Install')}</h5>
				</div>
			</div>
			<div class="inner-container">
				<div>
					<a class="btn btn-md btn-primary" role="button"
					   href="../index.php">{\Install\Language::translate('LBL_BACK','Install')}</a>
				</div>
			</div>
		</div>
	{/if}
{/strip}
