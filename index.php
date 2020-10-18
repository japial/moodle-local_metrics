<?php

/**
 * Global Search version details.
 *
 * @package   local_metrics
 * @copyright 2020 mPower Social <pial@mpower-social.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Jahangir Pial
 */


include 'Accesslog.php';
$log = new Accesslog();

include 'Serverusage.php';
$server_details = server_details();
echo "# Server metrics";
echo "<br/>";
foreach ($server_details as $k => $val) {
	if(is_array($val)){
		echo $k;
		echo json_encode($val);
		echo "<br/>";
	}else{
		echo $k.": ".$val;
		echo "<br/>";
	}
};

echo "------------------------------------------";
echo "<br/>";

echo "# Application metrics";
echo "<br/>";
echo "# HELP endpoint_requests_total Total API requests for endpoints";
echo "<br/>";
echo "#TYPE endpoint_requests_total counter";
echo "<br/>";
foreach ($log->processed() as $key => $value) {
	echo $value;
	echo "<br/>";
};



