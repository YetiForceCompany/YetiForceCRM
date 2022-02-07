{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<table class="table table-bordered themeTableColor">
		<thead>
			<tr>
				<th>
					{\App\Language::translate('LBL_NAME', $QUALIFIED_MODULE)}
				</th>

			</tr>
		</thead>
		<tbody>
			{foreach from=$LIST_CONTENT item=RECORD}
				<tr class="opacity" data-id="{$RECORD->getId()}">
					<td>
						{\App\Language::translate($RECORD->getName(), $QUALIFIED_MODULE)}
						{if $RECORD->get('presence') == 1}
							<div class="float-right actions">
								<a class="edit u-cursor-pointer" data-url="{$RECORD->getEditUrl()}">
									<span class="yfi yfi-full-editing-view alignBottom" title="Edycja"></span>
								</a>
								<a class="remove u-cursor-pointer"><span title="" class="fas fa-trash-alt alignBottom"></span>
								</a>
							</div>
						{/if}
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{/strip}
