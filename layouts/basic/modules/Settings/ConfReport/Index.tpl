{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-ConfReport-Index">
		<div class="o-breadcrumb widget_header mb-2 d-flex flex-nowrap flex-md-wrap justify-content-between px-2 row">
			<div class="o-breadcrumb__container">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
			<div class="my-auto o-header-toggle__actions js-header-toggle__actions d-flex float-right flex-column flex-md-row ml-md-2 pb-md-2 pb-lg-0"
				 id="o-view-actions__container">
				<button class="btn btn-info js-check-php mr-md-2 flex-md-nowrap mt-1 mt-md-0" data-js="click">
					<span class="fab fa-php mr-1"></span>{App\Language::translate('BTN_CHECK_LATEST_VERSION',$QUALIFIED_MODULE)}
				</button>
				<button class="btn btn-primary js-test-speed mr-md-2 flex-md-nowrap mt-1 mt-md-0" data-js="click">
					<span class="fas fa-stopwatch mr-1"></span>{App\Language::translate('BTN_SERVER_SPEED_TEST',$QUALIFIED_MODULE)}
				</button>
				<button id="download-image" class="btn btn-outline-dark mr-md-2 flex-md-nowrap mt-1 mt-md-0">
					<span class="fas fa-download mr-1"></span>{\App\Language::translate('LBL_DOWNLOAD_CONFIG', $MODULE)}
				</button>
			</div>
		</div>
		<a class="btn btn-outline-dark d-md-none my-auto o-header-toggle__actions-btn js-header-toggle__actions-btn"
		   href="#" data-js="click" role="button" aria-expanded="false" aria-controls="o-view-actions__container">
			<span class="fas fa-ellipsis-h fa-fw" title="{\App\Language::translate('LBL_ACTION_MENU')}"></span>
		</a>
		<div class="u-columns-count-3 u-columns-gap-1rem u-columns-width-36rem pt-2 text-center u-align-middle-children">
			<div class="u-columns__item pb-3 libraries table-responsive-md">
				<table class="table table-bordered table-sm m-0">
					<thead>
					<tr>
						<th colspan="4">
							{App\Language::translate('LBL_LIBRARY', $MODULE)}
						</th>
					</tr>
					<tr>
						<th colspan="1" scope="col" class="text-left u-text-ellipsis">
							{App\Language::translate('LBL_FILE', $MODULE)}
						</th>
						<th colspan="1" scope="col" class="u-text-ellipsis">
							{App\Language::translate('LBL_MANDATORY', $MODULE)}
						</th>
						<th colspan="1" scope="col" class="u-text-ellipsis">
							{App\Language::translate('LBL_WWW_VALUE', $MODULE)}
						</th>
						<th colspan="1" scope="col" class="u-text-ellipsis">
							{App\Language::translate('LBL_CLI_VALUE', $MODULE)}
						</th>
					</tr>
					</thead>
					<tbody class="u-word-break-all small">
					{foreach from=$ALL['libraries'] key=KEY item=ITEM}
						<tr {if !$ITEM['status']}class="table-danger"{/if}>
							<td class="bg-light text-left u-word-break-keep-all">
								{if empty($ITEM['label'])}{$KEY}{else}{App\Language::translate('LBL_LABEL_'|cat:$ITEM['label'], $MODULE)}{/if}
								{if !$ITEM['status']}
									{assign var="HELP_TEXT" value=\App\Language::translateEncodeHtml('LBL_HELP_'|cat:strtoupper(\App\Colors::sanitizeValue($KEY)), $MODULE)}
									{if !empty($HELP_TEXT)}
										<a href="#" class="js-popover-tooltip float-right" data-js="popover"
										   data-trigger="focus hover" data-placement="right"
										   data-content="{$HELP_TEXT}">
											<span class="fas fa-info-circle"></span>
										</a>
									{/if}
								{/if}
							</td>
							<td>
								{if isset($ITEM['mandatory'])}
									{if $ITEM['mandatory']}
										{App\Language::translate('LBL_MANDATORY', $MODULE)}
									{else}
										{App\Language::translate('LBL_OPTIONAL', $MODULE)}
									{/if}
								{else}
									-
								{/if}
							</td>
							{if empty($ITEM['testCli'])}
								<td colspan="2">
									{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], $MODULE)}{/if}
								</td>
							{else}
								<td>
									{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], $MODULE)}{/if}
								</td>
								<td>
									{if !empty($ITEM['cron'])}{App\Language::translate($ITEM['cron'], $MODULE)}{/if}
								</td>
							{/if}
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
			<div class="u-columns__item pb-3 performance table-responsive-md">
				<table class="table table-bordered table-sm m-0">
					<thead>
					<tr>
						<th colspan="4">
							{App\Language::translate('LBL_PERFORMANCE_VERIFICATION', $MODULE)}
						</th>
					</tr>
					<tr>
						<th colspan="1" scope="col" class="text-left u-text-ellipsis">
							{App\Language::translate('LBL_PARAMETER', $MODULE)}
						</th>
						<th colspan="1" scope="col" class="u-text-ellipsis">
							{App\Language::translate('LBL_RECOMMENDED', $MODULE)}
						</th>
						<th colspan="1" scope="col" class="u-text-ellipsis">
							{App\Language::translate('LBL_WWW_VALUE', $MODULE)}
						</th>
						<th colspan="1" scope="col" class="u-text-ellipsis">
							{App\Language::translate('LBL_CLI_VALUE', $MODULE)}
						</th>
					</tr>
					</thead>
					<tbody class="u-word-break-all small">
					{foreach from=$ALL['performance'] key=KEY item=ITEM}
						<tr {if !$ITEM['status']}class="table-danger"{/if}>
							<td class="bg-light text-left u-word-break-keep-all">
								{if empty($ITEM['label'])}{$KEY}{else}{App\Language::translate('LBL_LABEL_'|cat:$ITEM['label'], $MODULE)}{/if}
								{if !$ITEM['status']}
									{assign var="HELP_TEXT" value=\App\Language::translateEncodeHtml('LBL_HELP_'|cat:strtoupper(\App\Colors::sanitizeValue($KEY)), $MODULE)}
									{if !empty($HELP_TEXT)}
										<a href="#" class="js-popover-tooltip float-right" data-js="popover"
										   data-trigger="focus hover" data-placement="right"
										   data-content="{$HELP_TEXT}">
											<span class="fas fa-info-circle"></span>
										</a>
									{/if}
								{/if}
							</td>
							<td>
								{if isset($ITEM['recommended'])}
									{App\Language::translate($ITEM['recommended'], $MODULE)}
								{else}
									-
								{/if}
							</td>
							{if empty($ITEM['testCli'])}
								<td colspan="2">
									{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], $MODULE)}{/if}
								</td>
							{else}
								<td>
									{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], $MODULE)}{/if}
								</td>
								<td>
									{if !empty($ITEM['cron'])}{App\Language::translate($ITEM['cron'], $MODULE)}{/if}
								</td>
							{/if}
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
			<div class="u-columns__item pb-3 publicDirectoryAccess table-responsive-md">
				<table class="table table-bordered table-sm m-0">
					<thead>
					<tr>
						<th colspan="2" scope="col">
							{App\Language::translate('LBL_DENY_PUBLIC_DIR_TITLE', $MODULE)}
						</th>
					</tr>
					<tr>
						<th colspan="1" scope="col" class="text-left u-text-ellipsis">
							{App\Language::translate('LBL_PUBLIC_DIR', $MODULE)}
						</th>
						<th colspan="1" scope="col" class="u-text-ellipsis">
							{App\Language::translate('LBL_DENY_PUBLIC_DIR_STATUS', $MODULE)}
						</th>
					</tr>
					</thead>
					<tbody class="u-word-break-all small">
					{foreach from=$ALL['publicDirectoryAccess'] key=KEY item=ITEM}
						<tr {if !$ITEM['status']}class="table-danger"{/if}>
							<td class="bg-light text-left u-word-break-keep-all">
								{if empty($ITEM['label'])}{$KEY}{else}{App\Language::translate('LBL_LABEL_'|cat:$ITEM['label'], $MODULE)}{/if}
								{if !$ITEM['status']}
									{assign var="HELP_TEXT" value=\App\Language::translateEncodeHtml('LBL_HELP_'|cat:strtoupper(\App\Colors::sanitizeValue($KEY)), $MODULE)}
									{if !empty($HELP_TEXT)}
										<a href="#" class="js-popover-tooltip float-right" data-js="popover"
										   data-trigger="focus hover" data-placement="right"
										   data-content="{$HELP_TEXT}">
											<span class="fas fa-info-circle"></span>
										</a>
									{/if}
								{/if}
							</td>
							<td colspan="2">
								{if $ITEM.status}
									{App\Language::translate('LBL_YES', $MODULE)}
								{else}
									{App\Language::translate('LBL_NO', $MODULE)}
								{/if}
							</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
			<div class="u-columns__item pb-3 stability table-responsive-md">
				<table class="table table-bordered table-sm m-0">
					<thead>
					<tr>
						<th colspan="4">
							{App\Language::translate('LBL_SYSTEM_STABILITY', $MODULE)}
						</th>
					</tr>
					<tr>
						<th colspan="1" scope="col" class="text-left u-text-ellipsis">
							{App\Language::translate('LBL_PARAMETER', $MODULE)}
						</th>
						<th colspan="1" scope="col" class="u-text-ellipsis">
							{App\Language::translate('LBL_RECOMMENDED', $MODULE)}
						</th>
						<th colspan="1" scope="col" class="u-text-ellipsis">
							{App\Language::translate('LBL_WWW_VALUE', $MODULE)}
						</th>
						<th colspan="1" scope="col" class="u-text-ellipsis">
							{App\Language::translate('LBL_CLI_VALUE', $MODULE)}
						</th>
					</tr>
					</thead>
					<tbody class="u-word-break-all small">
					{foreach from=$ALL['stability'] key=KEY item=ITEM}
						<tr {if !$ITEM['status']}class="table-danger"{/if}>
							<td class="bg-light text-left u-word-break-keep-all">
								{if empty($ITEM['label'])}{$KEY}{else}{App\Language::translate('LBL_LABEL_'|cat:$ITEM['label'], $MODULE)}{/if}
								{if !$ITEM['status']}
									{assign var="HELP_TEXT" value=\App\Language::translateEncodeHtml('LBL_HELP_'|cat:strtoupper(\App\Colors::sanitizeValue($KEY)), $MODULE)}
									{if !empty($HELP_TEXT)}
										<a href="#" class="js-popover-tooltip float-right" data-js="popover"
										   data-trigger="focus hover" data-placement="right"
										   data-content="{$HELP_TEXT}">
											<span class="fas fa-info-circle"></span>
										</a>
									{/if}
								{/if}
							</td>
							<td>
								{if isset($ITEM['recommended'])}
									{App\Language::translate($ITEM['recommended'], $MODULE)}
								{else}
									-
								{/if}
							</td>
							{if empty($ITEM['testCli'])}
								<td colspan="2">
									{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], $MODULE)}{/if}
								</td>
							{else}
								<td>
									{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], $MODULE)}{/if}
								</td>
								<td>
									{if !empty($ITEM['cron'])}{App\Language::translate($ITEM['cron'], $MODULE)}{/if}
								</td>
							{/if}
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
			<div class="u-columns__item pb-3 environment table-responsive-md">
				<table class="table table-bordered table-sm m-0">
					<thead>
					<tr>
						<th colspan="3" scope="col">
							{App\Language::translate('LBL_ENVIRONMENTAL_INFORMATION', $MODULE)}
						</th>
					</tr>
					<tr>
						<th colspan="1" scope="col" class="text-left u-text-ellipsis">
							{App\Language::translate('LBL_PARAMETER', $MODULE)}
						</th>
						<th colspan=" 1
							" scope="col" class="u-text-ellipsis">
							{App\Language::translate('LBL_WWW_VALUE', $MODULE)}
						</th>
						<th colspan="1" scope="col" class="u-text-ellipsis">
							{App\Language::translate('LBL_CLI_VALUE', $MODULE)}
						</th>
					</tr>
					</thead>
					<tbody class="u-word-break-all small">
					{foreach from=$ALL['environment'] key=KEY item=ITEM}
						<tr {if !$ITEM['status']}class="table-danger"{/if}>
							<td class="bg-light text-left u-word-break-keep-all">
								{if empty($ITEM['label'])}{$KEY}{else}{App\Language::translate('LBL_LABEL_'|cat:$ITEM['label'], $MODULE)}{/if}
								{if !$ITEM['status']}
									{assign var="HELP_TEXT" value=\App\Language::translateEncodeHtml('LBL_HELP_'|cat:strtoupper(\App\Colors::sanitizeValue($KEY)), $MODULE)}
									{if !empty($HELP_TEXT)}
										<a href="#" class="js-popover-tooltip float-right" data-js="popover"
										   data-trigger="focus hover" data-placement="right"
										   data-content="{$HELP_TEXT}">
											<span class="fas fa-info-circle"></span>
										</a>
									{/if}
								{/if}
							</td>
							{if empty($ITEM['testCli'])}
								<td colspan="2">
									{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], $MODULE)}{/if}
								</td>
							{else}
								<td>
									{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], $MODULE)}{/if}
								</td>
								<td>
									{if !empty($ITEM['cron'])}{App\Language::translate($ITEM['cron'], $MODULE)}{/if}
								</td>
							{/if}
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
			<div class="u-columns__item pb-3 writableFilesAndFolders table-responsive-md">
				<table class="table table-bordered table-sm m-0">
					<thead>
					<tr>
						<th colspan="3" scope="col">
							{App\Language::translate('LBL_FILES_PERMISSIONS', $QUALIFIED_MODULE)}
						</th>
					</tr>
					<tr>
						<th colspan="1" scope="col" class="text-left u-text-ellipsis">
							{App\Language::translate('LBL_PARAMETER', $MODULE)}
						</th>
						<th colspan=" 1
							" scope="col" class="u-text-ellipsis">
							{App\Language::translate('LBL_WWW_VALUE', $MODULE)}
						</th>
						<th colspan="1" scope="col" class="u-text-ellipsis">
							{App\Language::translate('LBL_CLI_VALUE', $MODULE)}
						</th>
					</tr>
					</thead>
					<tbody class="u-word-break-all small">
					{foreach from=$ALL['writableFilesAndFolders'] key=KEY item=ITEM}
						<tr {if !$ITEM['status']}class="table-danger"{/if}>
							<td class="bg-light text-left u-word-break-keep-all">
								{if empty($ITEM['label'])}{$KEY}{else}{App\Language::translate('LBL_LABEL_'|cat:$ITEM['label'], $MODULE)}{/if}
								{if !$ITEM['status']}
									{assign var="HELP_TEXT" value=\App\Language::translateEncodeHtml('LBL_HELP_'|cat:strtoupper(\App\Colors::sanitizeValue($KEY)), $MODULE)}
									{if !empty($HELP_TEXT)}
										<a href="#" class="js-popover-tooltip float-right" data-js="popover"
										   data-trigger="focus hover" data-placement="right"
										   data-content="{$HELP_TEXT}">
											<span class="fas fa-info-circle"></span>
										</a>
									{/if}
								{/if}
							</td>
							{if empty($ITEM['testCli'])}
								<td colspan="2">
									{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], $MODULE)}{/if}
								</td>
							{else}
								<td>
									{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], $MODULE)}{/if}
								</td>
								<td>
									{if !empty($ITEM['cron'])}{App\Language::translate($ITEM['cron'], $MODULE)}{/if}
								</td>
							{/if}
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
			<div class="u-columns__item pb-3 security table-responsive-md">
				<table class="table table-bordered table-sm m-0">
					<thead>
					<tr>
						<th colspan="4">
							{App\Language::translate('LBL_SYSTEM_SECURITY', $MODULE)}
						</th>
					</tr>
					<tr>
						<th colspan="1" scope="col" class="text-left u-text-ellipsis">
							{App\Language::translate('LBL_PARAMETER', $MODULE)}
						</th>
						<th colspan="1" scope="col" class="u-text-ellipsis">
							{App\Language::translate('LBL_RECOMMENDED', $MODULE)}
						</th>
						<th colspan="1" scope="col" class="u-text-ellipsis">
							{App\Language::translate('LBL_WWW_VALUE', $MODULE)}
						</th>
						<th colspan="1" scope="col" class="u-text-ellipsis">
							{App\Language::translate('LBL_CLI_VALUE', $MODULE)}
						</th>
					</tr>
					</thead>
					<tbody class="u-word-break-all small">
					{foreach from=$ALL['security'] key=KEY item=ITEM}
						<tr {if !$ITEM['status']}class="table-danger"{/if}>
							<td class="bg-light text-left u-word-break-keep-all">
								{if empty($ITEM['label'])}{$KEY}{else}{App\Language::translate('LBL_LABEL_'|cat:$ITEM['label'], $MODULE)}{/if}
								{if !$ITEM['status']}
									{assign var="HELP_TEXT" value=\App\Language::translateEncodeHtml('LBL_HELP_'|cat:strtoupper(\App\Colors::sanitizeValue($KEY)), $MODULE)}
									{if !empty($HELP_TEXT)}
										<a href="#" class="js-popover-tooltip float-right" data-js="popover"
										   data-trigger="focus hover" data-placement="right"
										   data-content="{$HELP_TEXT}">
											<span class="fas fa-info-circle"></span>
										</a>
									{/if}
								{/if}
							</td>
							<td>
								{if isset($ITEM['recommended'])}
									{App\Language::translate($ITEM['recommended'], $MODULE)}
								{else}
									-
								{/if}
							</td>
							{if empty($ITEM['testCli'])}
								<td colspan="2">
									{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], $MODULE)}{/if}
								</td>
							{else}
								<td>
									{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], $MODULE)}{/if}
								</td>
								<td>
									{if !empty($ITEM['cron'])}{App\Language::translate($ITEM['cron'], $MODULE)}{/if}
								</td>
							{/if}
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>

			<div class="u-columns__item pb-3 database table-responsive-md">
				<table class="table table-bordered table-sm m-0">
					<thead>
					<tr>
						<th colspan="4">
							{App\Language::translate('LBL_DATABASE_INFORMATION', $MODULE)}
						</th>
					</tr>
					<tr>
						<th colspan="1" scope="col" class="text-left u-text-ellipsis">
							{App\Language::translate('LBL_PARAMETER', $MODULE)}
						</th>
						<th colspan="1" scope="col" class="u-text-ellipsis">
							{App\Language::translate('LBL_RECOMMENDED', $MODULE)}
						</th>
						<th colspan="1" scope="col" class="u-text-ellipsis">
							<span>{App\Language::translate('LBL_VALUE', $MODULE)}</span>
						</th>
					</tr>
					</thead>
					<tbody class="u-word-break-all small">
					{foreach from=$ALL['database'] key=KEY item=ITEM}
						<tr {if !$ITEM['status']}class="table-danger"{/if}>
							<td class="bg-light text-left u-word-break-keep-all">
								{if empty($ITEM['label'])}{$KEY}{else}{App\Language::translate('LBL_LABEL_'|cat:$ITEM['label'], $MODULE)}{/if}
								{if !$ITEM['status']}
									{assign var="HELP_TEXT" value=\App\Language::translateEncodeHtml('LBL_HELP_'|cat:strtoupper(\App\Colors::sanitizeValue($KEY)), $MODULE)}
									{if !empty($HELP_TEXT)}
										<a href="#" class="js-popover-tooltip float-right" data-js="popover"
										   data-trigger="focus hover" data-placement="right"
										   data-content="{$HELP_TEXT}">
											<span class="fas fa-info-circle"></span>
										</a>
									{/if}
								{/if}
							</td>
							{if isset($ITEM['recommended'])}
								<td>
									{$ITEM['recommended']}
								</td>
								<td>
									{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], $MODULE)}{/if}
								</td>
							{else}
								<td colspan="2">
									{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], $MODULE)}{/if}
								</td>
							{/if}
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>
{/strip}