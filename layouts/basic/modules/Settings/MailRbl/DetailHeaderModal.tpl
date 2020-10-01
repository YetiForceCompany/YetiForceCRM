{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-MailRbl-DetailHeaderModal -->
<div class="modal js-modal-data {if $LOCK_EXIT}static" data-keyboard="false"{/if} tabindex="-1" data-js="data"
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
			<div class="modal-header{if isset($MODAL_VIEW->headerClass)} {$MODAL_VIEW->headerClass}{/if}">
				<h5 class="modal-title">
					{if $MODAL_VIEW->modalIcon}
						<span class="modal-header-icon {$MODAL_VIEW->modalIcon} mr-2"></span>
					{/if}
					{$MODAL_TITLE}
				</h5>
				<span class="ml-auto u-fs-19px">
					<span {if !$VERIFY_SENDER['status']}class="js-popover-tooltip" data-class="u-min-w-470px" data-placement="top" data-content="{\App\Language::translate('LBL_MAIL_SENDERS_DESC', $QUALIFIED_MODULE)}<br />{\App\Purifier::encodeHtml(implode('<br />',$VERIFY_SENDER['info']))}" data-js="popover" {/if}>
						{\App\Language::translate('LBL_MAIL_SENDER', $QUALIFIED_MODULE)}:
						{if $VERIFY_SENDER['status']}
							<span class="ml-2 badge badge-success"><span class="fas fa-check mr-2"></span>{\App\Language::translate('LBL_CORRECT', $QUALIFIED_MODULE)}</span>
						{else}
							<span class="ml-2 badge badge-danger"><span class="fas fa-times mr-2"></span>{\App\Language::translate('LBL_INCORRECT', $QUALIFIED_MODULE)}</span>
						{/if}
					</span>
					<span class="js-popover-tooltip ml-3" data-class="u-min-w-470px" data-placement="top" data-content="{\App\Purifier::encodeHtml(\App\Language::translate($VERIFY_SPF['desc'], $QUALIFIED_MODULE))}" data-js="popover">
						{\App\Language::translate('LBL_SPF', $QUALIFIED_MODULE)}:
						<span class="ml-2 badge {$VERIFY_SPF['class']}"><span class="{$VERIFY_SPF['icon']} mr-2"></span>{\App\Language::translate($VERIFY_SPF['label'], $QUALIFIED_MODULE)}</span>
					</span>
					<span class="js-popover-tooltip ml-3" data-class="u-min-w-470px" data-placement="top" data-content="{\App\Purifier::encodeHtml(\App\Language::translate($VERIFY_DKIM['desc'], $QUALIFIED_MODULE))}<hr />{\App\Purifier::encodeHtml($VERIFY_DKIM['logs'])}" data-js="popover">
						{\App\Language::translate('LBL_DKIM', $QUALIFIED_MODULE)}:
						<span class="ml-2 badge {$VERIFY_DKIM['class']}"><span class="{$VERIFY_DKIM['icon']} mr-2"></span>{\App\Language::translate($VERIFY_DKIM['label'], $QUALIFIED_MODULE)}</span>
					</span>
					<span class="js-popover-tooltip ml-3" data-class="u-min-w-470px" data-placement="top" data-content="{\App\Purifier::encodeHtml(\App\Language::translate($VERIFY_DMARC['desc'], $QUALIFIED_MODULE))}<hr />{\App\Purifier::encodeHtml($VERIFY_DMARC['logs'])}" data-js="popover">
						{\App\Language::translate('LBL_DMARC', $QUALIFIED_MODULE)}:
						<span class="ml-2 badge {$VERIFY_DMARC['class']}"><span class="{$VERIFY_DMARC['icon']} mr-2"></span>{\App\Language::translate($VERIFY_DMARC['label'], $QUALIFIED_MODULE)}</span>
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
