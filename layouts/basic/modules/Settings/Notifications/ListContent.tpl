{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<table class="table table-bordered themeTableColor">
		<thead>
			<tr>
				<th>
					{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}
				</th>

			</tr>
		</thead>
		<tbody>
			{foreach from=$LIST_CONTENT item=RECORD}
				<tr class="opacity" data-id="{$RECORD->getId()}">
					<td>
						{vtranslate($RECORD->getName(), $QUALIFIED_MODULE)}
						{if $RECORD->get('presence') == 1}
							<div class="pull-right actions">
								<a class="edit cursorPointer" data-url="{$RECORD->getEditUrl()}">
									<span class="glyphicon glyphicon-pencil alignBottom" title="Edycja"></span>
								</a>
								<a class="remove cursorPointer"><span title="" class="glyphicon glyphicon-trash alignBottom"></span>
								</a>
							</div>
						{/if}
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{/strip}
