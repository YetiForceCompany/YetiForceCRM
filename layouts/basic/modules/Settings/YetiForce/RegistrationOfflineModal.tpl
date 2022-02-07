{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-YetiForce-RegistrationOfflineModal modal-body">
		<div class="alert alert-success" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			<p>{\App\Language::translateArgs('LBL_REGISTRATION_OFFLINE_DESC',$QUALIFIED_MODULE,'registration@yetiforce.com')}</p>
		</div>
		<textarea rows="14" readonly="readonly">{$EXAMPLE}</textarea>
		<form>
			<div class="form-group form-row">
				<label class="col-form-label u-text-small-bold text-right col-lg-5">
					{\App\Language::translate('LBL_REGISTRATION_KEY', $QUALIFIED_MODULE)}
					<span class="redColor"> *</span>:
				</label>
				<div class="fieldValue col-lg-7">
					<input type="text" class="form-control registrationKey" data-validation-engine="validate[required,custom[onlyLetterNumber],minSize[40],maxSize[40]]">
				</div>
			</div>
		</form>
	</div>
{/strip}
