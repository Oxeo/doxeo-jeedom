<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

require_once __DIR__  . '/../../../../core/php/core.inc.php';

if (!jeedom::apiAccess(init('apikey'), 'doxeo') && !jeedom::apiAccess(init('apikey'), 'core')) {
	echo __('Vous n\'etes pas autorisé à effectuer cette action', __FILE__);
	die();
}

if (isset($_GET['test'])) {
	echo 'OK';
	die();
}

$results = json_decode(file_get_contents("php://input"), true);
if (!is_array($results)) {
    echo 'NO JSON';
	die();
}

if (isset($results['gateway'])) {
	$gateway = $results['gateway'];
	$nodeid = $results['sender'];
	$sensor = $results['sensor'];
	$type = $results['type'];
	$value = $results['payload'];
	$messagetype = $results['messagetype'];

	switch ($messagetype) {
	case 'saveValue' : mySensors::saveValue($gateway, $nodeid,$sensor,$type, $value); break;//saveValue($gateway, $nodeid,$sensor,$type, $value)
	case 'saveSketchName' : mySensors::saveSketchNameEvent($gateway, $nodeid, $value); break;//saveSketchVersion($gateway, $nodeid, $value)
	case 'saveSketchVersion' : mySensors::saveSketchVersion($gateway, $nodeid, $value); break;//saveSketchVersion($gateway, $nodeid, $value)
	case 'saveLibVersion' : mySensors::saveLibVersion($gateway, $nodeid, $value); break;//saveLibVersion($gateway, $nodeid, $value)
	case 'saveSensor' : mySensors::saveSensor($gateway, $nodeid, $sensor, $type); break;//saveSensor($gateway, $nodeid, $sensor, $value)
	case 'saveBatteryLevel' : mySensors::saveBatteryLevel($gateway, $nodeid, $value); break; // saveBatteryLevel($gateway, $nodeid, $value)
	case 'saveGateway' : mySensors::saveGateway($gateway, $value); break;//saveGateway($gateway, $value)
	case 'getValue' : mySensors::getValue($gateway,$nodeid,$sensor,$type); break;//getValue($gateway,$nodeid,$sensor,$type)
	case 'getNextSensorId' : mySensors::getNextSensorId($gateway); break;//getNextSensorId($gateway)
	}

	echo 'success';
	die();
}

if (isset($results['type'])) {
	$logicalId = $results['type'] . "_" . $results['id'];
	$eqLogic = doxeo::byLogicalId($logicalId, 'doxeo');
	if (is_object($eqLogic)) {
		if ($results['subtype'] == 'battery') {
			log::add('doxeo', 'info', $results['type'] . ';battery;' . $results['value'] );
			$eqLogic->batteryStatus($result['value']);
		} else {
			foreach ($eqLogic->getCmd('info') as $cmd) {
				log::add('doxeo', 'info', $results['type'] . ';' . $results['id'] . ';' . $results['value'] );

				if ($cmd->getConfiguration("instance", 1) != 1 && $cmd->getConfiguration("instance") != $results['subtype']) {
					continue;
				}

				if ($cmd->getSubType() == 'binary') {
					if ($results['value'] == "started") {
						$results['value'] = 1;
					}

					if ($results['value'] == "close") {
						$results['value'] = 1;
					}

					if ($results['value'] == "event") {
						$results['value'] = 1;
					}
				}

				$cmd->event($results['value']);
			}
		}
	}

	echo 'success';
	die();
}

if (isset($results['message'])) {
	log::add('doxeo', 'error', $results['message']);

	echo 'success';
	die();
}
