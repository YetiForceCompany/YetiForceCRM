{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="col-xs-12 paddingLRZero">
		<div class="table-responsive">
			<table class="table table-bordered table-condensed">
				<thead>
					<tr>
						<th><strong>{vtranslate('LBL_LOGIN',$QUALIFIED_MODULE)}</strong></th>
						<th><strong>{vtranslate('Single_Users',$QUALIFIED_MODULE)}</strong></th>
						<th><strong>{vtranslate('SINGLE_Emails',$QUALIFIED_MODULE)}</strong></th>
						<th><strong>{vtranslate('Status',$QUALIFIED_MODULE)}</strong></th>
						<th><strong>{vtranslate('LBL_KEY',$QUALIFIED_MODULE)}</strong></th>
					
					</tr>
				</thead>
				<tbody>
					{if $LIST_USERS}
						{foreach from=$LIST_USERS item=USER}
							<tr data-id="{$USER['id']}">
								<td>{$USER['user_name']}</td>
								<td>{$USER['userModel']->getName()}</td>
								<td>{$USER['userModel']->get('email1')}</td>
								<td>{vtranslate($USER['userModel']->get('status'),$QUALIFIED_MODULE)}</td>
								<td>
									<div class="action">
										{$USER['key']}
										<div class="pull-right">
											<span class="glyphicon glyphicon-pencil cursorPointer edit"></span>
											<span class="glyphicon glyphicon-remove cursorPointer remove"></span>
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
