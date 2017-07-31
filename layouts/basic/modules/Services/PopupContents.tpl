{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	{if $INVENTORY_LIMITED_FROM_POTENTIALS}
		<div class="alert alert-info no-margin">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			{\App\Language::translate('LBL_INVENTORY_LIMITED_FROM_POTENTIALS_INFO', $MODULE)}
		</div>
	{/if}
	{include file=vtemplate_path('PopupContents.tpl','Vtiger')}
{/strip}
