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
//      $parent->getParent()->setRedirectURL('index.php?option=stophitcounts');
//      echo '<br />' .JText::_('stophitcounts_INSTALL_TEXT');
		echo '<br />' .JText::_('PLG_SYSTEM_SHC_INST_SEE_NOTES');

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
//		echo '<br />' .'Uninstall - nothing to do';
		echo '<br />' .JText::_('PLG_SYSTEM_SHC_UNINST_NOTHING_TO_DO');
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
//      echo '<br />' .JText::_('stophitcounts_UPDATE_' . $type . ' see notes!');
		echo '<br />' .JText::_('PLG_SYSTEM_SHC_UPD_SEE_NOTES');

		// aktuelles Verzeichnis
//      echo '<br />' .'akt. Verzeichnis= ' .getcwd() . "\n";

		$dsn = '../plugins/system/stophitcounts/stophitcounts.xml';
      
		if (file_exists($dsn))
		{
			$xml = simplexml_load_file($dsn);
// 			print "<pre>";			
//       	print_r($xml);
// 			print "</pre>";

//			echo '<br />' .'Note: Die neue/aktuelle Version des Plugins ist jetzt: <b>' .$xml->version .'</b>';
			echo '<br />' .JText::_('PLG_SYSTEM_SHC_UPD_NEW_VERSION_IS')  .'<b>' .$xml->version .'</b>';

		} 
		else
		{
			exit('Konnte ' .$dsn .' nicht öffnen.');
//      	echo '<br />' .'Konnte ' .$dsn .' nicht öffnen.';
			echo '<br />' .JText::_('PLG_SYSTEM_SHC_UPD_DSN_NOT_TO_OPEN');

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
//		echo '<br />' .JText::_('stophitcounts_PREFLIGHT_' . $type . ' We do some updates');
		echo '<br />' .JText::_('PLG_SYSTEM_SHC_DB_UPD_PARAMS');

		// Start prefight
		
		/*********************************************************************
		 * preflight actions
		 * in V1.2.2 params items have been changed there names.
		 * disable_users  -> disabled_users
		 * disable_groups -> disabled_groups
		 * this changes have to be done also in the DB to already existing entries
		 *********************************************************************/
		$db =& JFactory::getDBO();
		
		$query = $db->getQuery(true);
//		$query = 'SELECT params FROM #__extensions WHERE name LIKE "%stophitcount%"';

	//  qn = 'quotename'
		$query->select($query->qn('params'))
			  ->from($query->qn('#__extensions'))
			  ->where($query->qn('name') . 'LIKE "%stophitcount%"');
		$db->setQuery($query);
 		$shc_parms_readFromDB = $db->loadResult();
		
		if ( $db->getErrorNum() ) 
		{
 			echo  '<br />' 	.'db-query: db-error - return';
 			echo  '<br />' 	.$db->getErrorNum();
			return;				
		}
		if ( empty($shc_parms_readFromDB ) ) 
		{
			// we are on an initial installation 
			echo  '<br />' .'no params-entry found - nothing further to do ... return';
			echo  '<br />' .'$shc_parms_readFromDB= ' .$shc_parms_readFromDB;
			return;
		}
		else 
		{
			echo  '<br />' 	.'params-entry found - do further operations';
			echo  '<br />' .'$shc_parms_readFromDB= ' .$shc_parms_readFromDB;
		}
		// End prefight test

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
		echo '<br />' .JText::_('stophitcounts_POSTFLIGHT_' . $type . ' We do some clean-ups');
		echo '<br />' .JText::_('PLG_SYSTEM_SHC_POSTFLIGHT_CLEANUP');
	// 	echo '' 		 .JText::sprintf('The new version is now: ', $parent->get('manifest')->version);
		  
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
	//      echo 'Pfad: ' .$pfad .' gelöscht.';
			echo '<br />' .'clean-up done for ' .$pfad;
			echo '<br />' .JText::_('PLG_SYSTEM_SHC_POSTFLIGHT_CLEANUP_DONE');

		}
		  else
		{
	//      echo 'Pfad: ' .$pfad .' nicht gefunden';
			echo '<br />' .JText::_('PLG_SYSTEM_SHC_POSTFLIGHT_CLEANUP_PATH_NOT_EXISTS');
			echo '<br />' .'nothing to do!';       
			echo '<br />' .JText::_('PLG_SYSTEM_SHC_POSTFLIGHT_CLEANUP_NOTHING_TO_DO');
		}
	}	
} // end-class
