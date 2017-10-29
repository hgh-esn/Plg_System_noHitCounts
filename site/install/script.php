<?php
/**
 * Script file of HelloWorld component.
 *
 * The name of this class is dependent on the component being installed.
 * The class name should have the component's name, directly followed by
 * the text InstallerScript (ex:. com_helloWorldInstallerScript).
 *
 * This class will be called by Joomla!'s installer, if specified in your component's
 * manifest file, and is used for custom automation actions in its installation process.
 *
 * In order to use this automation script, you should reference it in your component's
 * manifest file as follows:
 * <scriptfile>script.php</scriptfile>
 *
 * @package     Joomla.Administrator
 * @subpackage  com_helloworld
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class plgsystemstophitcountsInstallerScript
{
    /**
     * This method is called after a component is installed.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function install($parent) 
    {
 //     $parent->getParent()->setRedirectURL('index.php?option=stophitcounts');
         echo '<p>' . JText::_('stophitcounts_INSTALL_TEXT') . '</p>';
    }

    /**
     * This method is called after a component is uninstalled.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function uninstall($parent) 
    {
        echo '<p>' . JText::_('stophitcounts_UNINSTALL_TEXT') . '</p>';
    }

    /**
     * This method is called after a component is updated.
     *
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function update($parent) 
    {
        echo '<p>' . JText::_('stophitcounts_UPDATE_' . $type . ' see notes!') . '</p>';

      // aktuelles Verzeichnis
//       echo '<br />' .'akt. Verzeichnis= ' .getcwd() . "\n";

      $dsn = '../plugins/system/stophitcounts/stophitcounts.xml';
      
      if (file_exists($dsn))
      {
         $xml = simplexml_load_file($dsn);
//          print_r($xml);
//        echo '<p>' . JText::sprintf('stophitcounts_UPDATE_TEXT', $parent->get('manifest')->version) . '</p>';
         echo '<p>' .'Note: Die neue/aktuelle Version des Plugins ist jetzt: <b>' .$xml->version .'</b></p>';
      } 
      else
      {
       exit('Konnte ' .$dsn .' nicht öffnen.');
//      echo 'Konnte ' .$dsn .' nicht öffnen.';
      }     
   }

    /**
     * Runs just before any installation action is preformed on the component.
     * Verifications and pre-requisites should run in this function.
     *
     * @param  string    $type   - Type of PreFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function preflight($type, $parent) 
    {
        echo '<hr><p>' . JText::_('stophitcounts_PREFLIGHT_' .$type .' => nothing to do') . '</p>';
   }

    /**
     * Runs right after any installation action is preformed on the component.
     *
     * @param  string    $type   - Type of PostFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    function postflight($type, $parent) 
    {
      echo '<p>' . JText::_('stophitcounts_POSTFLIGHT_' . $type . ' We do some clean-ups') . '</p>';
     // echo '<p>' . JText::sprintf('The new version is now: ', $parent->get('manifest')->version) . '</p>';
      
      $pfad='../media/2delete'; // for testing
      
      if(is_dir($pfad) == true)
      {
         function rrmdir($dir) 
         {
            if (is_dir($dir)) 
            {
              $objects = scandir($dir);
              foreach ($objects as $object) 
              {
               if ($object != "." && $object != "..") 
               {
                 if (filetype($dir."/".$object) == "dir") 
                 { 
                  rrmdir($dir."/".$object);
                 }                  
                 else 
                 {
                  unlink($dir."/".$object);
                 }
               }
              }
              reset($objects);
              rmdir($dir);
            }
         }
         rrmdir($pfad);       
//         echo 'Pfad: ' .$pfad .' gelöscht.';
          echo 'clean-up for ' .$pfad .' done.';
      }
      else
     {
//      echo 'Pfad: ' .$pfad .' nicht gefunden';
        echo 'nothing to do !';       
     }
    }
}