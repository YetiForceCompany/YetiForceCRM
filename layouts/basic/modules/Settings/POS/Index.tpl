{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="col-xs-12 paddingLRZero">
		<div class="table-responsive">
			<table class="table table-bordered table-condensed">
				<thead>
					<tr>
						<th><strong>{vtranslate('First Name',$QUALIFIED_MODULE)}</strong></th>
						<th><strong>{vtranslate('Last Name',$QUALIFIED_MODULE)}</strong></th>
						<th><strong>{vtranslate('SINGLE_Emails',$QUALIFIED_MODULE)}</strong></th>	
						<th><strong>{vtranslate('LBL_LOGIN',$QUALIFIED_MODULE)}</strong></th>
						<th><strong>{vtranslate('LBL_PASS',$QUALIFIED_MODULE)}</strong></th>
						<th><strong>{vtranslate('Single_Users',$QUALIFIED_MODULE)}</strong></th>	
						<th><strong>{vtranslate('Status',$QUALIFIED_MODULE)}</strong></th>
						<th><strong>{vtranslate('LBL_ACTION', $QUALIFIED_MODULE)}</strong></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{if $LIST_USERS}
						{foreach from=$LIST_USERS item=USER}
							<tr data-id="{$USER['id']}">
								<td>{$USER['first_name']}</td>
								<td>{$USER['last_name']}</td>
								<td>{$USER['email']}</td>
								<td>{$USER['user_name']}</td>
								<td>{$USER['pass']}</td>
								<td>{$USER['userModel']->getName()}</td>
								<td>
									{if $USER['status'] eq 1}
										{vtranslate('LBL_ACTIVE',$QUALIFIED_MODULE)}
									{else}
										{vtranslate('LBL_INACTIVE',$QUALIFIED_MODULE)}
									{/if}
								</td>
								<td>
									{foreach from=$USER['action'] item=USER_ACTION}
										{vtranslate($USER_ACTION['label'],$QUALIFIED_MODULE)}<br>
									{/foreach}
								</td>
								<td>
									<div class="action">
										<div class="pull-right">
											<button class="btn btn-primary btn-xs">
												<span class="glyphicon glyphicon-pencil cursorPointer edit"></span>
											</button>
											<button class="btn btn-danger btn-xs marginLeft5">
												<span class="glyphicon glyphicon-trash cursorPointer remove"></span>
											</button>
										</div>
									</div>
								</td>
							</tr>
						{/foreach}
					{/if}
				</tbody>
			</table>
		</div>
	</div>
{/strip}
