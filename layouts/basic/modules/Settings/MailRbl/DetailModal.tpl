{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-MailRbl-DetailModal -->
<div class="modal-body pt-1">
	{assign var=RECEIVED value=$RECORD->getReceived()}
	{if $RECEIVED}
		<div class="lineOfText mb-2">
			<div>{\App\Language::translate('LBL_MAIL_TRACE_TITLE', $QUALIFIED_MODULE)}</div>
		</div>
		<div class="d-flex align-items-center justify-content-center">
		{foreach item=ROW from=$RECEIVED name=ReceivedForeach}
			<div class="{if count($RECEIVED) > 1} col {else} w-100 {/if} p-0 pr-2 mb-2">
				<div class="card{if $SENDER['key'] === $ROW['key']} u-bg-gray{/if}">
					<div class="card-body p-1">
						<ul class="list-group list-group-flush">
						{foreach item=ITEM_ROWS key=KEY_ROWS from=$ROW}
							{if $ITEM_ROWS && is_array($ITEM_ROWS)}
								<li class="list-group-item p-1">
								{foreach item=ITEM key=KEY from=$ITEM_ROWS}
									{if $ITEM}
										<span class="{$CARD_MAP[$KEY_ROWS][$KEY]['icon']} mr-1" title="{\App\Language::translate($CARD_MAP[$KEY_ROWS][$KEY]['label'], $QUALIFIED_MODULE)}"></span>{\App\Purifier::encodeHtml($ITEM)}<br>
									{/if}
								{/foreach}
								</li>
							{/if}
						{/foreach}
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
		<div>{\App\Language::translate('LBL_MAIL_HEADERS', $QUALIFIED_MODULE)}</div>
	</div>
	<pre class="mb-0">{\App\Purifier::encodeHtml(trim($RECORD->get('header')))}</pre>
	{if $RECORD->get('body')}
		<div class="lineOfText">
			<div>{\App\Language::translate('LBL_MAIL_CONTENT', $QUALIFIED_MODULE)}</div>
		</div>
		<iframe sandbox="allow-same-origin"  class="w-100" frameborder="0" srcdoc="{\App\Purifier::encodeHtml($RECORD->get('body'))}"></iframe>
	{/if}
</div>
<!-- /tpl-Settings-MailRbl-DetailModal -->
{/strip}
