{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-Modals-HelpInfoFooter -->
	<div class="tpl-Modals-Footer modal-footer">
		{if !empty($BTN_SUCCESS)}
			<button class="btn btn-success" type="submit" name="saveButton" data-js="click">
				<span class="fas fa-check mr-1"></span><strong>{\App\Language::translate($BTN_SUCCESS, $MODULE)}</strong>
			</button>
		{/if}
		<button class="btn btn-info" type="submit" name="saveCloseButton" data-js="click">
			<span class="fas fa-check mr-1"></span><strong>{\App\Language::translate('LBL_SAVE_AND_CLOSE', $MODULE)}</strong>
		</button>
		{if !empty($BTN_DANGER) && empty($LOCK_EXIT)}
			<button class="btn btn-danger" type="reset" data-dismiss="modal">
				<span class="fas fa-times mr-1"></span><strong>{\App\Language::translate($BTN_DANGER, $MODULE)}</strong>
			</button>
		{/if}
	</div>
	</form>
	<!-- /tpl-Settings-LayoutEditor-Modals-HelpInfoFooter -->
{/strip}
