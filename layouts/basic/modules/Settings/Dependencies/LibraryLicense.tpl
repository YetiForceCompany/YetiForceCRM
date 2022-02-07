{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Dependencies-LibraryLicense -->
	<div class="modal-header">
		<h5 class="modal-title">
			<span class="fab fa-wpforms mr-1"></span>
			{\App\Language::translate('LBL_LICENSE', $QUALIFIED_MODULE)}
		</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CLOSE')}">
			<span aria-hidden="true" title="{\App\Language::translate('LBL_CLOSE')}">&times;</span>
		</button>
	</div>
	<div class="modal-body col-md-12">
		{if $FILE_EXIST}
			{nl2br($FILE_CONTENT)}
		{else}
			<div class="alert alert-danger" role="alert">
				{\App\Language::translate('LBL_MISSING_LICENSE_FILE', $QUALIFIED_MODULE)}
			</div>
		{/if}
	</div>
	<!-- /tpl-Settings-Dependencies-LibraryLicense -->
{/strip}
