<?php

class Accesslog{

	private $access_log;
	private $log_array;

	public function __construct(){
		$this->access_log = file_get_contents('../../../../../log/apache2/access.log');
		$this->log_array = explode("\n", $this->access_log);
	}

	public function processed(){
		$response = array();
		foreach ($this->log_array as $key => $value) {
			$temp = explode('"', $value);
			$ip = explode("- -", $temp[0])[0];
			$method = explode(" ", $temp[1])[0];
			$endpoint = explode(" ", $temp[1])[1];
			if(strlen($endpoint) > 100){
				$endpoint = substr(explode(" ", $temp[1])[1], 0, 100).'...';
			}
			$status = explode(" ",trim($temp[2]))[0];
			if($method){
				$response[] = 'endpoint_requests_total{method="'.$method.'",endpoint="'.$endpoint.'",status_code="'.$status.'"}"';
			}
		}

		return array_reverse($response);
	}
}


