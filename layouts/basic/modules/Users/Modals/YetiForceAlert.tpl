{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Users-Modals-YetiForceAlert -->
	<div class="modal-body pb-3">
		{if $MODE === 'registration'}
			<div class="alert alert-danger">
				<span class="yfi yfi-yeti-register-alert color-red-600 u-fs-5x mr-4 float-left"></span>
				<h3 class="alert-heading">{\App\Language::translate('LBL_SYSTEM_NOT_REGISTERED','Settings::Companies')}</h3>
			</div>
			{if \App\Security\AdminAccess::isPermitted('Companies')}
				<a href="index.php?module=Companies&parent=Settings&view=List&displayModal=online" target="_blank" class="btn btn-success mr-1 float-right">
					<span class="adminIcon-company-detlis mr-2"></span>
					{App\Language::translate('LBL_COMPANY_DATA','Settings:SystemWarnings')}
				</a>
			{/if}
		{else}
			<div class="alert alert-danger">
				<span class="yfi yfi-shop-alert text-warning u-fs-5x mr-4 float-left"></span>
				<h3 class="alert-heading">{\App\Language::translateArgs('LBL_PAID_FN_NO_SUBSCRIPTION','Settings::Companies',$PRODUCTS)}</h3>
			</div>
			{if \App\Security\AdminAccess::isPermitted('YetiForce')}
				<a href="index.php?module=YetiForce&parent=Settings&view=Shop" target="_blank" class="btn btn-success mr-1 float-right">
					<span class="yfi yfi-shop mr-2"></span>
					{App\Language::translate('LBL_YETIFORCE_SHOP')}
				</a>
			{/if}
		{/if}
		<div class="clearfix"></div>
		<div class="mt-3">
			<div class="progress">
				<div class="progress-bar progress-bar-striped progress-bar-animated js-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 100%;" data-js="container">
					<span class="sr-only"></span>
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Users-Modals-YetiForceAlert -->
{/strip}
