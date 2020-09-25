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
				<span class="ml-5 u-fs-19px">
					<span class="js-popover-tooltip" data-class="" data-placement="top" data-content="{\App\Purifier::encodeHtml(implode('<br />',$CHECK_SENDER['info']))}" data-js="popover">
						{\App\Language::translate('LBL_MAIL_SENDER', $QUALIFIED_MODULE)}:
						{if $CHECK_SENDER['status']}
							<span class="ml-2 badge badge-success"><span class="fas fa-check mr-2"></span>{\App\Language::translate('LBL_CORRECT', $QUALIFIED_MODULE)}</span>
						{else}
							<span class="ml-2 badge badge-danger"><span class="fas fa-times mr-2"></span>{\App\Language::translate('LBL_INCORRECT', $QUALIFIED_MODULE)}</span>
						{/if}
					</span>
					<span class="js-popover-tooltip ml-4" data-placement="top" data-content="{\App\Purifier::encodeHtml(\App\Language::translate($CHECK_SPF['desc'], $QUALIFIED_MODULE))}" data-js="popover">
						{\App\Language::translate('LBL_SPF', $QUALIFIED_MODULE)}:
						<span class="ml-2 badge {$CHECK_SPF['class']}"><span class="{$CHECK_SPF['icon']} mr-2"></span>{\App\Language::translate($CHECK_SPF['label'], $QUALIFIED_MODULE)}</span>
					</span>

				</span>
				{if !$LOCK_EXIT}
					<button type="button" class="close" data-dismiss="modal"
							aria-label="{\App\Language::translate('LBL_CANCEL')}">
						<span aria-hidden="true">&times;</span>
					</button>
				{/if}
			</div>
<!-- /tpl-Settings-MailRbl-DetailHeaderModal -->
{/strip}
