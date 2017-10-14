<?php
/**
 * @package    stopHitCounts
 * @subpackage Base
 * @author     Hans-Guenter Heiserholt [HGH] {@link moba-hgh/joomla}
 * @author     Created on 10-Oct-2017
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

/**
 * System Plugin.
 *
 * @package    stopHitCounts
 * @subpackage Plugin
 */
 
class plgSystemstopHitCounts extends JPlugin
{
    /**
     * Constructor
     *
     * @param object $subject The object to observe
     * @param array $config  An array that holds the plugin configuration
     */
    public function __construct(& $subject, $config)
    {
      parent::__construct($subject, $config);
      $this->loadLanguage();

      // load the plugin parameters
      
      if(!isset($this->params))
      {
         $plugin       = JPluginHelper::getPlugin('system', 'stophitcounts');
         $this->params = new JRegistry($plugin->params);
      }
      
      if ($this->params->get('log_path'))
      {
         $logpath = $this->params->get('log_path');            
      }

    } // end-function-construct

    /**
     *  onContentBeforeDisplay
     */

   public function onContentBeforeDisplay($context, &$article, &$params, $limitstart)
   {  
      $botfound = false;
      /**********************************************
       * First of all, we check if it is a bot-access
       * Then the counter is decremented because there 
       * is no counting
       **********************************************/      
      if ($this->params->get('disable_bots'))
      {
         $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
         
         // returns true or false
         $botfound = $this->checkBot($user_agent);
         if ($botfound)
         { 
            $this->decrcnt();
            
            if ($this->params->get('log_active'))
            {
               JLog::add('Bot - decr. hitCounter and return');
            }
            return;
         }
                
      }
      
      /***********************************
       * get act. UserData
       ***********************************/
      $user = JFactory::getUser();
      $groups = $user->groups;
      $userid = $user->id;

//      echo '<br />' .'userid=' .$user->id;
//      echo '<br />' .'name='   .$user->name;
//      echo '<br />';

         if ($article->featured)
         {
//         echo '<br />' .'loginuser=' .$user->id .'article featured';
         $this->decrcnt();
         return;
         }
      /***************************************
       * check parm-user(s) to ignore counting
       ***************************************/
          
      if ($this->params->get('disable_users') && $user->id > 0 )
      {
//       echo '<br />' .'get-disable_users=' .print_r($this->params->get('disable_users'));
         if (in_array($user->id, $this->params->get('disable_users')) )
         {
//          echo '<br />' .'loggedIn-user=' .$user->id .' is blocked from counting.';
            if ($this->params->get('log_active'))
            {
               JLog::add('loggedIn-user=' .$user->id .' is blocked from counting.');
            } 
         }
      }     

      /***********************************
       * Check Groups
       ***********************************/
      if ($this->params->get('disable_groups'))
      {
//         echo '<br />' .'parm-groups=' .$this->params->get('disable_groups');
//         echo '<br />' .'group(s)='    .print_r($groups);
         
         if (in_array($user->getAuthorisedGroups(), $this->params->get('disable_groups')) )
         {
//          echo '<br />' .'loggedIn-user=' .$user->id .' in the to block group for counting.';

            if ($this->params->get('log_active'))
            {
               JLog::add('loggedIn-user=' .$user->id .' in the to block group for counting.');
            } 
         }
      }
         
      /**************************************************
       * Check if public-user matches
       **************************************************/       
      if ($user->id == 0 && $botfound === false)
      {
//        echo '<br />' .'user-parm='.$this->params->get('disable_users');
//        echo '<br />' .'actual user is Public - Article-HitCounter stays counting';

         if ($this->params->get('log_active'))
         {
            JLog::add('actual user is PUBLIC - hitcounter not blocked');
         }
         return;
      }
      
      /**************************************************
       * Check if user is not a Super-Admin and not a bot
       **************************************************/ 
/*
      if (!in_array(8, $groups) && ($botfound === false) && ($this->params->get('disable_users') != $user->id))
      {
         // only enter if the user is in the group 8 (group 8 = Super-Administrator)
          echo '<br />' .'actual user is NO Super-Admin' 
              .'<br />' .'Article-HitCounter stays counting';
 
         if ($this->params->get('log_active'))
         {
            JLog::add('actual user is NO Super-Admin');
         }
         return;
      }
      else
      {
*/
/*
         echo '<br />' .'Ihre IP-Adresse ist:<b> ' .$_SERVER['REMOTE_ADDR'] .'</b>';
               
         echo '<br />' .'created_by='.$article->created_by;;
         echo '<br />' .'user='      .$user; 
         echo '<br />' .'userid='    .$user->id .'<br />';
         echo '<br />' .'groups='    .print_r($groups);       
*/
//      }
 
      /***********************************
       * increment hitcounter
       ***********************************/
      
     if ( ($context  == 'com_content.article' && $user->id == $article->created_by) 
          || ($botfound === true) 
          || in_array($user->id, $this->params->get('disable_users'))
          || in_array($user->getAuthorisedGroups(), $this->params->get('disable_groups'))
        )
     {
         if ($this->params->get('log_active'))
         {
            JLog::add('decr. article-hitCounter =' .$article->id .'/' .$article->hits);
         }
         
         
                
//        echo '<br />' .'no HitCounting - [user-id <-> created by] matched or a bot';

/*
         if ($article->hits > 0)
         {       
            /****************************************************************************************
             * we decrement the article-hitconter because it is already incremented by joomla before
             ****************************************************************************************
            $db = JFactory::getDbo();
            $db->setQuery('UPDATE #__content SET hits = hits - 1 WHERE id = ' .$article->id);
            $db->execute();
         }
*/
           $this->decrcnt();
      }
      else 
      {
//          echo '<br />' .'HitCounting';
      }
       
      return '';

   }// end-function onContentBeforeDisplay

private function decrcnt()
{
         if ($article->hits > 0)
         {       
            /****************************************************************************************
             * we decrement the article-hitconter because it is already incremented by joomla before
             ****************************************************************************************/
            $db = JFactory::getDbo();
            $db->setQuery('UPDATE #__content SET hits = hits - 1 WHERE id = ' .$article->id);
            $db->execute();
         }
}
   
