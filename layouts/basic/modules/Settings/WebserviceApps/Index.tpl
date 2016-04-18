{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="col-xs-12 paddingLRZero">
		<div class="table-responsive">
			<table class="table table-bordered table-condensed">
				<thead>
					<tr>
						<th><strong>{vtranslate('LBL_APP_NAME',$QUALIFIED_MODULE)}</strong></th>
						<th><strong>{vtranslate('LBL_ADDRESS_URL',$QUALIFIED_MODULE)}</strong></th>
						<th><strong>{vtranslate('Status',$QUALIFIED_MODULE)}</strong></th>
						<th><strong>{vtranslate('LBL_TYPE_SERVER', $QUALIFIED_MODULE)}</strong></th>
						<th><strong>{vtranslate('LBL_API_KEY',$QUALIFIED_MODULE)}</strong></th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$LIST_SERVERS item=SERVER}
						<tr data-id="{$SERVER['id']}">
							<td>{$SERVER['name']}</td>
							<td>{$SERVER['acceptable_url']}</td>
							<td>
								{if $SERVER['status'] eq 1}
									{vtranslate('LBL_ACTIVE',$QUALIFIED_MODULE)}
								{else}
									{vtranslate('LBL_INACTIVE',$QUALIFIED_MODULE)}
								{/if}
							</td>
							<td>
								{$SERVER['type']}
							</td>
							<td>
								<div class="action">
									{$SERVER['api_key']}
									<div class="pull-right">
										<button class="btn btn-primary btn-xs edit">
											<span class="glyphicon glyphicon-pencil cursorPointer"></span>
										</button>
										<button class="btn btn-danger btn-xs marginLeft5 remove">
											<span class="glyphicon glyphicon-trash cursorPointer"></span>
										</button>
									</div>
								</div>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
{/strip}
