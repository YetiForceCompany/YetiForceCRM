{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-MailRbl-DetailModal -->
<div class="modal-body pt-1">
	{assign var=RECEIVED value=$RECORD->getReceived()}
	{if $RECEIVED}
		<div class="lineOfText mb-2">
			<div>{\App\Language::translate('LBL_MAIL_TRACE_TITLE', $LANG_MODULE_NAME)}</div>
		</div>
		<div class="row col-12 m-0">
			{assign var=TABLE_HEADERS value=['fromName', 'fromIP', 'byName', 'extraWith', 'extraComments']}
			<table class="table table-sm p-0 pr-2 mb-0 o-tab__container">
				<thead>
					<tr>
						{foreach item=ITEM_ROWS from=$TABLE_HEADERS}
							<th class="text-center">
								<span class="{$CARD_MAP[$ITEM_ROWS]['icon']} mr-1" title="{\App\Language::translate($CARD_MAP[$ITEM_ROWS]['title'], $LANG_MODULE_NAME)}"></span> {{\App\Language::translate($CARD_MAP[$ITEM_ROWS]['label'], $LANG_MODULE_NAME)}}
							</th>
						{/foreach}
					</tr>
				</thead>
				<tbody>
					{foreach item=ROW from=$RECEIVED}
						<tr class="{if $SENDER['key'] === $ROW['key']} bg-light {/if}">
							{foreach item=ITEM_ROWS  from=$TABLE_HEADERS}
								<td class="text-center u-min-w-150px">
									{if isset($ROW[$ITEM_ROWS])}
										{if $ITEM_ROWS eq 'fromIP' && $SENDER['key'] eq $ROW['key'] && $SENDER['ip']}
											<a href="https://soc.yetiforce.com/search?ip={$SENDER['ip']}" class="ml-2" target="_blank" title="soc.yetiforce.com">{\App\Purifier::encodeHtml($ROW[$ITEM_ROWS])}</a>
										{else}
											{\App\Purifier::encodeHtml($ROW[$ITEM_ROWS])}
										{/if}
									{/if}
								</td>
							{/foreach}
						</tr>
					{/foreach}
				</tbody>
			</table>
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
