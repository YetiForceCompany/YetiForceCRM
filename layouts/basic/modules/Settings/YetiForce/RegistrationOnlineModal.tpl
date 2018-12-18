{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-AccountHierarchy modelContainer modal fade" id="accountHierarchyContainer" tabindex="-1"
		 role="dialog">
		<div class="modal-dialog c-modal-xxl" role="document">
			<form method="post" class="form-horizontal validateForm" id="editForm">
				<input type="hidden" name="module" value="YetiForce"/>
				<input type="hidden" name="parent" value="Settings"/>
				<input type="hidden" name="action" value="RegisterOnline"/>
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">{\App\Language::translate('LBL_REGISTRATION_ONLINE_MODAL', $QUALIFIED_MODULE)}</h5>
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					</div>
					<div class="modal-body col-md-12">

					</div>
					<div class="modal-footer">
						<button class="btn btn-success" type="submit" name="saveButton" data-dismiss="modal">
							<span class="fas fa-check mr-1"></span><strong>{\App\Language::translate('LBL_REGISTER', $MODULE)}</strong>
						</button>
						<button class="btn btn-danger" type="reset" data-dismiss="modal">
							<span class="fas fa-times mr-1"></span><strong>{\App\Language::translate('LBL_CLOSE', $MODULE)}</strong>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
{/strip}