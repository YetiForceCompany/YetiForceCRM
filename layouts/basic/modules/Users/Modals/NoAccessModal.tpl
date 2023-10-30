{*<!-- {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Users-Modals-NoAccessModal -->
	<div class="modal-body">
		<div class="row">
			<div class="col-md-4 text-center py-4">
				<img src="{App\Layout::getPublicUrl('layouts/resources/Logo/yetiforce_capterra.png')}"
					alt="Yetiforce Logo" class="w-100">
			</div>
			<div class="col-md-8">
				<p>
					{App\Language::translate('LBL_NO_ACCESS_DESCRIPTION', $MODULE_NAME)}
				</p>
				<div class="modal-footer">
					<a class="btn btn-danger js-post-action" role="button" href="index.php?module=Users&parent=Settings&action=Logout">
						<span class="fas fa-power-off mr-2"></span><strong>{\App\Language::translate('LBL_SIGN_OUT', $MODULE_NAME)}</strong>
					</a>
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Users-Modals-NoAccessModal -->
{/strip}
