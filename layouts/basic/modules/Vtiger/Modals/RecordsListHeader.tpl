{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<div class="tpl-Modals-RecordsListHeader modal js-modal-data" tabindex="-1" data-js="data"
	 role="dialog" {foreach from=$MODAL_VIEW->modalData key=KEY item=VALUE} data-{$KEY}="{$VALUE}"{/foreach}>
	<div class="modal-dialog {$MODAL_VIEW->modalSize}" role="document">
	<div class="modal-content">
	{foreach item=MODEL from=$MODAL_CSS}
		<link rel="{$MODEL->getRel()}" href="{$MODEL->getHref()}"/>
	{/foreach}
	{foreach item=MODEL from=$MODAL_SCRIPTS}
		<script type="{$MODEL->getType()}" src="{$MODEL->getSrc()}"></script>
	{/foreach}
	<script type="text/javascript">app.registerModalController();</script>
	<div class="modal-header">
		<h5 class="modal-title">
			{if $MODAL_VIEW->modalIcon}
				<span class="{$MODAL_VIEW->modalIcon} mr-2"></span>
			{/if}
			{App\Language::translate($MODULE_NAME, $MODULE_NAME)}
		</h5>
		<div class="ml-auto">
			{if $SOURCE_MODULE neq 'PriceBooks' && $SOURCE_FIELD neq 'productsRelatedList'}
				<div class="js-pagination-container float-right" data-js="container">
					{include file=App\Layout::getTemplatePath('Pagination.tpl', $MODULE_NAME) VIEWNAME='recordsList'}
				</div>
			{/if}
		</div>
		<button type="button" class="close" data-dismiss="modal" aria-label="{App\Language::translate('LBL_CANCEL')}">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
{/strip}