{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="table-responsive">
		<table class="table table-bordered table-sm">
			<thead>
				<tr>
					<th><strong>{\App\Language::translate('LBL_APP_NAME',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{\App\Language::translate('LBL_ADDRESS_URL',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{\App\Language::translate('Status',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{\App\Language::translate('LBL_TYPE_SERVER', $QUALIFIED_MODULE)}</strong></th>
					<th><strong>{\App\Language::translate('LBL_API_KEY',$QUALIFIED_MODULE)}</strong></th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$LIST_SERVERS key=KEY item=SERVER}
					<tr data-id="{$KEY}">
						<td>{$SERVER['name']}</td>
						<td>{$SERVER['acceptable_url']}</td>
						<td>
							{if $SERVER['status'] eq 1}
								{\App\Language::translate('LBL_ACTIVE',$QUALIFIED_MODULE)}
							{else}
								{\App\Language::translate('LBL_INACTIVE',$QUALIFIED_MODULE)}
							{/if}
						</td>
						<td>
							{$SERVER['type']}
						</td>
						<td>
							<div class="action">
								*******************
								<div class="float-right">
									<button class="btn btn-primary btn-sm clipboard" data-copy-attribute="clipboard-text" data-clipboard-text="{\App\Encryption::getInstance()->decrypt($SERVER['api_key'])}">
										<span class="fas fa-copy u-cursor-pointer"></span>
									</button>
									<button class="btn btn-primary btn-sm edit ml-2">
										<span class="fas fa-edit u-cursor-pointer"></span>
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
{/strip}
