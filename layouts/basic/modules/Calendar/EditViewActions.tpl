{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-EditViewActions c-form__action-panel d-flex justify-content-center">
		<div class="btn-group-toggle mr-1" data-toggle="buttons">
			<label class="btn c-btn-checkbox c-btn-outline-done js-btn--mark-as-completed" data-js="click">
				<strong>
					<span class="far fa-square fa-lg mr-1 c-btn-checkbox--unchecked"></span>
					<span class="far fa-check-square fa-lg mr-1 c-btn-checkbox--checked"></span>
					<input type="checkbox" checked
						autocomplete="off">{\App\Language::translate('LBL_MARK_AS_HELD', $MODULE)}</strong>
			</label>
		</div>
		<button class="btn btn-success mr-1 js-form-submit-btn" type="submit" disabled="disabled" data-js="disabled">
			<span class="fas fa-check mr-1"></span>
			<strong class="d-none d-sm-inline-block">{\App\Language::translate('LBL_SAVE', $MODULE)}</strong>
		</button>
		<button class="btn btn-danger mr-1" type="reset" onclick="javascript:window.history.back();">
			<span class="fas fa-times mr-1"></span><strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong>
		</button>
		{if isset($EDITVIEW_LINKS['EDIT_VIEW_HEADER'])}
			{foreach item=LINK from=$EDITVIEW_LINKS['EDIT_VIEW_HEADER']}
				{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='editViewHeader'}
			{/foreach}
		{/if}
	</div>
	</form>
	</div>
	</div>
{/strip}
