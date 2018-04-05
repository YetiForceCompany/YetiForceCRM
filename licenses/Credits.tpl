{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*} 
<div class="License-Credits js-table-container" data-js="container">
	<table class="table table-bordered dataTableWithRecords">
		<thead>
		<th >
			{\App\Language::translate('LBL_NAME')}
		</th>
		<th >
			{\App\Language::translate('LBL_VERSION')}
		</th>
		<th >
			{\App\Language::translate('LBL_LICENSE')}
		</th>
		</thead>
		<tbody>
		{foreach from=$LIBRARIES item=ITEM}
			<tr>
				<td>
					{$ITEM['name']}
				</td>
				<td>
					{$ITEM['version']}
				</td>
				<td>
					{$ITEM['license']}
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
</div>