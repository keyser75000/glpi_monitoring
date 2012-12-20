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

class PluginMonitoringDisplayview extends CommonDBTM {
   

   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return __('Views', 'monitoring');
   }



   static function canCreate() {
      return PluginMonitoringProfile::haveRight("view", 'w');
   }


   
   static function canView() {
      return PluginMonitoringProfile::haveRight("view", 'r');
   }

   

   function getSearchOptions() {
      $tab = array();
    
      $tab['common'] = __('Views', 'monitoring');

		$tab[1]['table'] = $this->getTable();
		$tab[1]['field'] = 'name';
		$tab[1]['linkfield'] = 'name';
		$tab[1]['name'] = __('Name');
		$tab[1]['datatype'] = 'itemlink';


      return $tab;
   }



   function defineTabs($options=array()){
      global $CFG_GLPI;

      $ong = array();
      if ($this->fields['id'] > 0) {
         $ong[1] = 'items';
      }
      
      return $ong;
   }
   
   
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      $array_ret = array();

      if (PluginMonitoringProfile::haveRight("viewshomepage", 'r')) {
         $pmDisplayview = new PluginMonitoringDisplayview();
         $i = 50;
         $a_views = $pmDisplayview->getViews(1);
         foreach ($a_views as $name) {
            $array_ret[$i] = self::createTabEntry(
                    __('Monitoring', 'monitoring')."-".htmlentities($name));
            $i++;
         }
      }
      return $array_ret;
   }



   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      $pmDisplayview = new PluginMonitoringDisplayview();
      $a_views = $pmDisplayview->getViews();

      $i = 50;
      foreach ($a_views as $views_id=>$name) {
         if ($tabnum == $i) {
            $pmDisplayview_item = new PluginMonitoringDisplayview_item();
            $pmDisplayview_item->view($views_id);
            break;
         }
         $i++;
      }
      return true;
   }



   /**
   * Display form for agent configuration
   *
   * @param $items_id integer ID 
   * @param $options array
   *
   *@return bool true if form is ok
   *
   **/
   function showForm($items_id, $options=array()) {
      global $DB,$CFG_GLPI;

      if ($items_id!='') {
         $this->getFromDB($items_id);
      } else {
         $this->getEmpty();
         $this->fields['width'] = 950;
      }

      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Name')." :</td>";
      echo "<td>";
      echo "<input type='text' name='name' value='".$this->fields["name"]."' size='30'/>";
      echo "</td>";

      echo "<td>"._n('Type','Types',1)."&nbsp;:</td>";
      echo "<td>";
      $elements = array();
      $elements['public'] = __('Public');
      $elements['private'] = __('Private');
      
      $value = 'public';
      if ($this->fields["users_id"] > '0') {
         $value = 'private';
      }
      Dropdown::showFromArray('users_id', $elements, array('value'=>$value));
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Header counter (critical/warning/ok)', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      $elements = array();
      $elements['NULL'] = Dropdown::EMPTY_VALUE;
      $elements['Businessrules'] = __('Business rules', 'monitoring');
      $elements['Componentscatalog'] = __('Components catalog', 'monitoring');
      $elements['Ressources'] = __('Resources', 'monitoring');
      Dropdown::showFromArray('counter', $elements, array('value'=>$this->fields['counter']));
      echo "</td>";
      
      echo "<td>";
      echo __('Display in GLPI home page', 'monitoring');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo("in_central", $this->fields['in_central']);
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Width', 'monitoring')." (px) :";
      echo "</td>";
      echo "<td>";
      Dropdown::showInteger("width", $this->fields['width'], 950, 3000);
      echo "</td>";
      echo "<td>";
      echo __('Active');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo("is_active", $this->fields['is_active']);
      echo "</td>";
      echo "</tr>";
      
      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
   }
   
   
   
   function getViews($central='0') {
      global $DB;
      
      $wcentral = '';
      if ($central == '1') {
         $wcentral = " AND `in_central`='1' ";
      }
      
      $a_views = array();
      $query = "SELECT * FROM `glpi_plugin_monitoring_displayviews`      
                WHERE `is_active` = '1'
                  AND (`users_id`='0' OR `users_id`='".$_SESSION['glpiID']."')
                  ".$wcentral."
                  ".getEntitiesRestrictRequest(" AND", 'glpi_plugin_monitoring_displayviews', "entities_id",'', true)."
                ORDER BY `users_id`, `name`";
      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetch_array($result)) {
            $a_views[$data['id']] = $data['name'];
         }
      }
      return $a_views;
   }

}

?>