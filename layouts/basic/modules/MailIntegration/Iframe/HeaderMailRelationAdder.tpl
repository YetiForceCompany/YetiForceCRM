{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-MailIntegration-Iframe-HeaderMailRelationAdder -->
<div class="input-group input-group-sm my-1">
	<select class="select2 form-control js-modules" data-js="change">
		{foreach item=MODULE from=$MODULES name=MODULES_SELECT}
			{if $smarty.foreach.MODULES_SELECT.first}
				{assign var=IS_EDIT_PERMITTED value=App\Privilege::isPermitted($MODULE, 'EditView')}
			{/if}
			<option value="{$MODULE}" data-add-record="{App\Privilege::isPermitted($MODULE, 'CreateView') && !Vtiger_Module_Model::getInstance($MODULE)->isInventory()}">{\App\Language::translate($MODULE, $MODULE)}</option>
		{/foreach}
	</select>
	<div class="input-group-append">
		<button class="btn btn-light js-add-record js-popover-tooltip mr-3px{if !$IS_EDIT_PERMITTED} d-none{/if}" data-js="popover | click" data-content="{\App\Language::translate('LBL_ADD_RECORD', $MODULE)}" data-js="click">
			<span class="fas fa-plus"></span>
		</button>
		<button class="btn btn-light js-select-record js-popover-tooltip" data-js="popover | click" data-content="{\App\Language::translate('LBL_SELECT_RECORD', $MODULE)}" data-js="click">
			<span class="fas fa-search"></span>
		</button>
	</div>
</div>
<!-- /tpl-MailIntegration-Iframe-HeaderMailRelationAdder -->
{/strip}
