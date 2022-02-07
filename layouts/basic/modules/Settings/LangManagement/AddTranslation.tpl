{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="modal fade AddNewTranslationMondal" tabindex="-1" role="dialog" aria-labelledby="AddTranslation" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 id="AddTranslation" class="modal-title">{\App\Language::translate('LBL_ADD_Translate',$QUALIFIED_MODULE)}</h5>
				<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form class="form-horizontal AddTranslationForm">
					<input type="hidden" name="langs" value="" />
					<div class="form-group">
						<label for="translation_type" class="col-sm-4 col-form-label">{\App\Language::translate('LBL_TranslationType', $QUALIFIED_MODULE)}:</label>
						<div class="col-sm-8">
							<select name="type" class="form-control" id="translation_type">
								<option value="php">{\App\Language::translate('LBL_LangPHP', $QUALIFIED_MODULE)}</option>
								<option value="js">{\App\Language::translate('LBL_LangJS', $QUALIFIED_MODULE)}</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="variable" class="col-sm-4 col-form-label">{\App\Language::translate('LBL_variable', $QUALIFIED_MODULE)}:</label>
						<div class="col-sm-8">
							<input id="variable" name="variable" class="form-control" type="text" placeholder="{\App\Language::translate('LBL_variable', $QUALIFIED_MODULE)}" />
						</div>
					</div>
					<div class="add_translation_block">
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary">{\App\Language::translate('LBL_AddLanguage', $QUALIFIED_MODULE)}</button>
				<button class="btn btn-warning" data-dismiss="modal" aria-hidden="true" type="button">{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}</button>
			</div>
		</div>
	</div>
</div>
