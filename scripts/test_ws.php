<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2014 by the Plugin Monitoring for GLPI Development Team.

   https://forge.indepnet.net/projects/monitoring/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Monitoring project.

   Plugin Monitoring for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Monitoring for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Monitoring. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Monitoring for GLPI
   @author    Frédéric MOHIER
   @co-author 
   @comment   Test module for Web services
   @copyright Copyright (c) 2011-2014 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2011
 
   ------------------------------------------------------------------------
 */

/*
* SETTINGS
*/
$xmlrpc = false;
chdir(dirname($_SERVER["SCRIPT_FILENAME"]));
chdir("../../..");
if ($xmlrpc) {
   if (!extension_loaded("xmlrpc")) {
      die("Extension xmlrpc not loaded\n");
   }

   $url = "/plugins/webservices/xmlrpc.php";
} else {
   $url = "/plugins/webservices/rest.php";
}

$host = 'localhost/glpi';
$host = 'kiosks.ipmfrance.com';
$glpi_user  = "test_ws";
$glpi_pass  = "ipm-France2012";



/*
* LOGIN
*/
function login() {
   global $glpi_user, $glpi_pass, $ws_user, $ws_pass;

   $args['method']          = "glpi.doLogin";
   $args['login_name']      = $glpi_user;
   $args['login_password']  = $glpi_pass;

   if (isset($ws_user)){
      $args['username'] = $ws_user;
   }

   if (isset($ws_pass)){
      $args['password'] = $ws_pass;
   }

   if($result = call_glpi($args)) {
      return $result['session'];
   }
   
   return null;
}

/*
* LOGOUT
*/
function logout() {
    $args['method'] = "glpi.doLogout";
    
    if($result = call_glpi($args)) {
       return true;
    }
}

/*
* GENERIC CALL
*/
function call_glpi($args) {
   global $host,$url,$deflate,$base64,$xmlrpc;

   if ($xmlrpc) {
      echo "+ Calling {".$args['method']."} RPC, on http://$host/$url\n";
   } else {
      echo "+ Calling {".$args['method']."} JSON, on http://$host/$url\n";
   }

   if (isset($args['session'])) {
      $url_session = $url.'?session='.$args['session'];
   } else {
      $url_session = $url;
   }

   if ($xmlrpc) {
      $header = "Content-Type: text/xml";
      if (isset($deflate)) {
         $header .= "\nAccept-Encoding: deflate";
      }
      $request = xmlrpc_encode_request($args['method'], $args);
      $context = stream_context_create(array('http' => array('method'  => "POST",
                                                             'header'  => $header,
                                                             'content' => $request)));
   } else {
      $header = "Content-Type: application/json";
      if (isset($deflate)) {
         $header .= "\nAccept-Encoding: deflate";
      }
      $context = stream_context_create(array('http' => array('method'  => "GET",
                                                             'header'  => $header)));
      if (isset($args['session'])) {
         $url_session = $url.'?session='.$args['session'].'&'.http_build_query($args);
      } else {
         $url_session = $url.'?'.http_build_query($args);
      }
   }
   
   
   $begin_time = array_sum(explode(' ', microtime()));

   $file = file_get_contents("http://$host/$url_session", false, $context);

   $end_time = array_sum(explode(' ', microtime()));
   echo '+ Duration: '.($end_time - $begin_time)."\n";
   
   if (!$file) {
      die("+ No response\n");
   }

   if (in_array('Content-Encoding: deflate', $http_response_header)) {
      $lenc=strlen($file);
      echo "+ Compressed response : $lenc\n";
      $file = gzuncompress($file);
      $lend=strlen($file);
      echo "+ Uncompressed response : $lend (".round(100.0*$lenc/$lend)."%)\n";
   }
   
   if ($xmlrpc) {
      $response = xmlrpc_decode($file);
      if (!is_array($response)) {
         echo "+ Content : $file\n";
         echo "+ Bad response, not an array !\n";
      }
      
      if (is_array($response) && xmlrpc_is_fault($response)) {
          echo(" -> xmlrpc error(".$response['faultCode']."): ".$response['faultString']."\n");
          return null;
      }
   } else {
      // echo "+ Content : $file\n";
      $response = json_decode($file, true);
      // print_r($response);
   }
   return $response;
}

/*
* getTickets
*/
function getTickets($session, $start=0, $limit=5000) {
   /*
   * Get tickets
   */
   $args['session'] = $session;
   $args['method'] = "glpi.listTickets";
   $args['start'] = $start;
   $args['limit'] = $limit;
   
   if ($tickets = call_glpi($args)) {
      // print_r($tickets);
      echo "+ Got ".count($tickets)." records.\n";
      return $tickets;
   }

   return null;
}

