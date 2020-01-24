{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=OPS value=\App\Language::translate('LBL_PER_SECOND', $QUALIFIED_MODULE)}
	<div class="tpl-Settings-ConfReport-Speed modal-body">
		<div class="card">
			<h5 class="card-header">{\App\Language::translate('LBL_CPU', $QUALIFIED_MODULE)}</h5>
			<div class="card-body">
				{if isset($BENCHMARKS['cpu'])}
					math: {App\Fields\Integer::formatToDisplay($BENCHMARKS['cpu']['math']['time'])}{$OPS} ({App\Fields\Integer::formatToDisplay($BENCHMARKS['cpu']['math']['operations'])})<br>
					hash: {App\Fields\Integer::formatToDisplay($BENCHMARKS['cpu']['hash']['time'])}{$OPS} ({App\Fields\Integer::formatToDisplay($BENCHMARKS['cpu']['hash']['operations'])})<br>
					string: {App\Fields\Integer::formatToDisplay($BENCHMARKS['cpu']['string']['time'])}{$OPS} ({App\Fields\Integer::formatToDisplay($BENCHMARKS['cpu']['string']['operations'])})
				{/if}
			</div>
		</div>
		<div class="card mt-2">
			<h5 class="card-header">{\App\Language::translate('LBL_RAM', $QUALIFIED_MODULE)}</h5>
			<div class="card-body">
				{if isset($BENCHMARKS['ram'])}
					read: {App\Fields\Integer::formatToDisplay($BENCHMARKS['ram']['read']['time'])}{$OPS} ({App\Fields\Integer::formatToDisplay($BENCHMARKS['ram']['read']['operations'])})<br>
					write: {App\Fields\Integer::formatToDisplay($BENCHMARKS['ram']['write']['time'])}{$OPS} ({App\Fields\Integer::formatToDisplay($BENCHMARKS['ram']['write']['operations'])})
				{/if}
			</div>
		</div>
		<div class="card mt-2">
			<h5 class="card-header">Hard drive</h5>
			<div class="card-body">
				{if isset($BENCHMARKS['hardDrive'])}
					<table class="table">
					<thead>
						<tr>
							<th scope="col">File size</th>
							<th scope="col">{\App\Language::translate('LBL_READ_TEST', $QUALIFIED_MODULE)}</th>
							<th scope="col">{\App\Language::translate('LBL_WRITE_TEST', $QUALIFIED_MODULE)}</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td scope="row">1 KB</td>
							<td>
								{App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['read'][1]['time'])}{$OPS}
								({App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['read'][1]['operations'])})
							</td>
							<td>
								{App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['write'][1]['time'])}{$OPS}
								({App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['write'][1]['operations'])})
							</td>
						</tr>
						<tr>
							<td scope="row">10 KB</td>
							<td>
								{App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['read'][10]['time'])}{$OPS}
								({App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['read'][10]['operations'])})
							</td>
							<td>
								{App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['write'][10]['time'])}{$OPS}
								({App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['write'][10]['operations'])})
							</td>
						</tr>
						<tr>
							<td scope="row">100 KB</td>
							<td>
								{App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['read'][100]['time'])}{$OPS}
								({App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['read'][100]['operations'])})
							</td>
							<td>
								{App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['write'][100]['time'])}{$OPS}
								({App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['write'][100]['operations'])})
							</td>
						</tr>
					</tbody>
					</table>
				{/if}
			</div>
		</div>
		{* <hr>
		<h5>{\App\Language::translate('LBL_READ_TEST', $QUALIFIED_MODULE)}
			: {$TESTS['FilesRead']}{\App\Language::translate('LBL_PER_SECOND', $QUALIFIED_MODULE)}</h5>
		<h5>{\App\Language::translate('LBL_WRITE_TEST', $QUALIFIED_MODULE)}
			: {$TESTS['FilesWrite']}{\App\Language::translate('LBL_PER_SECOND', $QUALIFIED_MODULE)}</h5>
		<h5>{\App\Language::translate('LBL_CPU', $QUALIFIED_MODULE)}
			: {$TESTS['CPU']}{\App\Language::translate('LBL_PER_SECOND', $QUALIFIED_MODULE)}</h5>
		<h5>{\App\Language::translate('LBL_RAM', $QUALIFIED_MODULE)}
			: {$TESTS['RAM']}{\App\Language::translate('LBL_PER_SECOND', $QUALIFIED_MODULE)}</h5>
		<h5>{\App\Language::translate('LBL_DB', $QUALIFIED_MODULE)}
		: {$TESTS['DB']}{\App\Language::translate('LBL_PER_SECOND', $QUALIFIED_MODULE)}</h5> *}
	</div>
{/strip}
