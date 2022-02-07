{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Modals-TreeHeader -->
	<div class="modal js-modal-data {if $LOCK_EXIT}static{/if}" tabindex="-1" data-js="data"
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
				<div class="modal-header d-block">
					<div class="d-flex">
						<h5 class="modal-title">
							{if $MODAL_VIEW->modalIcon}
								<span class="{$MODAL_VIEW->modalIcon} mr-2"></span>
							{/if}
							{$MODAL_TITLE}
						</h5>
						{if !$LOCK_EXIT}
							<button type="button" class="close" data-dismiss="modal"
								aria-label="{\App\Language::translate('LBL_CANCEL')}">
								<span aria-hidden="true">&times;</span>
							</button>
						{/if}
					</div>
					<div class="input-group pt-2">
						<input id="valueSearchTree" type="text" class="form-control"
							placeholder="{\App\Language::translate('LBL_SEARCH', $MODULE)} ...">
						<div class="input-group-append">
							<button id="btnSearchTree" class="btn btn-light" type="button">
								<span class="fas fa-search mr-2"></span>{\App\Language::translate('LBL_SEARCH', $MODULE)}
							</button>
						</div>
					</div>
				</div>
				<!-- /tpl-Base-Modals-TreeHeader -->
{/strip}
