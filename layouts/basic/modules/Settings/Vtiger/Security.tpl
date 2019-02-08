{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="securityIndexPage table-responsive">
		<table class="table tableRWD table-bordered table-sm themeTableColor confTable">
			<thead>
				<tr class="blockHeader">
					<th colspan="3" class="mediumWidthType">
						<span>{App\Language::translate('LBL_SYSTEM_SECURITY', 'Settings::ConfReport')}</span>
					</th>
				</tr>
				<tr class="blockHeader">
					<th colspan="1" class="mediumWidthType">
						<span>{App\Language::translate('LBL_PARAMETER', 'Settings::ConfReport')}</span>
					</th>
					<th colspan="1" class="mediumWidthType">
						<span>{App\Language::translate('LBL_RECOMMENDED', 'Settings::ConfReport')}</span>
					</th>
					<th colspan="1" class="mediumWidthType">
						<span>{App\Language::translate('LBL_VALUE', 'Settings::ConfReport')}</span>
					</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=Settings_ConfReport_Module_Model::getSecurityConf() key=key item=item}
					<tr {if !empty($item.status)}class="table-danger"{/if}>
						<td>
							<label>{$key}</label>
							{if !empty($item.help) && !empty($item.status)}<a href="#" class="js-popover-tooltip float-right text-dark" data-js="popover" data-trigger="focus" data-placement="right" data-content="{App\Language::translate($item.help, 'Settings::ConfReport')}"><span class="fas fa-info-circle"></span></a>{/if}
						</td>
						<td><label>{App\Language::translate($item.recommended, 'Settings::ConfReport')}</label></td>
						<td><label>{App\Language::translate($item.current, 'Settings::ConfReport')}</label></td>
					</tr>
				{/foreach}
			</tbody>
		</table>
		{if $SENSIOLABS}
			<br />
			<table class="table tableRWD table-bordered table-sm themeTableColor confTable">
				<thead>
					<tr class="blockHeader">
						<th colspan="4" class="mediumWidthType">
							<span>{App\Language::translate('LBL_SECURITY_ADVISORIES_CHECKER', 'Settings::ConfReport')}</span>
						</th>
					</tr>
					<tr class="blockHeader">
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_LIB_NAME', 'Settings::ConfReport')}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_VULNERABILITY_NAME', 'Settings::ConfReport')}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_VULNERABILITY_URL', 'Settings::ConfReport')}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>CVE</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$SENSIOLABS key=LIB_NAME item=LIB}
						{foreach from=$LIB['advisories'] item=ADVISORIE}
							<tr>
								<td><label>{$LIB_NAME} ({$LIB['version']})</label></td>
								<td><label>{$ADVISORIE['title']}</label></td>
								<td><label><a title="{$ADVISORIE['cve']}" target="_blank" rel="noreferrer noopener" href="{$ADVISORIE['link']}">{$ADVISORIE['link']}</a></label></td>
								<td><label>{$ADVISORIE['cve']}</label></td>
							</tr>
						{/foreach}
					{/foreach}
				</tbody>
			</table>
		{/if}
		{assign var="ACCESS_FOR_ADMIN" value=App\Log::getLogs('access_for_admin', 'oneDay')}
		{if $ACCESS_FOR_ADMIN}
			<br />
			<table class="table tableRWD table-bordered table-sm themeTableColor confTable">
				<thead>
					<tr class="blockHeader">
						<th colspan="3" class="mediumWidthType">
							<span>{App\Language::translate('LBL_LOG_ACCESS_FOR_ADMIN', 'Settings::Vtiger')}</span>
						</th>
					</tr>
					<tr class="blockHeader">
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_DATE')}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_USER')}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>Url</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$ACCESS_FOR_ADMIN item=item}
						<tr>
							<td><label>{$item['date']}</label></td>
							<td><label>{$item['username']}</label></td>
							<td><label>{$item['url']}</label></td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		{/if}
		{assign var="ACCESS_FOR_RECORD" value=App\Log::getLogs('access_to_record', 'oneDay')}
		{if $ACCESS_FOR_RECORD}
			<br />
			<table class="table tableRWD table-bordered table-sm themeTableColor confTable">
				<thead>
					<tr class="blockHeader">
						<th colspan="4" class="mediumWidthType">
							<span>{App\Language::translate('LBL_LOG_ACCESS_TO_RECORD', 'Settings::Vtiger')}</span>
						</th>
					</tr>
					<tr class="blockHeader">
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_DATE')}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_USER')}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_RECORD_ID','Other.TextParser')}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_MODULE_NAME')}</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$ACCESS_FOR_RECORD item=item}
						<tr>
							<td><label>{$item['date']}</label></td>
							<td><label>{$item['username']}</label></td>
							<td><label>{$item['record']}</label></td>
							<td><label>{App\Language::translate($item['module'], $item['module'])}</label></td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		{/if}
		{assign var="ACCESS_FOR_API" value=App\Log::getLogs('access_for_api', 'oneDay')}
		{if $ACCESS_FOR_API}
			<br />
			<table class="table tableRWD table-bordered table-sm themeTableColor confTable">
				<thead>
					<tr class="blockHeader">
						<th colspan="3" class="mediumWidthType">
							<span>{App\Language::translate('LBL_LOG_ACCESS_FOR_API', 'Settings::Vtiger')}</span>
						</th>
					</tr>
					<tr class="blockHeader">
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_DATE')}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_USER')}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_USER_IP_ADDRESS','Settings::Vtiger')}</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$ACCESS_FOR_API item=item}
						<tr>
							<td><label>{$item['date']}</label></td>
							<td><label>{$item['username']}</label></td>
							<td><label>{$item['ip']}</label></td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		{/if}
		{assign var="ACCESS_FOR_USER" value=App\Log::getLogs('access_for_user', 'oneDay')}
		{if $ACCESS_FOR_USER}
			<br />
			<table class="table tableRWD table-bordered table-sm themeTableColor confTable">
				<thead>
					<tr class="blockHeader">
						<th colspan="4" class="mediumWidthType">
							<span>{App\Language::translate('LBL_LOG_ACCESS_FOR_USER', 'Settings::Vtiger')}</span>
						</th>
					</tr>
					<tr class="blockHeader">
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_DATE')}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_USER')}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_USER_IP_ADDRESS','Settings::Vtiger')}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>Url</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$ACCESS_FOR_USER item=item}
						<tr>
							<td><label>{$item['date']}</label></td>
							<td><label>{$item['username']}</label></td>
							<td><label>{$item['ip']}</label></td>
							<td><label>{$item['url']}</label></td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		{/if}
	</div>
{/strip}
