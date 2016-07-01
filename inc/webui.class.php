<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2016 by the Plugin Monitoring for GLPI Development Team.

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
   @author    David Durieux
   @co-author
   @comment
   @copyright Copyright (c) 2011-2016 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2016

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringWebui {

   function authentication($token) {
      echo Html::scriptBlock('$.ajaxSetup({
         headers: { "Authorization": "Basic " + btoa("'.$token.':") }}
      );');
   }


   function load_page($div_id, $page) {
      echo "<div id='webui'></div>";
      echo Html::scriptBlock(Ajax::updateItemJsCode($div_id, $page, array(), "",
              False));
   }


   function routes($httpMethod, $url) {
      require GLPI_ROOT.'/plugins/monitoring/lib/vendor/autoload.php';

      $dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
          $r->addRoute('GET', '/users', 'get_all_users');
          // {id} must be a number (\d+)
          $r->addRoute('GET', '/user/{id:[0-9a-f]+}', 'handler');
      });

      //$httpMethod = $_SERVER['REQUEST_METHOD'];
      $url = rawurldecode($url);
      $routeInfo = $dispatcher->dispatch($httpMethod, $url);
      if ($routeInfo[0] == FastRoute\Dispatcher::FOUND) {
         $handler = $routeInfo[1];
         $vars = $routeInfo[2];
         print_r($routeInfo);

      }
   }


   function example_display(){
      global $PM_CONFIG;

      $abc = new Alignak_Backend_Client($PM_CONFIG['alignak_backend_url']);
      PluginMonitoringUser::my_token($abc);

      $pmWebui = new PluginMonitoringWebui();
      $pmWebui->authentication($abc->token);

      //$page = $PM_CONFIG['alignak_webui_url']."/external/widget/hosts_table?widget_id=test&widget_template=hosts_table_widget&links=1";
      //$page = $PM_CONFIG['alignak_webui_url']."/external/table/hosts_graph?page=1&links=1&widget_id=hosts_graph";
      $page = $PM_CONFIG['alignak_webui_url']."/external/table/hosts_table?widget_id=hosts_graph&links=/glpi090/plugins/monitoring/front/test.php?url=";
      //$page = $PM_CONFIG['alignak_webui_url']."/external/widget/livestate_table?widget_id=test&widget_template=livestate_table_widget";
      $pmWebui->load_page('webui', $page);

   }
}

?>