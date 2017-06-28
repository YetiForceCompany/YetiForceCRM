{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	{if $ERRORTEXT neq ''}
		<div class="row main-container">
			<div class="inner-container">
				<div>
					<h3>{vtranslate('LBL_MIGRATION_ERROR', 'Install')}</h3>
				</div>
				<div>
					<h5>{vtranslate($ERRORTEXT, 'Install')}</h5>
				</div>
			</div>
			<div class="inner-container">
				<div>
					<a class="btn btn-sm btn-primary" href="../index.php" >{vtranslate('LBL_BACK','Install')}</a> 
				</div>
			</div>
		</div>
	{/if}
{/strip}
