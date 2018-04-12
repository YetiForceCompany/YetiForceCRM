{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-footer">
		{if !$HIDE_SAVE_BTN}
			<button class="btn btn-success" type="submit" name="saveButton">
				<span class="fas fa-check mr-1"></span><strong>{App\Language::translate('LBL_SAVE', $MODULE)}</strong>
			</button>
		{/if}
		{if !$HIDE_CANCEL_BTN}
			<button class="btn btn-danger" type="reset" data-dismiss="modal">
				<span class="fas fa-times mr-1"></span><strong>{App\Language::translate('LBL_CANCEL', $MODULE)}</strong>
			</button>
		{/if}
	</div>
	{if $SHOW_END_TAGS}
		</div>
		</div>
		</div>
	{/if}
{/strip}
