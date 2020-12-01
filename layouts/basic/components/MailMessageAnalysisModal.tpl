{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-MailRbl-DetailModal -->
<div class="modal-body pt-1">
	{assign var=RECEIVED value=$RECORD->getReceived()}
	{if $RECEIVED}
		<div class="lineOfText mb-2">
			<div>{\App\Language::translate('LBL_MAIL_TRACE_TITLE', $LANG_MODULE_NAME)}</div>
		</div>
		<div class="d-flex align-items-center justify-content-center">
		{foreach item=ROW from=$RECEIVED name=ReceivedForeach}
			<div class="{if count($RECEIVED) > 1} col {else} w-100 {/if} p-0 pr-2 mb-2">
				<div class="u-box-shadow card{if $SENDER['key'] === $ROW['key']} u-bg-modern{/if}">
					<div class="card-body p-1">
						<ul class="list-group list-group-flush text-break">
						{foreach item=ITEM_ROWS key=KEY_ROWS from=$ROW}
							{if $ITEM_ROWS && is_array($ITEM_ROWS)}
								<li class="list-group-item p-1">
								{foreach item=ITEM key=KEY from=$ITEM_ROWS}
									{if $ITEM}
										<span class="{$CARD_MAP[$KEY_ROWS][$KEY]['icon']} mr-1" title="{\App\Language::translate($CARD_MAP[$KEY_ROWS][$KEY]['label'], $LANG_MODULE_NAME)}"></span>{\App\Purifier::encodeHtml($ITEM)}<br>
									{/if}
								{/foreach}
								</li>
							{/if}
						{/foreach}
						{if $SENDER['key'] === $ROW['key']}
							<li class="list-group-item p-1">{\App\Language::translate('LBL_SERVER_IP_FROM', $LANG_MODULE_NAME)}: {$SENDER['ip']}</li>
						{/if}
						</ul>
					</div>
				</div>
			</div>
			{if !$smarty.foreach.ReceivedForeach.last}
				<div class="pr-2">
					<span class="fas fa-chevron-right mt-5 u-fs-2x text-primary my-auto"></span>
				</div>
			{/if}
		{/foreach}
		</div>
	{/if}
	<div class="lineOfText">
		<div>{\App\Language::translate('LBL_MAIL_SENDERS', $LANG_MODULE_NAME)}</div>
	</div>
	<div>
		{foreach key=KEY item=VALUE from=$RECORD->getSenders()}
			{$KEY}: {\App\Purifier::encodeHtml($VALUE)}<br />
		{/foreach}
	</div>
	<div class="lineOfText">
		<div>{\App\Language::translate('LBL_MAIL_HEADERS', $LANG_MODULE_NAME)}</div>
	</div>
	<pre class="mb-0">{\App\Purifier::encodeHtml(trim($RECORD->get('header')))}</pre>
	{if $RECORD->get('body')}
		<div class="lineOfText">
			<div>{\App\Language::translate('LBL_MAIL_CONTENT', $LANG_MODULE_NAME)}</div>
		</div>
		<iframe sandbox="allow-same-origin"  class="w-100" frameborder="0" srcdoc="{\App\Purifier::encodeHtml($RECORD->get('body'))}"></iframe>
	{/if}
</div>
<!-- /tpl-Settings-MailRbl-DetailModal -->
{/strip}
