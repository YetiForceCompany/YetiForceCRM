{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-MailRbl-DetailModal -->
<div class="modal-body">
	<div class="row col-12">
	{foreach item=ROW from=$RECORD->getReceived() name=ReceivedForeach}
		<div class="col-sm-3 p-0 pr-2 mb-3">
			{if !$smarty.foreach.ReceivedForeach.last}
				<div class="d-flex align-items-center float-right">
					<span class="fas fa-chevron-right mt-5 u-fs-2x text-primary"></span>
				</div>
			{/if}
			<div class="card">
				<div class="card-body p-1">
					<ul class="list-group list-group-flush">
					{foreach item=ITEM_ROWS key=KEY_ROW from=$ROW}
						{if $ITEM_ROWS}
							<li class="list-group-item p-1">
							{foreach item=ITEM key=KEY from=$ITEM_ROWS}
								{if $ITEM}
									<span class="{$CARD_MAP[$KEY_ROW][$KEY]['icon']} mr-1" title="{\App\Language::translate($CARD_MAP[$KEY_ROW][$KEY]['label'], $QUALIFIED_MODULE)}"></span>{\App\Purifier::encodeHtml($ITEM)}<br>
								{/if}
							{/foreach}
							</li>
						{/if}
					{/foreach}
					</ul>
				</div>
			</div>

		</div>
	{/foreach}
	</div>
	<div class="lineOfText">
		<div>{\App\Language::translate('LBL_MAIL_HEADERS', $QUALIFIED_MODULE)}</div>
	</div>
	<pre class="mb-0">{\App\Purifier::encodeHtml($RECORD->get('header'))}</pre>
	{if $RECORD->get('body')}
		<div class="lineOfText">
			<div>{\App\Language::translate('LBL_MAIL_CONTENT', $QUALIFIED_MODULE)}</div>
		</div>
		<iframe sandbox="allow-same-origin"  class="w-100" frameborder="0" srcdoc="{\App\Purifier::encodeHtml($RECORD->get('body'))}"></iframe>
	{/if}
</div>
<!-- /tpl-Settings-MailRbl-DetailModal -->
{/strip}
