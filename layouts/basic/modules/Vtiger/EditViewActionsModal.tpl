{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-EditViewActionsModal -->
	<div class="w-100 d-flex justify-content-center">
		{var_dump($MODULE_NAME)}
		{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE_NAME}
		<button class="btn btn-success js-form-submit-btn mr-1" type="submit" disabled="disabled" data-js="disabled" {if Vtiger_Field_Model::$tabIndexLastSeq}tabindex="{Vtiger_Field_Model::$tabIndexLastSeq}" {/if}>
			<span class="fas fa-check mr-1"></span>
			<strong>{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}</strong>
		</button>
		<button class="btn btn-danger mr-2" type="button" data-dismiss="modal" {if Vtiger_Field_Model::$tabIndexLastSeq}tabindex="{Vtiger_Field_Model::$tabIndexLastSeq}" {/if}>
			<span class="fas fa-times mr-1"></span>
			<strong>{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</strong>
		</button>
		{if \App\Privilege::isPermitted($MODULE_NAME, 'RecordCollector') && !empty($EDITVIEW_LINKS['EDIT_VIEW_RECORD_COLLECTOR'])}
			{include file=\App\Layout::getTemplatePath('Edit/RecordCollectors.tpl', $MODULE_NAME) RECORD_COLLECTOR=$EDITVIEW_LINKS['EDIT_VIEW_RECORD_COLLECTOR']}
		{/if}
	</div>
	<!-- /tpl-Base-EditViewActionsModal -->
{/strip}
