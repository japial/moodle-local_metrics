<?php

/**
* Global Search version details.
*
* @package   local_metrics
* @copyright 2020 mPower Social <pial@mpower-social.com>
* @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
* @author    Jahangir Pial
*/

$disabled = [];
if (stripos(PHP_OS, 'linux') !== false) {
	if (empty(sys_getloadavg())) {
		$disabled['cpu'] = true;
	}
	if (shell_exec("free") == "") {
		$disabled['memory'] = true;
	}
}

/**
 * Return server's cpu usage
 *
 * @return float cpu usage
 */

function get_cpu_usage() {
	$load = 0;
	if (stripos(PHP_OS, 'win') !== false) {
		$load = shell_exec("wmic cpu get loadpercentage");
		$load = (int)filter_var($load, FILTER_SANITIZE_NUMBER_INT);
	} else if (stripos(PHP_OS, 'linux') !== false) {
		if (isset($disabled['cpu'])) {
			return $load;
		}
		$loads    = sys_getloadavg();
		$corenums = trim(shell_exec("grep -P '^processor' /proc/cpuinfo|wc -l"));
		if (!empty($corenums)) {
			$load     = round($loads[0] / ($corenums + 1) * 100, 2);
		} else {
			$load = round($loads[0] * 100, 2);
		}
	}
	if ($load > 100) {
		return 100;
	}
	return $load;
}

/**
* Return server's memory usage
*
* @return float memory usage
*/
function get_memory_usage() {
	$usage = 0;
	if (stripos(PHP_OS, 'win') !== false) {
		$max   = shell_exec("wmic OS get TotalVisibleMemorySize");
		$max   = (int)filter_var($max, FILTER_SANITIZE_NUMBER_INT);
		$free  = shell_exec("wmic OS get FreePhysicalMemory");
		$free  = (int)filter_var($free, FILTER_SANITIZE_NUMBER_INT);
		$usage = round(($max - $free) / $max * 100, 2);
	} else if (stripos(PHP_OS, 'linux') !== false) {
		if (isset($disabled['memory'])) {
			return $usage;
		}
		$memory = shell_exec('free -k');
		if ($memory != "") {
			$memory    = explode("\n", $memory);
			$memory    = explode(' ', preg_replace('!\s+!', ' ', $memory[1]));
			$maxmemory = round($memory[1] / 1024 / 1024, 2);
			$usage     = round($memory[2] / 1024 / 1024, 2);
			$usage     = round($usage / $maxmemory * 100, 2);
		}
	}
	if ($usage > 100) {
		return 100;
	}
	return $usage;
}

/**
 * Return server's max memory
 *
 * @return float memory
 */
function get_total_memory() {
	$max = 0;
	if (stripos(PHP_OS, 'win') !== false) {
		$max = shell_exec("wmic OS get TotalVisibleMemorySize");
		$max = (int)filter_var($max, FILTER_SANITIZE_NUMBER_INT);
	} else if (stripos(PHP_OS, 'linux') !== false) {
		if (isset($disabled['memory'])) {
			return $max;
		}
		$memory = shell_exec('free');
		if ($memory != "") {
			$memory = explode("\n", $memory)[1];
			$memory = array_merge(array_filter(explode(" ", $memory)));
			$max    = $memory[1];
		}
	}
	return $max / 1024 / 1024;
}

/**
 * Return server's storage usage
 *
 * @return float storage usage
 */
function get_storage_usage() {
	global $CFG;
	$usage = 0;
	if (stripos(PHP_OS, 'linux') !== false) {
		$storage = shell_exec('df -m ' . $CFG->dirroot);
		if ($storage != "") {
			$storage = explode("\n", $storage)[1];
			$storage = array_merge(array_filter(explode(" ", $storage)));
			$usage   = round($storage[2] / $storage[1] * 100, 2);
		} else {
			$free  = disk_free_space($CFG->dirroot);
			$all   = disk_total_space($CFG->dirroot);
			$usage = round(($all - $free) / $all * 100, 2);
		}
	} else if (stripos(PHP_OS, 'win') !== false) {
		$free  = disk_free_space($CFG->dirroot);
		$all   = disk_total_space($CFG->dirroot);
		$usage = round(($all - $free) / $all * 100, 2);
	}
	if ($usage > 100) {
		return 100;
	}
	return $usage;
}

/**
 * Return server's storage
 *
 * @return float storage
 */
function get_total_storage() {
	global $CFG;
	$storage = disk_total_space($CFG->dirroot) / 1024 / 1024 / 1024;
	return $storage;
}

/**
 * Return server's details
 *
 * @return array details
 */

function server_details(){
	$cpu_usage = 'USAGE '.number_format(get_cpu_usage(), 2, '.', '').' %';
	$total_memory = number_format(get_total_memory(), 2, '.', '').' GB';
	$memory_usage = number_format(get_memory_usage(), 2, '.', '').' %';
	$total_storage = number_format(get_total_storage(), 2, '.', '').' GB';
	$storage_usage = number_format(get_storage_usage(), 2, '.', '').' %';

	$response = array();

	$response['CPU'] = $cpu_usage;
	$response['MEMORY'] = array('TOTAL' => $total_memory, 'USAGE' => $memory_usage);
	$response['STORAGE'] = array('TOTAL' => $total_storage, 'USAGE' => $storage_usage);

	return $response;
}


