{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-AppComponents-InterestsConflict-->
	<div class="bodyContents">
		<div class="contentsDiv">
			<div class="o-breadcrumb widget_header row mb-2">
				<div class="col-md-12">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
				</div>
			</div>
			<div>
				{if $MODE === 'confirm'}
					{include file=\App\Layout::getTemplatePath('InterestsConflictConfirmations.tpl', 'AppComponents')}
				{/if}
				{if $MODE === 'unlock'}
					{include file=\App\Layout::getTemplatePath('InterestsConflictUnlock.tpl', 'AppComponents')}
				{/if}
			</div>
		</div>
	</div>
	<!-- /tpl-AppComponents-InterestsConflict-->
{/strip}
