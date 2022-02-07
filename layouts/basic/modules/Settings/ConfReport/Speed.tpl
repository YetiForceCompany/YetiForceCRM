{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-ConfReport-Speed -->
	{assign var=OPS value=\App\Language::translate('LBL_PER_SECOND', $QUALIFIED_MODULE)}
	<div class="modal-body js-modal-content" data-js="click">
		{if isset($BENCHMARKS['cpu'])}
			<div class="card">
				<h5 class="card-header"><span class="fas fa-microchip mr-2"></span>{\App\Language::translate('LBL_CPU', $QUALIFIED_MODULE)}</h5>
				<div class="card-body p-2">
					{\App\Language::translate('LBL_BENCHMARK_CPU_MATH', $QUALIFIED_MODULE)}: {App\Fields\Integer::formatToDisplay($BENCHMARKS['cpu']['math']['time'])}{$OPS} ({App\Fields\Integer::formatToDisplay($BENCHMARKS['cpu']['math']['operations'])})<br>
					{\App\Language::translate('LBL_BENCHMARK_CPU_HASH', $QUALIFIED_MODULE)}: {App\Fields\Integer::formatToDisplay($BENCHMARKS['cpu']['hash']['time'])}{$OPS} ({App\Fields\Integer::formatToDisplay($BENCHMARKS['cpu']['hash']['operations'])})<br>
					{\App\Language::translate('LBL_BENCHMARK_CPU_STRING', $QUALIFIED_MODULE)}: {App\Fields\Integer::formatToDisplay($BENCHMARKS['cpu']['string']['time'])}{$OPS} ({App\Fields\Integer::formatToDisplay($BENCHMARKS['cpu']['string']['operations'])})
				</div>
			</div>
		{/if}
		{if isset($BENCHMARKS['ram'])}
			<div class="card mt-2">
				<h5 class="card-header"><span class="fas fa-memory mr-2"></span>{\App\Language::translate('LBL_RAM', $QUALIFIED_MODULE)}</h5>
				<div class="card-body p-2">
					{\App\Language::translate('LBL_BENCHMARK_RAM_READ', $QUALIFIED_MODULE)}: {App\Fields\Integer::formatToDisplay($BENCHMARKS['ram']['read']['time'])}{$OPS} ({App\Fields\Integer::formatToDisplay($BENCHMARKS['ram']['read']['operations'])})<br>
					{\App\Language::translate('LBL_BENCHMARK_RAM_WRITE', $QUALIFIED_MODULE)}: {App\Fields\Integer::formatToDisplay($BENCHMARKS['ram']['write']['time'])}{$OPS} ({App\Fields\Integer::formatToDisplay($BENCHMARKS['ram']['write']['operations'])})
				</div>
			</div>
		{/if}
		{if isset($BENCHMARKS['hardDrive'])}
			<div class="card mt-2">
				<h5 class="card-header"><span class="fas fa-hdd mr-2"></span>Hard drive</h5>
				<div class="card-body p-2">
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
									{App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['read'][1]['time'])}{$OPS} ({App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['read'][1]['operations'])})
								</td>
								<td>
									{App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['write'][1]['time'])}{$OPS} ({App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['write'][1]['operations'])})
								</td>
							</tr>
							<tr>
								<td scope="row">10 KB</td>
								<td>
									{App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['read'][10]['time'])}{$OPS} ({App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['read'][10]['operations'])})
								</td>
								<td>
									{App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['write'][10]['time'])}{$OPS} ({App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['write'][10]['operations'])})
								</td>
							</tr>
							<tr>
								<td scope="row">100 KB</td>
								<td>
									{App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['read'][100]['time'])}{$OPS} ({App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['read'][100]['operations'])})
								</td>
								<td>
									{App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['write'][100]['time'])}{$OPS} ({App\Fields\Integer::formatToDisplay($BENCHMARKS['hardDrive']['write'][100]['operations'])})
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		{/if}
		{if isset($BENCHMARKS['db'])}
			<div class="card mt-2">
				<h5 class="card-header"><span class="fas fa-database mr-2"></span>{\App\Language::translate('LBL_DB', $QUALIFIED_MODULE)}</h5>
				<div class="card-body p-2">
					<table class="table">
						<thead>
							<tr>
								<th scope="col">Type</th>
								<th scope="col">Time</th>
								<th scope="col">Operations</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td scope="row">Select</td>
								<td>
									{App\Fields\Integer::formatToDisplay($BENCHMARKS['db']['select']['time'])}{$OPS}
								</td>
								<td>
									{App\Fields\Integer::formatToDisplay($BENCHMARKS['db']['select']['operations'])}
								</td>
							</tr>
							<tr>
								<td scope="row">Update</td>
								<td>
									{App\Fields\Integer::formatToDisplay($BENCHMARKS['db']['update']['time'])}{$OPS}
								</td>
								<td>
									{App\Fields\Integer::formatToDisplay($BENCHMARKS['db']['update']['operations'])}
								</td>
							</tr>
							<tr>
								<td scope="row">Insert</td>
								<td>
									{App\Fields\Integer::formatToDisplay($BENCHMARKS['db']['insert']['time'])}{$OPS}
								</td>
								<td>
									{App\Fields\Integer::formatToDisplay($BENCHMARKS['db']['insert']['operations'])}
								</td>
							</tr>
							<tr>
								<td scope="row">Delete</td>
								<td>
									{App\Fields\Integer::formatToDisplay($BENCHMARKS['db']['delete']['time'])}{$OPS}
								</td>
								<td>
									{App\Fields\Integer::formatToDisplay($BENCHMARKS['db']['delete']['operations'])}
								</td>
							</tr>
							<tr>
								<td scope="row">Benchmark</td>
								<td>
									{App\Fields\Integer::formatToDisplay($BENCHMARKS['db']['benchmark']['time'])}{$OPS}
								</td>
								<td>
									{App\Fields\Integer::formatToDisplay($BENCHMARKS['db']['benchmark']['operations'])}
								</td>
							</tr>
						</tbody>
					</table>
					<div class="accordion" id="accordionExample">
						<div class="card">
							<div class="card-header" id="createTableH">
								<h2 class="mb-0">
									<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#createTable" aria-expanded="false" aria-controls="createTable">
										Create table
									</button>
								</h2>
							</div>
							<div id="createTable" class="collapse" aria-labelledby="createTableH" data-parent="#accordionExample">
								<div class="card-body p-2">
									<table class="table">
										<thead>
											<tr>
												<th scope="col">Status</th>
												<th scope="col">Duration</th>
											</tr>
										</thead>
										<tbody>
											{foreach from=$BENCHMARKS['db']['createTable']['profile'] item=ROW}
												<tr>
													<td>{$ROW['Status']}</td>
													<td>{$ROW['Duration']}</td>
												</tr>
											{/foreach}
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="selectH">
								<h2 class="mb-0">
									<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#select" aria-expanded="false" aria-controls="select">
										Select
									</button>
								</h2>
							</div>
							<div id="select" class="collapse" aria-labelledby="selectH" data-parent="#accordionExample">
								<div class="card-body p-2">
									<table class="table">
										<thead>
											<tr>
												<th scope="col">Status</th>
												<th scope="col">Duration</th>
											</tr>
										</thead>
										<tbody>
											{foreach from=$BENCHMARKS['db']['select']['profile'] item=ROW}
												<tr>
													<td>{$ROW['Status']}</td>
													<td>{$ROW['Duration']}</td>
												</tr>
											{/foreach}
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="insertH">
								<h2 class="mb-0">
									<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#insert" aria-expanded="false" aria-controls="insert">
										Insert
									</button>
								</h2>
							</div>
							<div id="insert" class="collapse" aria-labelledby="insertH" data-parent="#accordionExample">
								<div class="card-body p-2">
									<table class="table">
										<thead>
											<tr>
												<th scope="col">Status</th>
												<th scope="col">Duration</th>
											</tr>
										</thead>
										<tbody>
											{foreach from=$BENCHMARKS['db']['insert']['profile'] item=ROW}
												<tr>
													<td>{$ROW['Status']}</td>
													<td>{$ROW['Duration']}</td>
												</tr>
											{/foreach}
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="updateH">
								<h2 class="mb-0">
									<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#update" aria-expanded="false" aria-controls="update">
										update
									</button>
								</h2>
							</div>
							<div id="update" class="collapse" aria-labelledby="updateH" data-parent="#accordionExample">
								<div class="card-body p-2">
									<table class="table">
										<thead>
											<tr>
												<th scope="col">Status</th>
												<th scope="col">Duration</th>
											</tr>
										</thead>
										<tbody>
											{foreach from=$BENCHMARKS['db']['update']['profile'] item=ROW}
												<tr>
													<td>{$ROW['Status']}</td>
													<td>{$ROW['Duration']}</td>
												</tr>
											{/foreach}
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="deleteH">
								<h2 class="mb-0">
									<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#delete" aria-expanded="false" aria-controls="delete">
										delete
									</button>
								</h2>
							</div>
							<div id="delete" class="collapse" aria-labelledby="deleteH" data-parent="#accordionExample">
								<div class="card-body p-2">
									<table class="table">
										<thead>
											<tr>
												<th scope="col">Status</th>
												<th scope="col">Duration</th>
											</tr>
										</thead>
										<tbody>
											{foreach from=$BENCHMARKS['db']['delete']['profile'] item=ROW}
												<tr>
													<td>{$ROW['Status']}</td>
													<td>{$ROW['Duration']}</td>
												</tr>
											{/foreach}
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="benchmarkH">
								<h2 class="mb-0">
									<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#benchmark" aria-expanded="false" aria-controls="benchmark">
										Create table
									</button>
								</h2>
							</div>
							<div id="benchmark" class="collapse" aria-labelledby="benchmarkH" data-parent="#accordionExample">
								<div class="card-body p-2">
									<table class="table">
										<thead>
											<tr>
												<th scope="col">Status</th>
												<th scope="col">Duration</th>
											</tr>
										</thead>
										<tbody>
											{foreach from=$BENCHMARKS['db']['benchmark']['profile'] item=ROW}
												<tr>
													<td>{$ROW['Status']}</td>
													<td>{$ROW['Duration']}</td>
												</tr>
											{/foreach}
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="dropTableH">
								<h2 class="mb-0">
									<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#dropTable" aria-expanded="false" aria-controls="dropTable">
										Create table
									</button>
								</h2>
							</div>
							<div id="dropTable" class="collapse" aria-labelledby="dropTableH" data-parent="#accordionExample">
								<div class="card-body p-2">
									<table class="table">
										<thead>
											<tr>
												<th scope="col">Status</th>
												<th scope="col">Duration</th>
											</tr>
										</thead>
										<tbody>
											{foreach from=$BENCHMARKS['db']['dropTable']['profile'] item=ROW}
												<tr>
													<td>{$ROW['Status']}</td>
													<td>{$ROW['Duration']}</td>
												</tr>
											{/foreach}
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		{/if}
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
	<div class="modal-footer">
		<button class="js-download-img btn btn-success js-download-html" type="button" data-html=".js-modal-content" data-file-name="SpeedTest" data-js="download container">
			<span class="fas fa-download mr-1"></span>{\App\Language::translate('LBL_DOWNLOAD_CONFIG', $QUALIFIED_MODULE)}
		</button>
		<button class="btn btn-primary" type="reset" data-dismiss="modal">
			<span class="fas fa-times mr-1"></span>
			{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}
		</button>
	</div>
	<!-- /tpl-Settings-ConfReport-Speed -->
{/strip}
