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

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class doxeo extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */
    
    public static function callDoxeoMonitor($_url) {
        $url = 'http://127.0.0.1:' . config::byKey('port_server', 'doxeo', 8080) . '/' . $_url;
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
		));
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			$curl_error = curl_error($ch);
			curl_close($ch);
			throw new Exception(__('Echec de la requête http : ', __FILE__) . $url . ' Curl error : ' . $curl_error, 404);
		}
		curl_close($ch);
		return (is_json($result)) ? json_decode($result, true) : $result;
	}
    
    public static function deamon_info() {
		$return = array();
		$return['state'] = 'ok';
        $return['launchable'] = 'ok';
        
        $url = 'http://127.0.0.1:' . config::byKey('port_server', 'doxeo', 8080) . '/system.js';
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
		));
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
            $return['state'] = 'nok';
		} else if (!is_json($result)) {
            $json = json_decode($result, true);
            if (!$json['success']) {
                $return['state'] = 'nok';
            }
        }
		curl_close($ch);
        
        if ($return['state'] == 'nok') {
            $daemonPath = config::byKey('daemon_path', 'doxeo', './doxeo');
            
            if (($daemonPath != './doxeo' || $daemonPath != './doxeo-monitor') 
                    && !file_exists($daemonPath)) {
                $return['launchable'] = 'nok';
                $return['launchable_message'] = 'doxeo daemon is missing at ' + $daemonPath;
            }
        }
		
		return $return;
	}
    
    public static function deamon_start($_debug = false) {
        $callback = network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/doxeo/core/php/jeeDoxeo.php';
        $daemonPath = config::byKey('daemon_path', 'doxeo', './doxeo');
        $daemonViewPath = config::byKey('daemon_view_path', 'doxeo', '');

        $cmd = $daemonPath . ' ';
        if ($daemonViewPath != '') {
            $cmd .= ' --path ' . $daemonViewPath;
        }
		//$cmd .= ' --callback ' . $callback;
		//$cmd .= ' --apikey ' . jeedom::getApiKey('doxeo');

        log::add('doxeo', 'info', 'Starting doxeo daemon: ' . $cmd);
        exec($cmd . ' >> ' . log::getPathToLog('doxeo') . ' 2>&1 &');
        
        $i = 0;
        while ($i < 10) {
            $deamon_info = self::deamon_info();
            if ($deamon_info['state'] == 'ok') {
				break;
            }
			sleep(1);
			$i++;
		}
		if ($i >= 10) {
			log::add('doxeo', 'error', 'Unable to start doxeo daemon ' .$callback, 'unableStartDeamon');
			return false;
		}
        
		message::removeAll('doxeo', 'unableStartDeamon');
		log::add('doxeo', 'info', 'Doxeo daemon started');
        return true;
	}
    
    public static function deamon_stop() {
        doxeo::callDoxeoMonitor('stop');
        
        $i = 0;
        while ($i < 10) {
            $deamon_info = self::deamon_info();
            if ($deamon_info['state'] == 'nok') {
				break;
            }
			sleep(1);
			$i++;
		}
		if ($i >= 10) {
			log::add('doxeo', 'error', 'Unable to stop doxeo daemon ' .$callback, 'unableStopDeamon');
			return false;
        }
        
        message::removeAll('doxeo', 'unableStopDeamon');
        log::add('doxeo', 'info', 'Daemon doxeo stopped');
        return true;
	}

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */



    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
        
    }

    public function postInsert() {
        
    }

    public function preSave() {
        
    }

    public function postSave() {
        
    }

    public function preUpdate() {
        
    }

    public function postUpdate() {
        
    }

    public function preRemove() {
        
    }

    public function postRemove() {
        
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class doxeoCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {
        
        if ($this->getType() != 'action') {
			return;
		}
        
        $id = $this->getEqLogic()->getLogicalId();
        $command = $this->getConfiguration('command');
        
        if ($id == "gateway") {
            $title = urlencode(trim($_options['title']));
            $message = urlencode(trim($_options['message']));
            if ($command == "sensor") {
                $request = 'sensor/set_value.js?id=' . $title . '&value=' . $message;
            } else if ($command == "switch") {
                $request = 'switch/update_switch_status.js?id=' . $title . '&status=' . $message;
            }
        } else if ($id == "fcm") {
            $title = urlencode(trim($_options['title']));
            $message = urlencode(trim($_options['message']));
            if ($this->getName() == "info") {
                $request = 'fcm.js?type=INFO&title=' . $title . '&message=' . $message;
            } else if ($this->getName() == "warning") {
                $request = 'fcm.js?type=WARNING&title=' . $title . '&message=' . $message;
            } else if ($this->getName() == "alert") {
                $request = 'fcm.js?type=ALERT&title=' . $title . '&message=' . $message;
            }
        } else if ($id == "sms") {
            $message = urlencode(trim($_options['message']));
            $number = $command;
            $request = 'sms.js?number=' . $number . '&message=' . $message;
        } else if ($id == "execute_cmd") {
            $cmd = urlencode($command);
            $request = 'script/execute_cmd.js?cmd=' . $cmd;
        } else if (strpos($id, 'switch_') == 0) {
            $request = 'switch/change_switch_status?id=' . str_replace("switch_", "", $id) . '&status=' . $command;
        } else {
            log::add('doxeo', 'error', 'unknown logical id ' . $id, 'unknownId');
        }
        log::add('doxeo', 'info', 'send request: ' . $request);
        
        doxeo::callDoxeoMonitor($request);
    }

    private function startsWith($haystack, $needle)
    {
         $length = strlen($needle);
         return (substr($haystack, 0, $length) === $needle);
    }

    /*     * **********************Getteur Setteur*************************** */
}


