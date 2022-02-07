{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-ConfReport-Index -->
	{function SHOW_HELP_TEXT ITEM=[] KEY=''}
		{if !empty($ITEM['noParameter'])}<s>{/if}
			{if empty($ITEM['label'])}{$KEY}{else}{\App\Language::translate('LBL_LABEL_'|cat:$ITEM['label'], $MODULE_NAME)}{/if}
			{if !empty($ITEM['noParameter'])}</s>{/if}
		{if !$ITEM['status'] || !empty($ITEM['showHelp'])}
			{assign var="HELP_TEXT" value='LBL_HELP_'|cat:strtoupper(\App\Colors::sanitizeValue($KEY))}
			{assign var="HELP_TEXT_TRANS" value=\App\Language::translateEncodeHtml($HELP_TEXT, $MODULE_NAME)}
			{if !empty($HELP_TEXT_TRANS) && $HELP_TEXT_TRANS!==$HELP_TEXT }
				<a href="#" class="js-popover-tooltip float-right" data-js="popover"
					data-trigger="focus hover" data-placement="right"
					data-content="{$HELP_TEXT_TRANS}">
					<span class="fas fa-info-circle"></span>
				</a>
			{/if}
		{/if}
	{/function}
	{function HIGHLIGHT_ROW ITEM=[]}
		{if !$ITEM['status'] && (empty($ITEM['mode']) ||  $ITEM['mode'] eq 'showErrors')}
			class="table-danger"
		{elseif !$ITEM['status'] && isset($ITEM['mode']) &&  $ITEM['mode'] eq 'showWarnings'}
			class="table-warning"
		{elseif !$ITEM['status'] && isset($ITEM['mode']) &&  $ITEM['mode'] eq 'showInfo'}
			class=""
		{/if}
	{/function}
	<div class="pt-md-0 pt-1">
		<div class="o-breadcrumb widget_header mb-2 d-flex px-2 row">
			<div class="o-breadcrumb__container flex-md-wrap">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
				<div class="my-auto o-header-toggle__actions js-header-toggle__actions d-flex float-right flex-column flex-md-row ml-md-auto pb-md-2 pb-lg-0" id="o-view-actions__container">
					<a class="btn btn-danger btn-sm mr-md-2 u-white-space-md-nowrap mt-1 mt-md-0" href="https://github.com/YetiForceCompany/YetiForceCRM/tree/{App\Version::getShort()}.0/tests/setup" target="_blank" rel="noreferrer noopener">
						<span class="fas fa-server mr-2"></span>{\App\Language::translate('LBL_LAMP_CONFIG_FILES', $MODULE_NAME)}
					</a>
					<button class="btn btn-primary btn-sm mr-md-2 u-white-space-md-nowrap mt-1 mt-md-0 js-download-html" data-html=".contentsDiv" data-file-name="ConfReport" data-js="click">
						<span class="fas fa-download mr-2"></span>{\App\Language::translate('LBL_DOWNLOAD_CONFIG', $MODULE_NAME)}
					</button>
					<button class="btn btn-success btn-sm js-check-php mr-md-2 u-white-space-md-nowrap mt-1 mt-md-0" data-js="click">
						<span class="fab fa-php mr-2"></span>{\App\Language::translate('BTN_CHECK_LATEST_VERSION',$QUALIFIED_MODULE)}
					</button>
					<button class="btn btn-info btn-sm js-test-speed mr-md-2 u-white-space-md-nowrap mt-1 mt-md-0" data-js="click">
						<span class="fas fa-stopwatch mr-2"></span>{\App\Language::translate('BTN_SERVER_SPEED_TEST',$QUALIFIED_MODULE)}
					</button>
					<button class="btn btn-info btn-sm js-db-info mr-md-2 u-white-space-md-nowrap mt-1 mt-md-0" data-js="click">
						<span class="fas fa-database mr-2"></span>{\App\Language::translate('BTN_DB_INFO',$QUALIFIED_MODULE)}
					</button>
				</div>
			</div>
		</div>
		<a class="btn btn-outline-dark d-md-none my-auto o-header-toggle__actions-btn js-header-toggle__actions-btn"
			href="#" data-js="click" role="button" aria-expanded="false" aria-controls="o-view-actions__container">
			<span class="fas fa-ellipsis-h fa-fw" title="{\App\Language::translate('LBL_ACTION_MENU')}"></span>
		</a>
		{if $ERRORS}
			<div class="alert alert-danger" role="alert">
				<span class="fas fa-exclamation-triangle pr-2"></span>{\App\Language::translate('LBL_ERROR_DURING_VERIFICATION', $MODULE_NAME)}<br />
				{foreach from=$ERRORS key=KEY item=ITEM}
					{$KEY} | {\App\Purifier::encodeHtml($ITEM)}<br />
				{/foreach}
			</div>
		{/if}
		<div class="u-columns-count-3 u-columns-gap-1rem u-columns-width-36rem pt-2 text-center u-align-middle-children">
			<div class="u-columns__item pb-3 libraries u-table-min-width-5rem table-responsive-md">
				<table class="table table-bordered table-sm m-0">
					<thead>
						<tr>
							<th colspan="4">
								{\App\Language::translate('LBL_LIBRARY', $MODULE_NAME)}
							</th>
						</tr>
						<tr>
							<th colspan="1" scope="col" class="text-lef">
								{\App\Language::translate('LBL_FILE', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{\App\Language::translate('LBL_MANDATORY', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{\App\Language::translate('LBL_WWW_VALUE', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{\App\Language::translate('LBL_CLI_VALUE', $MODULE_NAME)}
							</th>
						</tr>
					</thead>
					<tbody class="u-word-break-all small">
						{foreach from=$ALL['libraries'] key=KEY item=ITEM}
							<tr data-key="{$KEY}" data-status="{var_export($ITEM['status'])}" {HIGHLIGHT_ROW ITEM=$ITEM}>
								<td class="bg-light text-left u-word-break-keep-all">
									{SHOW_HELP_TEXT ITEM=$ITEM KEY=$KEY}
								</td>
								<td>
									{if isset($ITEM['mandatory'])}
										{if $ITEM['mandatory']}
											{\App\Language::translate('LBL_MANDATORY', $MODULE_NAME)}
										{else}
											{\App\Language::translate('LBL_OPTIONAL', $MODULE_NAME)}
										{/if}
									{else}
										-
									{/if}
								</td>
								{if empty($ITEM['testCli'])}
									<td colspan="2">
										{if !empty($ITEM['www'])}
											{if isset($ITEM['isHtml'])} {$ITEM['www']} {else} {\App\Language::translate($ITEM['www'], $MODULE_NAME)} {/if}
										{/if}
										{if isset($ITEM['www_info'])}
											<a href="#" class="js-show-modal-content ml-2" data-content="{\App\Purifier::encodeHtml($ITEM['www_info'])}" data-class="modal-lg u-table-min-width-10rem" data-js="modal">
												<span class="fas fa-info-circle"></span>
											</a>
										{/if}
									</td>
								{else}
									<td>
										{if !empty($ITEM['www'])}
											{if isset($ITEM['isHtml'])} {$ITEM['www']} {else} {\App\Language::translate($ITEM['www'], $MODULE_NAME)} {/if}
										{/if}
										{if isset($ITEM['www_info'])}
											<a href="#" class="js-show-modal-content ml-2" data-content="{\App\Purifier::encodeHtml($ITEM['www_info'])}" data-class="modal-lg u-table-min-width-10rem" data-js="modal">
												<span class="fas fa-info-circle"></span>
											</a>
										{/if}
									</td>
									<td>
										{if !empty($ITEM['cron'])}
											{if isset($ITEM['isHtml'])} {$ITEM['cron']} {else} {\App\Language::translate($ITEM['cron'], $MODULE_NAME)} {/if}
										{/if}
										{if isset($ITEM['cron_info'])}
											<a href="#" class="js-show-modal-content ml-2" data-content="{\App\Purifier::encodeHtml($ITEM['cron_info'])}" data-class="modal-lg u-table-min-width-10rem" data-js="modal">
												<span class="fas fa-info-circle"></span>
											</a>
										{/if}
									</td>
								{/if}
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			<div class="u-columns__item pb-3 headers table-responsive-md">
				<table class="table table-bordered table-sm m-0">
					<thead>
						<tr>
							<th colspan="5">
								{\App\Language::translate('LBL_HEADERS_SECURITY', $MODULE_NAME)}
							</th>
						</tr>
						<tr>
							<th colspan="1" scope="col" class="text-left">
								{App\Language::translate('LBL_PARAMETER', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_RECOMMENDED', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_PHP_VALUE', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_JS_VALUE', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_CSS_VALUE', $MODULE_NAME)}
							</th>
						</tr>
					</thead>
					<tbody class="u-word-break-all small">
						{foreach from=$ALL['headers'] key=KEY item=ITEM}
							<tr data-key="{$KEY}" data-status="{var_export($ITEM['status'])}" {HIGHLIGHT_ROW ITEM=$ITEM}>
								<td class="bg-light text-left u-word-break-keep-all">
									{SHOW_HELP_TEXT ITEM=$ITEM KEY=$KEY}
								</td>
								<td>
									{if isset($ITEM['recommended'])}
										{if isset($ITEM['isHtml'])} {$ITEM['recommended']} {else} {\App\Purifier::encodeHtml($ITEM['recommended'])} {/if}
									{/if}
								</td>
								<td class="u-w-85px">
									{if !empty($ITEM['www'])}
										{if isset($ITEM['isHtml'])} {$ITEM['www']} {else} {\App\Purifier::encodeHtml($ITEM['www'])} {/if}
									{/if}
								</td>
								<td class="u-w-85px">
									{if !empty($ITEM['js'])}
										{if isset($ITEM['isHtml'])} {$ITEM['js']} {else} {\App\Purifier::encodeHtml($ITEM['js'])} {/if}
									{/if}
								</td>
								<td class="u-w-85px">
									{if !empty($ITEM['css'])}
										{if isset($ITEM['isHtml'])} {$ITEM['css']} {else} {\App\Purifier::encodeHtml($ITEM['css'])} {/if}
									{/if}
								</td>
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
								{\App\Language::translate('LBL_PERFORMANCE_VERIFICATION', $MODULE_NAME)}
							</th>
						</tr>
						<tr>
							<th colspan="1" scope="col" class="text-left">
								{App\Language::translate('LBL_PARAMETER', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_RECOMMENDED', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_WWW_VALUE', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_CLI_VALUE', $MODULE_NAME)}
							</th>
						</tr>
					</thead>
					<tbody class="u-word-break-all small">
						{foreach from=$ALL['performance'] key=KEY item=ITEM}
							<tr data-key="{$KEY}" data-status="{var_export($ITEM['status'])}" {HIGHLIGHT_ROW ITEM=$ITEM}>
								<td class="bg-light text-left u-word-break-keep-all">
									{SHOW_HELP_TEXT ITEM=$ITEM KEY=$KEY}
								</td>
								<td>
									{if isset($ITEM['recommended'])}
										{if isset($ITEM['isHtml'])} {$ITEM['recommended']} {else} {\App\Language::translate($ITEM['recommended'], $MODULE_NAME)} {/if}
									{else}
										-
									{/if}
								</td>
								{if empty($ITEM['testCli'])}
									<td colspan="2">
										{if isset($ITEM['www'])}
											{if isset($ITEM['isHtml'])} {$ITEM['www']} {else} {\App\Language::translate($ITEM['www'], $MODULE_NAME)} {/if}
										{/if}
									</td>
								{else}
									<td>
										{if isset($ITEM['www'])}
											{if isset($ITEM['isHtml'])} {$ITEM['www']} {else} {\App\Language::translate($ITEM['www'], $MODULE_NAME)} {/if}
										{/if}
									</td>
									<td>
										{if isset($ITEM['cron'])}
											{if isset($ITEM['isHtml'])} {$ITEM['cron']} {else} {\App\Language::translate($ITEM['cron'], $MODULE_NAME)} {/if}
										{/if}
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
								{\App\Language::translate('LBL_DENY_PUBLIC_DIR_TITLE', $MODULE_NAME)}
							</th>
						</tr>
						<tr>
							<th colspan="1" scope="col" class="text-left">
								{App\Language::translate('LBL_PUBLIC_DIR', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_DENY_PUBLIC_DIR_STATUS', $MODULE_NAME)}
							</th>
						</tr>
					</thead>
					<tbody class="u-word-break-all small">
						{foreach from=$ALL['publicDirectoryAccess'] key=KEY item=ITEM}
							<tr data-key="{$KEY}" data-status="{var_export($ITEM['status'])}" {HIGHLIGHT_ROW ITEM=$ITEM}>
								<td class="bg-light text-left u-word-break-keep-all">
									{SHOW_HELP_TEXT ITEM=$ITEM KEY=$KEY}
								</td>
								<td colspan="2">
									{if $ITEM['status']}
										{\App\Language::translate('LBL_YES', $MODULE_NAME)}
									{else}
										{\App\Language::translate('LBL_NO', $MODULE_NAME)}
									{/if}
								</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			<div class="u-columns__item pb-3 pathVerification table-responsive-md">
				<table class="table table-bordered table-sm m-0">
					<thead>
						<tr>
							<th colspan="2" scope="col">
								{\App\Language::translate('LBL_PATH_VERIFICATION_TITLE', $MODULE_NAME)}
							</th>
						</tr>
						<tr>
							<th colspan="1" scope="col" class="text-left">
								{App\Language::translate('LBL_PATH', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_IT_WORKS_PROPERLY', $MODULE_NAME)}
							</th>
						</tr>
					</thead>
					<tbody class="u-word-break-all small">
						{foreach from=$ALL['pathVerification'] key=KEY item=ITEM}
							<tr data-key="{$KEY}" data-status="{var_export($ITEM['status'])}" {HIGHLIGHT_ROW ITEM=$ITEM}>
								<td class="bg-light text-left u-word-break-keep-all">
									{SHOW_HELP_TEXT ITEM=$ITEM KEY=$KEY}
								</td>
								<td colspan="2">
									{if $ITEM['status']}
										{\App\Language::translate('LBL_YES', $MODULE_NAME)}
									{else}
										{\App\Language::translate('LBL_NO', $MODULE_NAME)}
									{/if}
								</td>
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
								{\App\Language::translate('LBL_ENVIRONMENTAL_INFORMATION', $MODULE_NAME)}
							</th>
						</tr>
						<tr>
							<th colspan="1" scope="col" class="text-left">
								{App\Language::translate('LBL_PARAMETER', $MODULE_NAME)}
							</th>
							<th colspan=" 1
							" scope="col">
								{App\Language::translate('LBL_WWW_VALUE', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_CLI_VALUE', $MODULE_NAME)}
							</th>
						</tr>
					</thead>
					<tbody class="u-word-break-all small">
						{foreach from=$ALL['environment'] key=KEY item=ITEM}
							<tr data-key="{$KEY}" data-status="{var_export($ITEM['status'])}" {HIGHLIGHT_ROW ITEM=$ITEM}>
								<td class="bg-light text-left u-word-break-keep-all">
									{SHOW_HELP_TEXT ITEM=$ITEM KEY=$KEY}
								</td>
								{if empty($ITEM['testCli'])}
									<td colspan="2">
										{if !empty($ITEM['www'])}
											{if isset($ITEM['isHtml'])} {$ITEM['www']} {else} {\App\Language::translate($ITEM['www'], $MODULE_NAME)} {/if}
										{/if}
									</td>
								{else}
									<td>
										{if !empty($ITEM['www'])}
											{if isset($ITEM['isHtml'])} {$ITEM['www']} {else} {\App\Language::translate($ITEM['www'], $MODULE_NAME)} {/if}
										{/if}
									</td>
									<td>
										{if !empty($ITEM['cron'])}
											{if isset($ITEM['isHtml'])} {$ITEM['cron']} {else} {\App\Language::translate($ITEM['cron'], $MODULE_NAME)} {/if}
										{/if}
									</td>
								{/if}
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
								{\App\Language::translate('LBL_SYSTEM_STABILITY', $MODULE_NAME)}
							</th>
						</tr>
						<tr>
							<th colspan="1" scope="col" class="text-left">
								{App\Language::translate('LBL_PARAMETER', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_RECOMMENDED', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_WWW_VALUE', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_CLI_VALUE', $MODULE_NAME)}
							</th>
						</tr>
					</thead>
					<tbody class="u-word-break-all small">
						{foreach from=$ALL['stability'] key=KEY item=ITEM}
							<tr data-key="{$KEY}" data-status="{var_export($ITEM['status'])}" {HIGHLIGHT_ROW ITEM=$ITEM}>
								<td class="bg-light text-left u-word-break-keep-all">
									{SHOW_HELP_TEXT ITEM=$ITEM KEY=$KEY}
								</td>
								<td>
									{if isset($ITEM['recommended'])}
										{if isset($ITEM['isHtml'])} {$ITEM['recommended']} {else} {\App\Language::translate($ITEM['recommended'], $MODULE_NAME)} {/if}
									{else}
										-
									{/if}
								</td>
								{if empty($ITEM['testCli'])}
									<td colspan="2">
										{if isset($ITEM['www'])}
											{if isset($ITEM['isHtml'])} {$ITEM['www']} {else} {\App\Language::translate($ITEM['www'], $MODULE_NAME)} {/if}
										{/if}
									</td>
								{else}
									<td>
										{if isset($ITEM['www'])}
											{if isset($ITEM['isHtml'])} {$ITEM['www']} {else} {\App\Language::translate($ITEM['www'], $MODULE_NAME)} {/if}
										{/if}
									</td>
									<td>
										{if isset($ITEM['cron'])}
											{if isset($ITEM['isHtml'])} {$ITEM['cron']} {else} {\App\Language::translate($ITEM['cron'], $MODULE_NAME)} {/if}
										{/if}
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
								{\App\Language::translate('LBL_SYSTEM_SECURITY', $MODULE_NAME)}
							</th>
						</tr>
						<tr>
							<th colspan="1" scope="col" class="text-left">
								{App\Language::translate('LBL_PARAMETER', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_RECOMMENDED', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_WWW_VALUE', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_CLI_VALUE', $MODULE_NAME)}
							</th>
						</tr>
					</thead>
					<tbody class="u-word-break-all small">
						{foreach from=$ALL['security'] key=KEY item=ITEM}
							<tr data-key="{$KEY}" data-status="{var_export($ITEM['status'])}" {HIGHLIGHT_ROW ITEM=$ITEM}>
								<td class="bg-light text-left u-word-break-keep-all">
									{SHOW_HELP_TEXT ITEM=$ITEM KEY=$KEY}
								</td>
								<td>
									{if isset($ITEM['recommended'])}
										{if isset($ITEM['isHtml'])} {$ITEM['recommended']} {else} {\App\Language::translate($ITEM['recommended'], $MODULE_NAME)} {/if}
									{else}-
									{/if}
								</td>
								{if empty($ITEM['testCli'])}
									<td colspan="2">
										{if !empty($ITEM['www'])}
											{if isset($ITEM['isHtml'])} {$ITEM['www']} {else} {\App\Language::translate($ITEM['www'], $MODULE_NAME)} {/if}
										{/if}
									</td>
								{else}
									<td>
										{if !empty($ITEM['www'])}
											{if isset($ITEM['isHtml'])} {$ITEM['www']} {else} {\App\Language::translate($ITEM['www'], $MODULE_NAME)} {/if}
										{/if}
									</td>
									<td>
										{if !empty($ITEM['cron'])}
											{if isset($ITEM['isHtml'])} {$ITEM['cron']} {else} {\App\Language::translate($ITEM['cron'], $MODULE_NAME)} {/if}
										{/if}
									</td>
								{/if}
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			<div class="u-columns__item pb-3 functionalVerification table-responsive-md">
				<table class="table table-bordered table-sm m-0">
					<thead>
						<tr>
							<th colspan="2" scope="col">
								{\App\Language::translate('LBL_FUNCTIONAL_VERIFICATION_TITLE', $MODULE_NAME)}
							</th>
						</tr>
						<tr>
							<th colspan="1" scope="col" class="text-left">
								{App\Language::translate('LBL_FN_NAME', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_IT_WORKS_PROPERLY', $MODULE_NAME)}
							</th>
						</tr>
					</thead>
					<tbody class="u-word-break-all small">
						{foreach from=$ALL['functionalVerification'] key=KEY item=ITEM}
							<tr data-key="{$KEY}" data-status="{var_export($ITEM['status'])}" {HIGHLIGHT_ROW ITEM=$ITEM}>
								<td class="bg-light text-left u-word-break-keep-all">
									{SHOW_HELP_TEXT ITEM=$ITEM KEY=$KEY}
								</td>
								<td class="text-capitalize">
									{if empty($ITEM['only_info'])}
										{$ITEM['www']}
									{else}
										<u>{$ITEM['www']}</u>
									{/if}
								</td>
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
								{\App\Language::translate('LBL_DATABASE_INFORMATION', $MODULE_NAME)}
							</th>
						</tr>
						<tr>
							<th colspan="1" scope="col" class="text-left">
								{App\Language::translate('LBL_PARAMETER', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_RECOMMENDED', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								<span>{App\Language::translate('LBL_VALUE', $MODULE_NAME)}</span>
							</th>
						</tr>
					</thead>
					<tbody class="u-word-break-all small">
						{foreach from=$ALL['database'] key=KEY item=ITEM}
							<tr data-key="{$KEY}" data-status="{var_export($ITEM['status'])}" {HIGHLIGHT_ROW ITEM=$ITEM}>
								<td class="bg-light text-left u-word-break-keep-all">
									{SHOW_HELP_TEXT ITEM=$ITEM KEY=$KEY}
								</td>
								{if isset($ITEM['recommended'])}
									<td>
										{if isset($ITEM['isHtml'])} {$ITEM['recommended']} {else} {\App\Language::translate($ITEM['recommended'], $MODULE_NAME)} {/if}
										{if isset($ITEM['values'])}
											<span class="fas fa-info-circle js-popover-tooltip" data-js="popover" data-trigger="focus hover" data-content="{\App\Purifier::encodeHtml(implode('<br>', $ITEM['values']))}"></span>
										{/if}
									</td>
									<td>
										{if isset($ITEM['www'])}
											{if isset($ITEM['isHtml'])} {$ITEM['www']} {else} {\App\Language::translate($ITEM['www'], $MODULE_NAME)} {/if}
										{/if}
									</td>
								{else}
									<td colspan="2">
										{if isset($ITEM['www'])}
											{if isset($ITEM['isHtml'])} {$ITEM['www']} {else} {\App\Language::translate($ITEM['www'], $MODULE_NAME)} {/if}
										{/if}
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
							<th colspan="5" scope="col">
								{\App\Language::translate('LBL_FILES_PERMISSIONS', $QUALIFIED_MODULE)}
							</th>
						</tr>
						<tr>
							<th colspan="1" scope="col" class="text-left">
								{App\Language::translate('LBL_PARAMETER', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_WWW_VALUE', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_CLI_VALUE', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_FILE_OWNER', $MODULE_NAME)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_FILE_PERMISSIONS', $MODULE_NAME)}
							</th>
						</tr>
					</thead>
					<tbody class="u-word-break-all small">
						{foreach from=$ALL['writableFilesAndFolders'] key=KEY item=ITEM}
							<tr data-key="{$KEY}" data-status="{var_export($ITEM['status'])}" {HIGHLIGHT_ROW ITEM=$ITEM}>
								<td class="bg-light text-left u-word-break-keep-all">
									{SHOW_HELP_TEXT ITEM=$ITEM KEY=$KEY}
								</td>
								<td>
									{if !empty($ITEM['www'])}
										{\App\Language::translate($ITEM['www'], $MODULE_NAME)}
									{/if}
								</td>
								<td>
									{if !empty($ITEM['cron'])}
										{\App\Language::translate($ITEM['cron'], $MODULE_NAME)}
									{/if}
								</td>
								<td>
									{if !empty($ITEM['owner'])}
										{\App\Purifier::encodeHtml($ITEM['owner'])}
									{else}-
									{/if}
								</td>
								<td>
									{if !empty($ITEM['perms'])}
										{\App\Purifier::encodeHtml($ITEM['perms'])}
									{else}-
									{/if}
								</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-ConfReport-Index -->
{/strip}
