<?php

/*
   ----------------------------------------------------------------------
   Monitoring plugin for GLPI
   Copyright (C) 2010-2011 by the GLPI plugin monitoring Team.

   https://forge.indepnet.net/projects/monitoring/
   ----------------------------------------------------------------------

   LICENSE

   This file is part of Monitoring plugin for GLPI.

   Monitoring plugin for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation, either version 2 of the License, or
   any later version.

   Monitoring plugin for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with Monitoring plugin for GLPI.  If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------
   Original Author of file: David DURIEUX
   Co-authors of file:
   Purpose of file:
   ----------------------------------------------------------------------
 */

define('GLPI_ROOT', '../../..');

include (GLPI_ROOT . "/inc/includes.php");

commonHeader($LANG['plugin_monitoring']['title'][0],$_SERVER["PHP_SELF"], "plugins",
             "monitoring", "host");

$pluginMonitoringHost_Host = new PluginMonitoringHost_Host();
if (isset($_POST['parent_add'])) {
   // Add host in dependencies/parent of host

   $input = array();
   $input['plugin_monitoring_hosts_id_1'] = $_POST['id'];
   $input['plugin_monitoring_hosts_id_2'] = $_POST['parent_to_add'];
   $pluginMonitoringHost_Host->add($input);

   glpi_header($_SERVER['HTTP_REFERER']);
} else if (isset($_POST['parent_delete'])) {
   // Delete host in dependencies/parent of host

   foreach ($_POST['parent_to_delete'] as $delete_id) {
      $query = "DELETE FROM ".$pluginMonitoringHost_Host->getTable()."
         WHERE `plugin_monitoring_hosts_id_1`='".$_POST['id']."'
            AND `plugin_monitoring_hosts_id_2`='".$delete_id."'";
      $DB->query($query);
   }
   glpi_header($_SERVER['HTTP_REFERER']);
}

commonFooter();

?>