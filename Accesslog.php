<?php

class Accesslog{

	private $access_log;
	private $log_array;

	public function __construct(){
		$this->access_log = file_get_contents('../../../../../log/apache2/access.log');
		$this->log_array = explode("\n", $this->access_log);
	}

	public function processed(){
		$epoints = array();
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
			if($method && !in_array($endpoint, $epoints)){
				array_push($epoints, $endpoint);
				$hits = $this->count_endpoint_hits($endpoint);
				$response[] = 'endpoint_requests_total{method="'.$method.'",endpoint="'.$endpoint.'",status_code="'.$status.'"} '.$hits;
			}
		}

		return array_reverse($response);
	} 

	private function count_endpoint_hits($endurl){
		$count = 0;
		foreach ($this->log_array as $key => $value) {
			$temp = explode('"', $value);
			$endpoint = explode(" ", $temp[1])[1];
			if($endpoint == $endurl){
				$count++;
			}
		}
		return $count;
	}
}


