{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<div class="tpl-Modals-RecordConverterHeader modal js-modal-data" tabindex="-1" data-js="data"
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
			<span class="fas fa-exchange-alt mt-3 mr-2"></span>{App\Language::translate('LBL_RECORD_CONVERTER', $MODULE_NAME)}
		</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="{App\Language::translate('LBL_CANCEL')}">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
{/strip}