{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-MailRbl-ReportModalHeader -->
	<div class="modal js-modal-data {if $LOCK_EXIT}static" data-keyboard="false" {/if} tabindex="-1" data-js="data"
		role="dialog" {foreach from=$MODAL_VIEW->modalData key=KEY item=VALUE} data-{$KEY}="{$VALUE}" {/foreach}>
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
						app.registerModalController();
					</script>
				{/if}
				<div class="modal-header{if isset($MODAL_VIEW->headerClass)} {$MODAL_VIEW->headerClass}{/if}  d-flex align-items-center">
					<h5 class="modal-title">
						{if $MODAL_VIEW->modalIcon}
							<span class="modal-header-icon {$MODAL_VIEW->modalIcon} mr-2"></span>
						{/if}
						{$MODAL_TITLE}
					</h5>
					<div class="ml-auto">
						<button type="button" name="saveButton" class="js-modal__save btn btn-success mr-4">
							<span class="fas fa-paper-plane mr-2"></span>
							{\App\Language::translate('BTN_SEND_REPORT', $MODULE_NAME)}
						</button>
						<button type="button" class="btn btn-danger mr-2" data-dismiss="modal">
							<span class="fas fa-times mr-2"></span>
							{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}
						</button>
					</div>
					{if !$LOCK_EXIT}
						<button type="button" class="close ml-0" data-dismiss="modal"
							aria-label="{\App\Language::translate('LBL_CANCEL')}">
							<span aria-hidden="true">&times;</span>
						</button>
					{/if}
				</div>
				<!-- /tpl-Settings-MailRbl-ReportModalHeader -->
{/strip}
