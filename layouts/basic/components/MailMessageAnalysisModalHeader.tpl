{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-MailRbl-DetailHeaderModal -->
	<div class="modal js-modal-data {if $LOCK_EXIT}static" data-keyboard="false" {/if} tabindex="-1" data-js="data" role="dialog" {foreach from=$MODAL_VIEW->modalData key=KEY item=VALUE} data-{$KEY}="{$VALUE}" {/foreach}>
		<div class="modal-dialog {$MODAL_VIEW->modalSize}" role="document">
			<div class="modal-content">
				{foreach item=MODEL from=$MODAL_CSS}
					<link rel="{$MODEL->getRel()}" href="{$MODEL->getHref()}" />
				{/foreach}
				{foreach item=MODEL from=$MODAL_SCRIPTS}
					<script type="{$MODEL->getType()}" src="{$MODEL->getSrc()}"></script>
				{/foreach}
				<script type="text/javascript">
					app.registerModalController();
				</script>
				<div class="modal-header{if isset($MODAL_VIEW->headerClass)} {$MODAL_VIEW->headerClass}{/if}">
					<h5 class="modal-title">
						{if $MODAL_VIEW->modalIcon}
							<span class="modal-header-icon {$MODAL_VIEW->modalIcon} mr-2"></span>
						{/if}
						{$MODAL_TITLE}
					</h5>
					<span class="ml-auto u-fs-19px">
						<span {if !$VERIFY_SENDER['status']}class="js-popover-tooltip" data-class="u-min-w-470pxr" data-placement="top" data-content="{\App\Language::translate('LBL_ALERT_FAKE_SENDER', $LANG_MODULE_NAME)}<br />{\App\Purifier::encodeHtml($VERIFY_SENDER['info'])}" data-js="popover" {/if}>
							{\App\Language::translate('LBL_MAIL_SENDER', $LANG_MODULE_NAME)}:
							{if $VERIFY_SENDER['status']}
								<span class="ml-2 badge badge-success"><span class="fas fa-check mr-2"></span>{\App\Language::translate('LBL_CORRECT', $LANG_MODULE_NAME)}</span>
							{else}
								<span class="ml-2 badge badge-danger"><span class="fas fa-times mr-2"></span>{\App\Language::translate('LBL_INCORRECT', $LANG_MODULE_NAME)}</span>
							{/if}
						</span>
						<span class="js-popover-tooltip ml-3" data-class="u-min-w-470pxr" data-placement="top" data-content="[{$SENDER['ip']}] {\App\Purifier::encodeHtml(\App\Language::translateArgs($VERIFY_SPF['desc'], $LANG_MODULE_NAME, $VERIFY_SPF['domain']))}" data-js="popover">
							{\App\Language::translate('LBL_SPF', $LANG_MODULE_NAME)}:
							<span class="ml-2 badge {$VERIFY_SPF['class']}"><span class="{$VERIFY_SPF['icon']} mr-2"></span>{\App\Language::translate($VERIFY_SPF['label'], $LANG_MODULE_NAME)}</span>
						</span>
						<span class="js-popover-tooltip ml-3" data-class="u-min-w-470pxr" data-placement="top" data-content="{\App\Purifier::encodeHtml(\App\Language::translate($VERIFY_DKIM['desc'], $LANG_MODULE_NAME))}<hr />{\App\Purifier::encodeHtml($VERIFY_DKIM['logs'])}" data-js="popover">
							{\App\Language::translate('LBL_DKIM', $LANG_MODULE_NAME)}:
							<span class="ml-2 badge {$VERIFY_DKIM['class']}"><span class="{$VERIFY_DKIM['icon']} mr-2"></span>{\App\Language::translate($VERIFY_DKIM['label'], $LANG_MODULE_NAME)}</span>
						</span>
						<span class="js-popover-tooltip ml-3" data-class="u-min-w-470pxr" data-placement="top" data-content="{\App\Purifier::encodeHtml(\App\Language::translate($VERIFY_DMARC['desc'], $LANG_MODULE_NAME))}<hr />{\App\Purifier::encodeHtml($VERIFY_DMARC['logs'])}" data-js="popover">
							{\App\Language::translate('LBL_DMARC', $LANG_MODULE_NAME)}:
							<span class="ml-2 badge {$VERIFY_DMARC['class']}"><span class="{$VERIFY_DMARC['icon']} mr-2"></span>{\App\Language::translate($VERIFY_DMARC['label'], $LANG_MODULE_NAME)}</span>
						</span>
					</span>
					{if !$LOCK_EXIT}
						<button type="button" class="close ml-3" data-dismiss="modal"
							aria-label="{\App\Language::translate('LBL_CANCEL')}">
							<span aria-hidden="true">&times;</span>
						</button>
					{/if}
				</div>
				<!-- /tpl-Settings-MailRbl-DetailHeaderModal -->
{/strip}
