{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}

{strip}
		<div class="contentHeader">
			<div class="pull-right">
				<button class="btn btn-success generatePass" name="save" type="button">
					<strong>{\App\Language::translate($GENERATEPASS, $MODULE)}</strong>
				</button>&nbsp;
				<button class="btn btn-success" type="submit"><strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong></button>&nbsp;
				<button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{\App\Language::translate('LBL_CANCEL', $MODULE)}</button>
			</div>
			<div class="clearfix"></div>
		</div>
	</form>
</div>
{/strip}
