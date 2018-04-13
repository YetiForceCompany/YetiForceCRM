{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Vtiger-LibraryLicense modal-header">
		<span class="fab fa-wpforms mt-3 mr-2" data-fa-transform="grow-10"></span>
		<h3 class="modal-title">{\App\Language::translate('LBL_LICENSE', $QUALIFIED_MODULE)}</h3>
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
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
{/strip}
