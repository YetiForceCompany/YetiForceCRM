<?php
class CallHistory{
    public $restler;
	
    function post($type = '', $authorization = '', $data = '')
    {
		$test = array('restStatus' => 'true','type' => $type,'authorization' => $authorization, 'data' => $data);

		
        return $test;
    }
}