   /**
    * Method to check if the user agent is a bot
    * @access private
    * @param $user_agent string The user agent data
    * @return bool True if match (is bot), false if doesn't match (not a bot)
    * @since 1.0.0
    */
   private function checkBot($user_agent)
   {
      /****************************************************
       * get the custom bots from the plugin configuration
       ****************************************************/
      $custom_bots = explode(',', $this->params->get('custom_bots'));

      /*************************************************************************
       * array of most common known robots
       * Note: The original array contains a bot named 'Web'.
       *       That interferres with the normal browser HTTP_USER_AGENT-string
       *       I deleted it in the array.
       *************************************************************************/
      $bots = array('bingbot', 'msn', 'abacho', 'abcdatos', 'abcsearch', 'acoon', 'adsarobot', 'aesop', 'ah-ha',
         'alkalinebot', 'almaden', 'altavista', 'antibot', 'anzwerscrawl', 'aol', 'search', 'appie', 'arachnoidea',
         'araneo', 'architext', 'ariadne', 'arianna', 'ask', 'jeeves', 'aspseek', 'asterias', 'astraspider', 'atomz',
         'augurfind', 'backrub', 'baiduspider', 'bannana_bot', 'bbot', 'bdcindexer', 'blindekuh', 'boitho', 'boito',
         'borg-bot', 'bsdseek', 'christcrawler', 'computer_and_automation_research_institute_crawler', 'coolbot',
         'cosmos', 'crawler', 'crawler@fast', 'crawlerboy', 'cruiser', 'cusco', 'cyveillance', 'deepindex', 'denmex',
         'dittospyder', 'docomo', 'dogpile', 'dtsearch', 'elfinbot', 'entire', 'esism', 'artspider', 'exalead',
         'excite', 'ezresult', 'fast', 'fast-webcrawler', 'fdse', 'felix', 'fido', 'findwhat', 'finnish', 'firefly',
         'firstgov', 'fluffy', 'freecrawl', 'frooglebot', 'galaxy', 'gaisbot', 'geckobot', 'gencrawler', 'geobot',
         'gigabot', 'girafa', 'goclick', 'goliat', 'googlebot', 'griffon', 'gromit', 'grub-client', 'gulliver',
         'gulper', 'henrythemiragorobot', 'hometown', 'hotbot', 'htdig', 'hubater', 'ia_archiver', 'ibm_planetwide',
         'iitrovatore-setaccio', 'incywincy', 'incrawler', 'indy', 'infonavirobot', 'infoseek', 'ingrid', 'inspectorwww',
         'intelliseek', 'internetseer', 'ip3000.com-crawler', 'iron33', 'jcrawler', 'jeeves', 'jubii', 'kanoodle',
         'kapito', 'kit_fireball', 'kit-fireball', 'ko_yappo_robot', 'kototoi', 'lachesis', 'larbin', 'legs',
         'linkwalker', 'lnspiderguy', 'look.com', 'lycos', 'mantraagent', 'markwatch', 'maxbot', 'mercator', 'merzscope',
         'meshexplorer', 'metacrawler', 'mirago', 'mnogosearch', 'moget', 'motor', 'muscatferret', 'nameprotect',
         'nationaldirectory', 'naverrobot', 'nazilla', 'ncsa', 'beta', 'netnose', 'netresearchserver', 'ng/1.0',
         'northerlights', 'npbot', 'nttdirectory_robot', 'nutchorg', 'nzexplorer', 'odp', 'openbot', 'openfind',
         'osis-project', 'overture', 'perlcrawler', 'phpdig', 'pjspide', 'polybot', 'pompos', 'poppi', 'portalb',
         'psbot', 'quepasacreep', 'rabot', 'raven', 'rhcs', 'robi', 'robocrawl', 'robozilla', 'roverbot', 'scooter',
         'scrubby', 'search.ch', 'search.com.ua', 'searchfeed', 'searchspider', 'searchuk', 'seventwentyfour',
         'sidewinder', 'sightquestbot', 'skymob', 'sleek', 'slider_search', 'slurp', 'solbot', 'speedfind', 'speedy',
         'spida', 'spider_monkey', 'spiderku', 'stackrambler', 'steeler', 'suchbot', 'suchknecht.at-robot', 'suntek',
         'szukacz', 'surferf3', 'surfnomore', 'surveybot', 'suzuran', 'synobot', 'tarantula', 'teomaagent', 'teradex',
         't-h-u-n-d-e-r-s-t-o-n-e', 'tigersuche', 'topiclink', 'toutatis', 'tracerlock', 'turnitinbot', 'tutorgig',
         'uaportal', 'uasearch.kiev.ua', 'uksearcher', 'ultraseek', 'unitek', 'vagabondo', 'verygoodsearch', 'vivisimo',
         'voilabot', 'voyager', 'vscooter', 'w3index', 'w3c_validator', 'wapspider', 'wdg_validator', 'webcrawler',
         'webmasterresourcesdirectory', 'webmoose', 'websearchbench', 'webspinne', 'whatuseek', 'whizbanglab', 'winona',
         'wire', 'wotbox', 'wscbot', 'www.webwombat.com.au', 'xenu', 'link', 'sleuth', 'xyro', 'yahoobot', 'yahoo!',
         'slurp', 'yandex', 'yellopet-spider', 'zao/0', 'zealbot', 'zippy', 'zyborg', 'mediapartners-google'
      );

      if(!empty($custom_bots))
      {
         /**************************************************
         * prepare the array and merge with any custom bots
         ***************************************************/
         
         $bots = array_map('strtolower', array_unique(array_merge($bots, $custom_bots)));
         natcasesort($bots);
      }
      
//   echo '<br />' .'Bot/User-Agent=' .$user_agent; 

// http://phpforum.de/forum/showpost.php?p=1445078&postcount=9

    for($i=0; $i <= count($bots); $i++)
    {
      if ( stristr($user_agent, $bots[$i])) 
      {
 //      echo '<br />'.'bot=' .$bots[$i];
      
         if ($this->params->get('log_active'))
         {
            JLog::add('Bot - ' .$bots[$i] .' - found');
         }
         return true;        
      }
    } 
    return false; 
/*
// This regex-Version makes no matches.
// make sure we escape any / characters for our regular expression
      $bots = str_replace('/','\/', $bots);

      // implode the array to a string like a|b|c| 
      $pattern = '/\s(' .implode('|', $bots) .')\s?/i';

      // set the matches array
      $matches = array();

      // do the match      
      $matchcount = preg_match($pattern, strtolower($user_agent), $matches, 0);

      // verify if we have any matches 
      if($matchcount > 0)
      {  
         // if matches found, return true
         if ($this->params->get('log_active'))
         {
            JLog::add('Bot - ' .$matches[0] .' - found');
         }
         $matchval = $matches[0];
         return true;
      }
      else   return false;
*/    
   }// end-function

}// end-class

/**
 * Helper for logging
 * @package    Notes
 * @subpackage com_notes
 * see: https://docs.joomla.org/Using_JLog
 */

jimport('joomla.log.log');

// Add the logger.

JLog::addLogger(
     // Pass an array of configuration options
     
    array(
            // Set the name of the log file
            'text_file' => 'plg_stophitcount-log.php',
            // (optional) you can change the directory
              'text_file_path' => 'administrator/logs'
 //               'text_file_path' => $logpath
       ),     
         JLog::INFO 
        // The log category/categories which should be recorded in this file
        // In this case, it's just the one category from our extension, still
        // we need to put it inside an array
        
//        array('plg_stophitcounts')
);

