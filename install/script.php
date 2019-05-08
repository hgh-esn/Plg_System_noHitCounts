<?php
/**
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
 * @subpackage  plg_stophitcounts
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
//      echo '<br />' .'akt. Verzeichnis= ' .getcwd() . "\n";

		$dsn = '../plugins/system/stophitcounts/stophitcounts.xml';
      
		if (file_exists($dsn))
		{
			$xml = simplexml_load_file($dsn);
//       	print_r($xml);
//        	echo '<p>' . JText::sprintf('stophitcounts_UPDATE_TEXT', $parent->get('manifest')->version) . '</p>';
			echo '<p>' .'Note: Die neue/aktuelle Version des Plugins ist jetzt: <b>' .$xml->version .'</b></p>';
		} 
		else
		{
			exit('Konnte ' .$dsn .' nicht öffnen.');
//      	echo 'Konnte ' .$dsn .' nicht öffnen.';
		}     
	}

    /**
     * Runs just before any installation action is performed.
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
		echo '<p>' . JText::_('stophitcounts_PREFLIGHT_' . $type . ' We do some updates') . '</p>';

		/*********************************************************************
		 * preflight actions
		 * in V1.2.2 params items have been changed there names.
		 * disable_users  -> disabled_users
		 * disable_groups -> disabled_groups
		 * this changes have to be done also in the DB to already existing entries
		 *********************************************************************/
		$db =JFactory::getDBO();
		$query = "SELECT params 		FROM `#_extensions` WHERE name LIKE '%stophitcount%'";
		$db->setQuery($query);
		$shc_parms_db =  $db->loadResult();
		
		$query = "SELECT extension_id  	FROM `#_extensions` WHERE name LIKE '%stophitcount%'";
		$db->setQuery($query);
		$shc_exid =  $db->loadResult();

//					echo '<br />' .$shc_parms_db;
//					echo '<br />' .$shc_exid;

		// do rename parms

		$shc_parms = str_ireplace('disable_users','disabled_users',$shc_parms_db,$cnt);
		echo '<br /> count rename - disable_/disabled_users : change-counts  =' .$cnt;
		$shc_parms = str_ireplace('disable_groups','disabled_groups',$shc_parms,$cnt);
		echo '<br /> count rename - disable_/disabled_groups: change-counts =' .$cnt;

		echo '<br />' .$shc_parms;

		if ( $shc_parms_db !== $shc_parms)
		{
			echo '<br /> params-update for extension necessary';
			$query = "UPDATE #_extensions SET params = " .$shc_parms ." WHERE extension_id=" .$exid;
			$db->execute();
		}
		else
		{
			echo '<br /> NO params-update for extension necessary';
			echo '<hr><p>' . JText::_('stophitcounts_PREFLIGHT_' .$type .' => nothing to do') . '</p>';
		}
		// END-preflight
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
	//       echo 'Pfad: ' .$pfad .' gelöscht.';
			 echo 'clean-up for ' .$pfad .' done.';
		}
		  else
		{
	//      echo 'Pfad: ' .$pfad .' nicht gefunden';
			echo 'nothing to do! ;)';       
		}
	}	
} // end-class
