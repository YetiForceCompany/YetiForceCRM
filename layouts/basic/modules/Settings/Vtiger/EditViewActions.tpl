{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-EditViewActions c-form__action-panel">
		{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
		<button class="btn btn-success js-form-submit-btn" type="submit">
			<span class="fas fa-check mr-1"></span>
			<strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong>
		</button>
		<button class="btn btn-danger ml-2" type="reset" onclick="javascript:window.history.back();">
			<span class="fas fa-times mr-1"></span>
			<strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong>
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
