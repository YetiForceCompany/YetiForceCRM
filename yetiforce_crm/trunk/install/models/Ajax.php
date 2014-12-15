<?php
class Install_Ajax_Model{
    public function process(Vtiger_Request $request) {
        $mode = $request->getMode();
        if (!empty($mode) && method_exists('Install_Ajax_Model', $mode)) {
            return $this->$mode($request);
        }
    }
    public function loadProgressBar(Vtiger_Request $request) {
		require_once('install/models/progressbar.php');
		$logsUrl = fopen('install/models/logs.txt','r');
		$logs = '';
		while(!feof($logsUrl)){
			$logs .= fgets($logsUrl).'<br />';
		}
		$result = array('step' => $progress, 'info' => $logs);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
    }
    public function execute(Vtiger_Request $request) {
		$system = $request->get('system');
		$userName = $request->get('user');
		$result = Install_InitSchema_Model::executeMigrationSchema($system, $userName);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
    }
}