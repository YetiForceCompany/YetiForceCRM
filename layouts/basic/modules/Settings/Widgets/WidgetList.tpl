{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class='modelContainer modal fade' tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<form class="form-modalAddWidget">
				<div class="modal-header">
					<h5 class="modal-title">
						<span class="fas fa-plus mr-1"></span>
						{\App\Language::translate('Add widget', $QUALIFIED_MODULE)}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="modal-Fields">
						<div class="row align-items-center">
							<div class="col-md-4">{\App\Language::translate('LBL_WIDGET_TYPE', $QUALIFIED_MODULE)}:</div>
							<div class="col-md-8">
								<select name="type" class="select2 col-md-3 marginLeftZero form-control">
									{foreach from=$MODULE_MODEL->getType($SOUNRCE_MODULE) item=item key=key}
										<option value="{$key}">{\App\Language::translate($item, $QUALIFIED_MODULE)}</option>
									{/foreach}
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success" type="submit" name="saveButton">
						<strong>
							<span class="fas fa-check mr-1"></span>
							{\App\Language::translate('LBL_SELECT', $QUALIFIED_MODULE)}
						</strong>
					</button>
					<button class="btn btn-danger" type="reset" data-dismiss="modal">
						<strong>
							<span class="fas fa-times mr-1"></span>
							{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
						</strong>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
