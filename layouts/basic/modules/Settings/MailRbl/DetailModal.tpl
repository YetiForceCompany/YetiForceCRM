{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-MailRbl-DetailModal -->
<div class="modal-body">
	<div class="row col-12">
	{foreach item=ROW from=$RECORD->getReceived() name=ReceivedForeach}
		<div class="col-sm-3 px-2">
			{if !$smarty.foreach.ReceivedForeach.last}
				<div class="d-flex align-items-center float-right">
					<span class="fas fa-chevron-right mt-5 u-fs-2x text-primary"></span>
				</div>
			{/if}
			<div class="card">
				<div class="card-body p-1">
					{foreach item=ITEM key=KEY from=$ROW}
						{if $ITEM}
							{$KEY}: {$ITEM}<br>
						{/if}
					{/foreach}
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
