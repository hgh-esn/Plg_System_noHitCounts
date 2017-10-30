<?php
/*
 * @package    stopHitCounts
 * @subpackage Base
 * @author     Hans-Guenter Heiserholt [HGH] {@link moba-hgh/joomla}
 * @author     Created on 10-Oct-2017
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

/*
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

    } // end-function-construct

    /**
     *  onContentBeforeDisplay
     */
   public function onContentBeforeDisplay($context, &$article, &$params, $limitstart)
   { 
//      echo '<br />' .'Ihre IP-Adresse ist:<b> ' .$_SERVER['REMOTE_ADDR'] .'</b>';    
//      echo '<br />' .'Ihre URL ist:<b> '        .$_SERVER['REQUEST_URI'] .'</b>';
//      echo '<br />' .'context='                 .$context; 

//       $this->logHitCounter($this->params->get('log_active'),$article->id,-);

      /***********************************
       * get act. UserData
       ***********************************/
      $user = JFactory::getUser();
      $groups     = $user->groups;
      $authgroups = $user->getAuthorisedGroups();
      $userid     = $user->id;
      
//    echo '<br />' .'userid=' .$user->id;
//    echo '<br />' .'name='   .$user->name;
//    echo '<br />';
//    echo '<br />' .'context='.$context;

      /********************************************************
       * ignore counting in featured area
       ********************************************************/      
      if ( $context == 'com_content.featured' || $context == 'com_content.category' )
      {
         $msg = 'user= ' .$user->id .' no counting in featuered/category area';
//       echo '<br />' .$msg;
              
         if ( $this->params->get('log_active') )
         {
            JLog::add($msg);
         }
        
         $this->decrHitCounter($this->params->get('log_active'),$article->id,$article->hits);
         return;
      }

      /**********************************************
       * user-check:
       * First of all, we check if it is a bot-access
       * Then the counter is decremented because there 
       * was already a hit
       **********************************************/    
      if ( $this->params->get('disable_bots') )
      {
         $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
         
         // we call "checkBot" it returns true or false
         
         if ( $this->checkBot($user_agent) )
         {        
            $msg = '[Bot]user - decr. hitCounter.';
//          echo '<br />' .$msg;

            $this->decrHitCounter($this->params->get('log_active'),$article->id,$article->hits);
             
            if ( $this->params->get('log_active') )
            {
//             JLog::add($msg);  // meaasage already in checkbot !
            }
            
//          $this-> logHitCounter($this->params->get('log_active'),$article->id,'bot',-);
            return;
         }               
      }

      /**************************************************
       * Check if public-user matches
       **************************************************/       
      if ( $context  == 'com_content.article' && $user->id == 0 )
      {
         $msg = '[public]user - HitCounter stays counting for article[' .$article->id .'].';
//       echo '<br />' .$msg;

         if ( $this->params->get('log_active') )
         {
            JLog::add($msg);
         }
         return;
      }

      /*******************************************************
       * Check if loggedIn user matches a self-created article
       *******************************************************/    
      if ( $this->params->get('disable_selfcreated_only') )
      {
         if ( $context  == 'com_content.article' && $user->id = $article->created_by )
         {
            $msg = 'loggedIn-user=' .$user->id .' matched >created_by< [' .$article->id .'] - no counting.';
//          echo '<br />' .$msg;
              
            if ( $this->params->get('log_active') )
            {
                  JLog::add($msg);
            } 
            $this->decrHitCounter($this->params->get('log_active'),$article->id,$article->hits);
            return;      
         }
      } 
       
      /***************************************
       * check user(s) to ignore for counting
       ***************************************/        
      if ( $this->params->get('disable_users') )
      {
         if ( in_array($user->id, $this->params->get('disable_users')) )
         {
            $msg = 'loggedIn-user= ' .$user->id .' is blocked from counting.';
//          echo '<br />' .$msg;

            if ( $this->params->get('log_active') )
            {
               JLog::add($msg);
            } 
            $this->decrHitCounter($this->params->get('log_active'),$article->id,$article->hits);
            return;
         }
      }     

      /***************************************
       * Check group(s) to ignore for counting
       ***************************************/
      if ( $this->params->get('disable_groups') )
      {
 //         echo '<br />' .'parm-groups='           .print_r($this->params->get('disable_groups')) .'<br />';
 //         echo '<br />' .'group(s)='              .print_r($groups) .'<br />';
 //         echo '<br />' .'autherisedgroup(s)='    .print_r($user->getAuthorisedGroups()) .'<br />';

         foreach ( $this->params->get('disable_groups') as $key => $value ) 
         {         
//          echo '<br />' .'key/value=' .$key .'/' .$value;         
            if ( in_array( $value , $authgroups ) )  
            {
               $msg ='loggedIn-user= ' .$user->id .' in noncounting - group.';
 //            echo '<br />' .$msg;  
               if ( $this->params->get('log_active') )
               {
                  JLog::add($msg);
               }          
               $this->decrHitCounter($this->params->get('log_active'),$article->id,$article->hits);   
               return; 
            }
         }
//       echo'<br />' .'super-user not found';
      }
   }// end-function onContentBeforeDisplay

   /**
    * Method to decrement the Hitcounter
    * @access private
    * @param  -
    * @use    article->hits, article->id, 
    * @return true - when hit-counter before decrementation is > 0, false - when hit-counter = 0
    * @since 1.0.0
    */
    
   public function decrHitCounter($log_active,$id,$hits)
   {
//      echo '<br />'.'fct::decrHitCounter-parm:log_active='.$log_active;
//      echo '<br />'.'fct::decrHitCounter:article-hits='   .$hits;
//      echo '<br />'.'fct::decrHitCounter:article-id='     .$id;
      if ( $hits > 0 )
      {       
         /****************************************************************************************
          * we decrement the article-hitconter because it is already incremented by joomla before
          ****************************************************************************************/
         $db = JFactory::getDbo();
         $db->setQuery('UPDATE #__content SET hits = hits - 1 WHERE id = ' .$id);
         $db->execute();

         if ( $db->getErrorNum() ) 
         {
            $msg = $db->getErrorMsg();
            
//          echo $msg;
            if ( $log_active )
            {
               JLog::add($msg);
            } 
            return false;
         }
                 
         $msg = '- decr. hitCounter[id/hits] = ' .$id .'/' .$hits;
         
//       echo '<br />' .$msg;       
         if ( $log_active )
         {
            JLog::add($msg);
         }
         return true;
      }
      else
      {         
         $msg = 'no decm. hitCounter, because of ZERO hits in article/hits] =' .$id .'/'.$hits;
//       echo '<br />' .$msg; 
         if ( $log_active )
         {
            JLog::add($msg);
         }
         return false;
      }
   }
   
   public function logHitCounter($log_active,$id,$nr)
   {  
      $db =& JFactory::getDBO();
//      $query = $db->getQuery(true);
//      $query->select('hits');
//      $query->from('#__content'); 
//      $query->where('id = ' .$id);   //put your condition here 
      $query = "SELECT hits FROM #__content WHERE id=" .$id;    
      $db->setQuery($query);

      if ( $db->getErrorNum() ) 
      {
         $msg = $db->getErrorMsg();
         
//          echo $msg;
         if ( $log_active )
         {
            JLog::add($msg);
         } 
         return false;
      }
      //echo $db->getQuery();exit;//SQL query string  
      //check if error
/*      if ($db->getErrorNum()) {
        echo $db->getErrorMsg();
        return;
      }
*/      
       $hits =  $db->loadResult();

       $msg = '- db-logHitCounter[id/hits] = ' .$id .'/' .$hits .'[ ' .$nr .']';
//     echo '<br />' .$msg;
       if ( $log_active )
       {
          JLog::add($msg);
       } 
          
      return $hits;
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

      if( !empty($custom_bots) )
      {
         /**************************************************
         * prepare the array and merge with any custom bots
         ***************************************************/        
         $bots = array_map('strtolower', array_unique(array_merge($bots, $custom_bots)));
         natcasesort($bots);
      }
      
//   echo '<br />' .'Bot/User-Agent=' .$user_agent; 

// http://phpforum.de/forum/showpost.php?p=1445078&postcount=9
     /**************************************************
      * check for a matching bot
      ***************************************************/

    for($i=0; $i <= count($bots); $i++)
    {
      if ( stristr($user_agent, $bots[$i]) ) 
      {

       $msg = 'Bot - ' .$bots[$i] .' - found';
//     echo '<br />' .$msg;      
         if ( $this->params->get('log_active') )
         {
            JLog::add($msg);
         }
         return true;        
      }
    } 
    return false; 
/*
// This regex-Version makes no matches! - don't know why?
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

/* 
 * Helper for logging
 * @package    Notes
 * @subpackage com_notes
 * see: https://docs.joomla.org/Using_JLog
 */

jimport('joomla.log.log');

    // Add the logger.
    // Set the name of the log file
    // (optional) you can change the directory 

    $options = array('text_file'      => 'plg_stophitcounts-log',            
                     'text_file_path' => 'administrator/logs');

// Pass the array of configuration options    

JLog::addLogger($options, JLog::INFO);
?>
