{*<!-- {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-WebserviceApps-Index -->
	{assign var=CHECK_ALERT value=\App\YetiForce\Shop::checkAlert('YetiForceWebservicePremium')}
	{if $IS_PORTAL && $CHECK_ALERT}
		<div class="alert alert-warning">
			<span class="yfi-premium mr-2 u-fs-2em color-red-600 float-left"></span>
			{\App\Language::translate($CHECK_ALERT, 'Settings::YetiForce')} ({\App\Language::translate('WebservicePremium', $QUALIFIED_MODULE)})
			<a class="btn btn-primary btn-sm ml-2" href="index.php?parent=Settings&module=YetiForce&view=Shop&product=YetiForceWebservicePremium&mode=showProductModal"><span class="yfi yfi-shop mr-2"></span>{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}</a>
		</div>
	{/if}
	<div class="table-responsive">
		<table class="table table-bordered table-sm">
			<thead>
				<tr>
					<th><strong>{\App\Language::translate('LBL_APP_NAME',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{\App\Language::translate('Status',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{\App\Language::translate('LBL_TYPE_SERVER', $QUALIFIED_MODULE)}</strong></th>
					<th><strong>{\App\Language::translate('LBL_ALLOWED_IPS',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{\App\Language::translate('LBL_PUBLIC_URL',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{\App\Language::translate('LBL_API_KEY',$QUALIFIED_MODULE)}</strong></th>
				</tr>
			</thead>
			<tbody>
				{assign var=ALERT value=!\App\YetiForce\Shop::check('YetiForceWebservicePremium')}
				{foreach from=$LIST_SERVERS key=KEY item=SERVER}
					<tr data-id="{$KEY}">
						<td>{\App\Purifier::encodeHtml($SERVER['name'])}</td>
						<td>
							{if $SERVER['status'] eq 1}
								{\App\Language::translate('LBL_ACTIVE',$QUALIFIED_MODULE)}
							{else}
								{\App\Language::translate('LBL_INACTIVE',$QUALIFIED_MODULE)}
							{/if}
						</td>
						<td class="{if 'WebservicePremium' === $SERVER['type'] &&  $ALERT}bg-color-red-100{/if}">
							{\App\Language::translate($SERVER['type'], $QUALIFIED_MODULE)}
							{if 'WebservicePremium' === $SERVER['type'] &&  $ALERT}
								{assign var=ALERT_MESSAGE value="{\App\Purifier::encodeHtml(App\Language::translateArgs('LBL_PAID_FUNCTIONALITY', $QUALIFIED_MODULE))} <a target='_blank' href='index.php?module=YetiForce&parent=Settings&view=Shop'>{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}</a>"}
								<span class="yfi-premium u-fs-xlg color-red-600 float-right js-popover-tooltip" data-class="u-min-w-500px" data-content="{$ALERT_MESSAGE}"></span>
							{/if}
						</td>
						<td>{\App\Purifier::encodeHtml($SERVER['ips'])}</td>
						<td>{\App\Purifier::encodeHtml($SERVER['url'])}</td>
						<td>
							<div class="action">
								*******************
								<div class="float-right">
									<button class="btn btn-primary btn-sm clipboard" data-copy-attribute="clipboard-text" data-clipboard-text="{\App\Purifier::encodeHtml(\App\Encryption::getInstance()->decrypt($SERVER['api_key']))}">
										<span class="fas fa-copy u-cursor-pointer"></span>
									</button>
									<button class="btn btn-primary btn-sm edit ml-2">
										<span class="yfi yfi-full-editing-view u-cursor-pointer"></span>
									</button>
									<button class="btn btn-danger btn-sm ml-2 remove">
										<span class="fas fa-trash-alt u-cursor-pointer"></span>
									</button>
								</div>
							</div>
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	<!-- /tpl-Settings-WebserviceApps-Index -->
{/strip}
