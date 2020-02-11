{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-EditViewActions c-form__action-panel">
		{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
		<button class="btn btn-success js-form-submit-btn mr-1" type="submit" disabled="disabled" data-js="disabled" {if Vtiger_Field_Model::$tabIndexLastSeq}tabindex="{Vtiger_Field_Model::$tabIndexLastSeq}"{/if}>
			<span class="fas fa-check mr-1"></span>
			<strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong>
		</button>
		<button class="btn btn-danger mr-1" type="reset" onclick="javascript:window.history.back();" {if Vtiger_Field_Model::$tabIndexLastSeq}tabindex="{Vtiger_Field_Model::$tabIndexLastSeq}"{/if}>
			<span class="fas fa-times mr-1"></span>
			<strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong>
		</button>
		{foreach item=LINK from=$EDITVIEW_LINKS['EDIT_VIEW_HEADER']}
			{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='editViewHeader' TABINDEX=Vtiger_Field_Model::$tabIndexLastSeq BTN_CLASS="ml-1"}
		{/foreach}
	</div>
</form>
</div>
</div>
{/strip}
