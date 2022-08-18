{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-dashboards-RssContents -->
	{if $ERRORS}
		<ul class="list-group list-group-flush">
			{foreach from=$ERRORS item=ERROR key=URL}
				<li class="list-group-item">{\App\Purifier::encodeHtml($URL)} - {\App\Purifier::encodeHtml($ERROR)}</li>
			{/foreach}
		</ul>
	{/if}
	{if $LIST_SUBJECTS}
		<table class="table table-sm table-bordered">
			<thead>
				<tr>
					<th>{\App\Language::translate('LBL_SUBJECT', $MODULE_NAME)}</th>
					<th>{\App\Language::translate('LBL_SOURCE', $MODULE_NAME)}</th>
					<th>{\App\Language::translate('LBL_DATE', $MODULE_NAME)}</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$LIST_SUBJECTS item=SUBJECT}
					<tr>
						<td>
							<a href="{\App\Purifier::encodeHtml($SUBJECT['link'])}" target="_blank" rel="noreferrer noopener">
								<strong title="{\App\Purifier::encodeHtml($SUBJECT['fullTitle'])}">{\App\Purifier::encodeHtml($SUBJECT['title'])}</strong>
							</a>
						</td>
						<td>{\App\Purifier::encodeHtml($SUBJECT['source'])}</td>
						<td>{$SUBJECT['date']}</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	{/if}
	<!-- /tpl-Base-dashboards-RssContents -->
{/strip}
