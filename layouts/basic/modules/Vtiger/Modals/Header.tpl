{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Modals-Header -->
	<div class="modal js-modal-data {if $LOCK_EXIT}static" data-keyboard="false{/if}" tabindex="-1" data-js="data" role="dialog" {foreach from=$MODAL_VIEW->modalData key=KEY item=VALUE} data-{$KEY}="{$VALUE}" {/foreach}>
		<div class="modal-dialog {$MODAL_VIEW->modalSize}" role="document">
			<div class="modal-content">
				{foreach item=MODEL from=$MODAL_CSS}
					<link rel="{$MODEL->getRel()}" href="{$MODEL->getHref()}" />
				{/foreach}
				{foreach item=MODEL from=$MODAL_SCRIPTS}
					<script type="{$MODEL->getType()}" src="{$MODEL->getSrc()}"></script>
				{/foreach}
				{if $REGISTER_EVENTS}
					<script type="text/javascript">
						app.registerModalController('{$MODAL_ID}');
					</script>
				{/if}
				<div class="modal-header{if isset($MODAL_VIEW->headerClass)} {$MODAL_VIEW->headerClass}{/if}">
					<h5 class="modal-title">
						{if $MODAL_VIEW->modalIcon}
							<span class="modal-header-icon {$MODAL_VIEW->modalIcon} mr-2"></span>
						{/if}
						{$MODAL_TITLE}
					</h5>
					{if !$LOCK_EXIT}
						<button type="button" class="close" data-dismiss="modal"
							aria-label="{\App\Language::translate('LBL_CANCEL')}">
							<span class="d-print-none"aria-hidden="true">&times;</span>
						</button>
					{/if}
				</div>
				<!-- /tpl-Base-Modals-Header -->
{/strip}
