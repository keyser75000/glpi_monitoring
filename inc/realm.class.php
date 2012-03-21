<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2012 by the Plugin Monitoring for GLPI Development Team.

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
   along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Monitoring for GLPI
   @author    David Durieux
   @co-author 
   @comment   
   @copyright Copyright (c) 2011-2012 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2012
 
   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringRealm extends CommonDropdown {

   public $first_level_menu  = "plugins";
   public $second_level_menu = "monitoring";

   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_monitoring']['realms'][0];
   }

   function canCreate() {
      return haveRight('computer', 'w');
   }

   function canView() {
      return haveRight('computer', 'w');
   }
   
   
//   
//   function getSearchOptions() {
//      global $LANG;
//
//      $tab = array();
//
//      $tab['common'] = $LANG['plugin_fusioninventory']['menu'][5];
//
//      $tab[1]['table'] = $this->getTable();
//      $tab[1]['field'] = 'name';
//      $tab[1]['name'] = $LANG['common'][16];
//      $tab[1]['datatype'] = 'itemlink';
//
//      $tab[3]['table']         = $this->getTable();
//      $tab[3]['field']         = 'itemtype';
//      $tab[3]['name']          = $LANG['common'][17];
//      $tab[3]['massiveaction'] = false;
//
//      $tab[4]['table'] = $this->getTable();
//      $tab[4]['field'] = 'username';
//      $tab[4]['name'] = $LANG['login'][6];
//
//      return $tab;
//   }

   
   
   
   function prepareInputForAdd($input) {
      $input['name'] = preg_replace("/[^A-Za-z0-9]/","",$input['name']);
      return $input;
   }


   
   function prepareInputForUpdate($input) {
      $input['name'] = preg_replace("/[^A-Za-z0-9]/","",$input['name']);
      return $input;
   }

}

?>