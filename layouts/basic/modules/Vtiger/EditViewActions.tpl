{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-EditViewActions c-form__action-panel">
		{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE_NAME}
		<button class="btn btn-success js-form-submit-btn mr-1" type="submit" disabled="disabled" data-js="disabled" {if Vtiger_Field_Model::$tabIndexLastSeq}tabindex="{Vtiger_Field_Model::$tabIndexLastSeq}"{/if}>
			<span class="fas fa-check mr-1"></span>
			<strong>{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}</strong>
		</button>
		<button class="btn btn-danger mr-1" type="reset" onclick="javascript:window.history.back();" {if Vtiger_Field_Model::$tabIndexLastSeq}tabindex="{Vtiger_Field_Model::$tabIndexLastSeq}"{/if}>
			<span class="fas fa-times mr-1"></span>
			<strong>{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</strong>
		</button>
		{if isset($EDITVIEW_LINKS['EDIT_VIEW_HEADER'])}
			{foreach item=LINK from=$EDITVIEW_LINKS['EDIT_VIEW_HEADER']}
				{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) BUTTON_VIEW='editViewHeader' TABINDEX=Vtiger_Field_Model::$tabIndexLastSeq BTN_CLASS="ml-1"}
			{/foreach}
		{/if}
		{if \App\Privilege::isPermitted($MODULE_NAME, 'RecordCollector') && !empty($EDITVIEW_LINKS['EDIT_VIEW_RECORD_COLLECTOR'])}
			{foreach item=COLLECTOR_LINK from=$EDITVIEW_LINKS['EDIT_VIEW_RECORD_COLLECTOR']}
				{assign var=COLLECTOR value=\App\RecordCollector::getInstance($COLLECTOR_LINK->get('linkurl'), $MODULE_NAME)}
				{if isset($COLLECTOR) && $COLLECTOR->isActive()}
					<button type="button" class="btn btn-outline-dark js-popover-tooltip js-record-collector-modal mr-1" {if isset(Vtiger_Field_Model::$tabIndexLastSeq)}tabindex="{Vtiger_Field_Model::$tabIndexLastSeq}"{/if} data-type={$COLLECTOR_LINK->get('linkurl')} data-content="{App\Language::translate({$COLLECTOR->label}, $MODULE_NAME)}" data-js="click|popover">
						<span class="{$COLLECTOR->icon}"></span>
					</button>
				{/if}
			{/foreach}
		{/if}
	</div>
</form>
</div>
</div>
{/strip}
