{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-BusinessHours-EditViewActions -->
	<div class="tpl-Settings-BusinessHours-EditViewActions c-form__action-panel">
		{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
		<button class="btn btn-success u-mr-5px" type="submit">
			<span class="fas fa-check u-mr-5px"></span>
			{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
		</button>
		<button class="btn btn-danger" type="reset" onclick="javascript:window.history.back();">
			<span class="fas fa-times u-mr-5px"></span>
			{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
		</button>
	</div>
	</form>
	</div>
	</div>
	<!-- /tpl-Settings-BusinessHours-EditViewActions -->
{/strip}