/*
* getCounters
*/
function getCounters($session, $lastPerHost=false, $start=0, $limit=500) {
   /*
   * Get counters
   */
   $args['session'] = $session;
   $args['method'] = "monitoring.getDailyCounters";
   if ($lastPerHost) $args['lastPerHost'] = true;
   $args['filter'] = "`glpi_entities`.`name` LIKE '%ISERE%'";
   $args['start'] = $start;
   $args['limit'] = $limit;
   
   if ($counters = call_glpi($args)) {
      echo "+ Got ".count($counters)." records.\n";
      // print_r($counters);
      return $counters;
   }

   return null;
}

/*
* getStatistics
*/
function getStatistics($session, $statistics='sum', $group='hostname', $order='hostname ASC', $start=0, $limit=500) {
   /*
   * Get statistics
   */
   $args['session'] = $session;
   $args['method'] = "monitoring.getDailyCounters";
   $args['statistics'] = $statistics;
   $args['group'] = $group;
   $args['order'] = $order;
   $args['start'] = $start;
   $args['limit'] = $limit;
   
   if ($counters = call_glpi($args)) {
      echo "+ Got ".count($counters)." records.\n";
      // print_r($counters);
      return $counters;
   }

   return null;
}

/*
* getOverallState
*/
function getOverallState($session, $view="Hosts") {
   /*
   * Get overall status
   */
   $args['session'] = $session;
   $args['method'] = "monitoring.dashboard";
   /* Requested view : 
      'Hosts', counters for all monitored hosts
      'Ressources', counters for all monitored services
      'Componentscatalog', counters for components catalogs
      'Businessrules', counters for business rules
   */
   $args['view'] = $view;
   if ($counters = call_glpi($args)) {
      echo "+ Got ".count($counters)." records.\n";
      // print_r($counters);
      return $counters;
   }

   return null;
}

/*
* getHostsStates
*/
function getHostsStates($session, $filter="") {
   /*
   * Get hosts states
   */
   $args['session'] = $session;
   $args['method'] = "monitoring.getHostsStates";
   /* Filter used in DB query; you may use : 
      `glpi_entities`.`name`, for entity name, or any column name from glpi_entities table
      `glpi_computers`.`name`, for computer name, or any column name from glpi_computers table
      any column name from glpi_plugin_monitoring_hosts table
   */
   // $args['filter'] = "`glpi_computers`.`name` LIKE 'ek3k%'";
   $args['filter'] = $filter;

   if ($hostsStates = call_glpi($args)) {
      echo "+ Got ".count($hostsStates)." records.\n";
      echo "Host states : \n";
      foreach ($hostsStates as $computer) {
         echo " - ".$computer['name']." is ".$computer['state']." (".$computer['state_type'].")\n";
      }
      
      return $hostsStates;
   }

   return null;
}

/*
* getServicesStates
*/
function getServicesStates($session, $filter="") {
   /*
   * Get hosts states
   */
   $args['session'] = $session;
   $args['method'] = "monitoring.getServicesStates";
   /* Filter used in DB query; you may use : 
      `glpi_entities`.`name`, for entity name, or any column name from glpi_entities table
      `glpi_computers`.`name`, for computer name, or any column name from glpi_computers table
      `glpi_computers`.*,
      `glpi_plugin_monitoring_hosts`.*,
      `glpi_plugin_monitoring_services`.*,
      `glpi_plugin_monitoring_componentscatalogs_hosts`.*,
      `glpi_plugin_monitoring_components`.*
   */
   // $args['filter'] = "`glpi_computers`.`name` LIKE 'ek3k%'";
   $args['filter'] = $filter;

   if ($servicesStates = call_glpi($args)) {
      echo "+ Got ".count($servicesStates)." records.\n";
      echo "Services states : \n";
      foreach ($servicesStates as $service) {
         echo " - ".$service['host_name']." / ".$service['name']." is ".$service['state']." (".$service['state_type'].")\n";
      }
      
      return $servicesStates;
   }

   return null;
}

/*
* ACTIONS
*/

// Init sessions
if (! $session = login()) {
   die ("Connexion refused !\n");
}

// Tickets
if (getTickets($session)) {
}

// die('test');

// Hosts counters
if (getOverallState($session, "Hosts")) {
}
// Services catalogs counters
if (getOverallState($session, "Businessrules")) {
}
// Components catalogs counters
if (getOverallState($session, "Componentscatalog")) {
}
// Services counters
if (getOverallState($session, "Ressources")) {
}

// Hosts states
if (getHostsStates($session)) {
}
// Services states
if (getServicesStates($session)) {
}

// All counters
if (getCounters($session)) {
}
// Last counters per each host
if (getCounters($session, true)) {
}
// Sum of daily printed pages per host
if (getStatistics($session, 'sum', 'hostname', 'hostname ASC')) {
}
// Sum of daily printed pages per entity ...
if (getStatistics($session, 'sum', '`glpi_entities`.`name`', '`glpi_entities`.`name` ASC')) {
}
// ... ordered by printed pages total ascending.
if (getStatistics($session, 'sum', '`glpi_entities`.`name`', '`sum_cPagesToday` ASC')) {
}

// Logout
logout();
?>
