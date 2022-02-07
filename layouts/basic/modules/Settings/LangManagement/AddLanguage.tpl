{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="modal fade AddNewLangMondal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 id="myModalLabel" class="modal-title">{\App\Language::translate('LBL_ADD_LANG',$QUALIFIED_MODULE)}</h5>
				<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body form-horizontal">
				<div class="form-group">
					<label class="col-form-label col-md-3">
						{\App\Language::translate('LBL_Lang_label', $QUALIFIED_MODULE)}:
					</label>
					<div class="col-md-7"><input name="label" class="form-control" type="text" /></div>
				</div>
				<div class="form-group">
					<label class="col-form-label col-md-3">
						{\App\Language::translate('LBL_Lang_name', $QUALIFIED_MODULE)}:
					</label>
					<div class="col-md-7"><input name="name" class="form-control" type="text" /></div>
				</div>
				<div class="form-group">
					<label class="col-form-label col-md-3">{\App\Language::translate('LBL_Lang_prefix', $QUALIFIED_MODULE)}
						<span class="js-popover-tooltip" data-js="popover" data-placement="top"
							data-content="{\App\Purifier::encodeHtml(\App\Language::translate('LBL_IETF_LANGUAGE_TAG', $QUALIFIED_MODULE))}">
							<span class="fas fa-info-circle"></span>
						</span>:
					</label>
					<div class="col-md-7"><input name="prefix" class="form-control" type="text" /></div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary">{\App\Language::translate('LBL_AddLanguage', $QUALIFIED_MODULE)}</button>
				<button class="btn btn-warning" data-dismiss="modal" type="button" aria-hidden="true">{\App\Language::translate('LBL_Cancel', $QUALIFIED_MODULE)}</button>
			</div>
		</div>
	</div>
</div>
