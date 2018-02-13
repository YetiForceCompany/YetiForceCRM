{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class='modelContainer modal fade' tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<form class="form-modalAddWidget">  
				<div class="modal-header contentsBackground">
					<button type="button" data-dismiss="modal" class="close" title="Zamknij">×</button>
					<h3 class="modal-title" id="massEditHeader">{\App\Language::translate('Add widget', $QUALIFIED_MODULE)}</h3>
				</div>
				<div class="modal-body">
					<div class="modal-Fields">
						<div class="row">
							<div class="col-md-4">{\App\Language::translate('LBL_WIDGET_TYPE', $QUALIFIED_MODULE)}:</div>
							<div class="col-md-8">
								<select name="type" class="select2 col-md-3 marginLeftZero form-control">
									{foreach from=$MODULE_MODEL->getType($SOUNRCE_MODULE) item=item key=key}
										<option value="{$key}" >{\App\Language::translate($item, $QUALIFIED_MODULE)}</option>
									{/foreach}
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success" type="submit" name="saveButton"><strong>{\App\Language::translate('LBL_SELECT', $QUALIFIED_MODULE)}</strong></button>
					<button class="btn btn-warning" type="reset" data-dismiss="modal"><strong>{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</strong></button>
				</div>
			</form>
		</div>
	</div>
</div>
