<?php
/**
 * pfcglobalconfig.class.php
 *
 * Copyright © 2006 Stephane Gully <stephane.gully@gmail.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details. 
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301  USA
 */

require_once dirname(__FILE__)."/pfctools.php";
require_once dirname(__FILE__)."/pfci18n.class.php";
require_once dirname(__FILE__).'/pfccontainer.class.php';

/**
 * pfcGlobalConfig stock configuration data into sessions and initialize some stuff
 *
 * @author Stephane Gully <stephane.gully@gmail.com>
 */
class pfcGlobalConfig
{
  // ------------------
  // public parameters
  // ------------------

  /**
   * <p>This is the only mandatory parameter used to identify the chat server.
   * You can compare it to the server ip/host like on an IRC server.
   * If you don't know what to write, just try : <code>$params["serverid"] = md5(__FILE__);</code></p>
   */
  var $serverid = '';
  
  /**
   * <p>Used to translate the chat text and messages. Accepted values are the <code>i18n/</code> sub directories names.
   * (By default, this is the local server language.)</p>
   */
  var $language = '';

  /**
   * <p>Set a sepcific encoding for chat labels.
   * This is really useful when the Web page embedding the chat is not UTF-8 encoded.
   * This parameter should be the same as the chat web page.
   * Could be ISO-8859-1 or anything else but it must be supported by iconv php module.
   * (Default value: UTF-8)</p>
   */
  var $output_encoding = 'UTF-8';

  /**
   * <p>If you have already identified the user (forum, portal...) you can force the user's nickname with this parameter.
   * Defining a nick will skip the "Please enter your nickname" popup.</p>
   * <p>Warning : Nicknames must be encoded in UTF-8.
   * For example, if you get nicks from a databases where they are ISO-8859-1 encoded,
   * you must convert it: <code>$params["nick"] = iconv("ISO-8859-1", "UTF-8", $bdd_nickname);</code>
   * (Of course, change the <code>$bdd_nickname</code> parameter for your needs.)</p>
   * <p>(Default value: "" - means users must choose a nickname when s/he connects.)</p>
   */
  var $nick = "";

  /**
   * <p>This is the maximum nickname length, a longer nickname is forbidden.
   * (Default value: 15)</p>
   */
  var $max_nick_len = 15;
  
  /**
   * <p>Setting this to true will forbid the user to change his/her nickname later.
   * (Default value: false)</p>
   */
  var $frozen_nick = false;

  /**
   * <p>Contains some extra data (metadata) about the user that can be used to customize the display.
   * For example: the user's gender, age, real name, etc. can be setup in order to display it in the user's info box.
   * A example for gender is : <code>$params["nickmeta"] = array('gender'=>'f');</code>
   * (Default value: empty array)</p>
   */
  var $nickmeta = array();

  /**
   * <p>Can be used to set user metadata that is only visible to admins.
   * (Default value:  <code>array('ip')</code> - means that the user's IP address is shown to admins only)</p>
   */
  var $nickmeta_private = array('ip');

  /**
   * <p>Can be used to hide keys in the final displayed whoisbox.
   * (Default value:  <code>array()</code> - means that nothing is hidden)</p>
   */
  var $nickmeta_key_to_hide = array();
  
  /**
   * <p>Set this parameter to true if you want to give admin rights to the connected user.
   * Attention : if you don't use any external registration system, all your users will be admins.
   * You have to test current user rights before setting this parameter to true.
   * (Default value: false)</p>
   */
  var $isadmin = false;

  /**
   * <p>This parameter contains a list of key/value that identify admin access.
   * The keys are the nicknames and the values are the corresponding passwords.
   * Note: The "isadmin" parameter does not depend on this variable.
   * (Default value: nick 'admin' with no password is available. Don't forget to change it.)</p>
   */
  var $admins = array("admin" => "");

  /**
   * <p>When this parameter is true, it gives admin rights to the first connected user on the server.
   * (Default value: false)</p>
   */
  var $firstisadmin  = false;
  
  /**
   * <p>Used to change the chat title that is visible just above the messages list.
   * (Default value: "My Chat")</p>
   */
  var $title = '';  

  /**
   * <p>Used to create default rooms (auto-joined at startup). It contains an array of rooms names.
   * (Default value: one room is created named "My room")</p>
   */
  var $channels = array();

  /**
   * <p>This parameter can be used to restrict channels to users.
   * If the array is empty, it allows users to create their own channels.
   * (Default value: empty array)</p>
   */
  var $frozen_channels = array();

  /**
   * <p>The maximum number of allowed channels for each user.
   * (Default value: 10)</p>
   */
  var $max_channels = 10;

  /**
   * <p>This array contains the nicknames list you want to initiate a private message at chat loading.
   * Of course, the listed nicknames should be online or it will just be ignored.
   * (Default value: empty array)</p>
   */
  var $privmsg = array();

  /**
   * <p>This is the maximum number of private message allowed at the same time for one user.
   * (Default value: 5)</p>
   */
  var $max_privmsg = 5;
  
  /**
   * <p>This is the time to wait between two refreshes.
   * A refresh is an HTTP request which asks the server if there are new messages to display.
   * If there are no new messages, then an empty HTTP response is returned.
   * This parameter will be dynamically changed depending on the chat activity, see refresh_delay_steps
   * parameter for more information.
   * (Default value: 2000 it means 2000 ms or 2 seconds)</p>
   */
  var $refresh_delay = 2000;

  /**
   * <p>This parameter is used to control the refresh_delay value dynamically.
   * More the chat is active, more the refresh_delay is low, that means more the chat is responsive.
   * The first parameter is a refresh delay value, the second is a time inactivity boundary etc ...
   * (Default value: array(2000,20000,3000,60000 ... that means: start with 2s delay after 20s of inactivity,
   *  3s delay after 60s of inactivity ...)</p>
   */
  var $refresh_delay_steps = array(2000,20000,3000,30000,5000,60000,8000,300000,15000,600000,30000);

  /**
   * <p>This is the time of inactivity to wait before considering a user is disconnected (in milliseconds).
   * A user is inactive only if s/he closed his/her chat window. A user with an open chat window
   * is not inactive because s/he sends each <code>refresh_delay</code> an HTTP request.
   * (Default value: 35000 it means 35000 ms or 35 seconds)</p>
   */
  var $timeout = 35000;
  
  /**
   * When this parameter is true, all the chatters will be redirected
   * to the url indicated by the <code>lockurl</code> parameter.
   * (Default value: false)</p>
   */
  var $islocked = false;

  /**
   * This url is used when <code>islocked</code> parameter is true.
   * The users will be redirected (http redirect) to this url.
   * (Default value: http://www.phpfreechat.net)
   */
  var $lockurl = 'http://www.phpfreechat.net';
  
  /**
   * <p>Contains the list of proxies to ingore.
   * For example: append 'censor' to the list to disable words censoring.
   * The list of system proxies can be found in src/proxies/.
   * Attention: 'checktimeout' and 'checknickchange' proxies should not be disabled or the chat will not work anymore.
   * (Default value: empty array - no proxies will be skipped)</p>
   */
  var $skip_proxies = array();

  /**
   * <p>This array contains the proxies that will be handled just before to process a command
   * and just after the system proxies.
   * You can use this array to execute your own proxy.
   * (Default value: empty array)</p>
   */
  var $post_proxies = array();
  
  /**
   * <p>This array ocntains the proxies that will be handled just before system proxies.
   * You can use this array to execute your own proxy.
   * (Default value: empty array)</p>
   */
  var $pre_proxies = array();

  /**
   * <p>Contains the proxies configuration.
   * TODO: explain the possible values for each proxies.</p>
   */
  var $proxies_cfg = array("auth"    => array(),
                           "noflood" => array("charlimit" => 450,
                                              "msglimit"  => 10,
                                              "delay"     => 5),
                           "censor"  => array("words"     => array("fuck","sex","bitch"),
                                              "replaceby" => "*",
                                              "regex"     => false),
                           "log"     => array("path" => ""));

  /**
   * <p>A custom proxies path. Used to easily plugin your own proxy to the chat without modifying the code.
   * (Default value: empty path)</p>
   */
  var $proxies_path = '';

  /**
   * <p>Contains the default proxies location.
   * Do not change this parameter if you don't know what you are doing.
   * If you try to add your own proxy, check the <code>proxies_path</code> parameter.
   * (Default value: <code>dirname(__FILE__).'/proxies'</code>)</p>
   */
  var $proxies_path_default = '';

  /**
   * <p>This parameter indicates your own commands directory location.
   * The chat uses commands to communicate between client and server.
   * As an example, when a message is sent, the <code>/send your message</code> command is used,
   * when a nickname is changed, the <code>/nick newnickname</code> command is used.
   * To create a new command, you have to write it and indicate in this parameter where it is located.
   * (Default value: empty string - means no custom command path is used)</p>
   */
  var $cmd_path = '';
  
  /**
   * <p>Contains the default command path used by the system.
   * Do not change this parameter if you don't know what you are doing.
   * If you try to add your own command, check the <code>cmd_path</code> parameter.
   * (Default value: <code>dirname(__FILE__).'/commands'</code>)</p>
   */
  var $cmd_path_default = '';

  /**
   * <p>This is the maximum message length in characters. A longer message is forbidden.
   * (Default value: 400)</p>
   */
  var $max_text_len = 400;
  
  /**
   * <p>This is the number of messages kept in the history.
   * This is what you see when you reload the chat.
   * The number of messages s/he can see is defined by this parameter.
   * (Default value: 20</p>
   */
  var $max_msg = 20;

  /**
   * <p>The maximum number of lines displayed in the window.
   * Old lines will be deleted to save browser's memory on clients.
   * Default value: 150)</p>
   */
  var $max_displayed_lines = 150;

  /**
   * <p>Setting this to true will send a <code>/quit</code> command when the user closes his/her window.
   * (NOTE: Doesn't work on Firefox).
   * This parameter isn't true by default because on IE and Konqueror/Safari,
   * reloading the window (F5) will generate the same event as closing the window which can be annoying.
   * (Default value: false)</p>
   */
  var $quit_on_closedwindow = true;

  /**
   * <p>Setting this to true will give the focus to the input text box when connecting to the chat.
   * It can be useful not to touch the focus when integrating the chat into an existing website
   * because when the focus is changed, the viewport follows the focus location.
   * (Default value: true)</p>
   */
  var $focus_on_connect = true;

  /**
   * <p>Setting this to false will oblige user to click on the connect button if s/he wants to chat.
   * (Default value: true - a connection to the chat is automaticaly performed)</p>
   */
  var $connect_at_startup = true;

  /**
   * <p>Setting it to true will start the chat minimized.
   * (Default value: false)</p>
   */
  var $start_minimized = false;

  /**
   * <p>Height of the chat area.
   * (Default value: "440px")</p>
   */
  var $height = "440px";

  /**
   * <p><ul><li>Setting this to 0 will show nothing.</li>
   * <li>Setting it to 1 will show nicknames changes.</li>
   * <li>Setting it to 2 will show connect/disconnect notifications.</li>
   * <li>Setting it to 4 will show kick/ban notifications.</li>
   * <li>Setting it to 7 (1+2+4) will show all the notifications.</li></ul>
   * (Default value: 7)</p>
   */
  var $shownotice = 7;

  /**
   * <p>Setting it to false will disable nickname colorization.
   * (Default value: true)</p>
   **/
  var $nickmarker = true;

  /**
   * <p>Setting it to false will hide the date/hour column.
   * (Default value: true)</p>
   */
  var $clock = true;

  /**
   * <p>Setting it to false will start the chat without sound notifications.
   * (Default value: true)</p>
   */
  var $startwithsound = true;

  /**
   * <p>Setting it to true will open all links in a new window.
   * (Default value: true)</p>
   */
  var $openlinknewwindow = true;

  /**
   * <p>Setting it to false will disable the window title notification.
   * When a message is received and this parameter is true, the window title is modified with <code>[n]</code>
   * (n is the number of new posted messages).
   * (Default value: true)</p>
   */
  var $notify_window = true;

  /**
   * <p>Setting it to true will shorten long URLs entered by users in the chat area.
   * (Default value: true)</p>
   */
  var $short_url = true;

  /**
   * <p>Final width of the shortened URL in characters.  (This includes the elipsis on shortened URLs.)
   * This parameter is taken into account only when <code>short_url</code> is true.
   * (Default value: 40)</p>
   */
  var $short_url_width = 40;

  /**
   * <p>Used to show/hide the ping information near the phpfreechat linkback logo.
   * The ping is the time between a client request and a server response.
   * More the ping is low, faster the chat is responding.
   * (Default value: true)</p>
   */
  var $display_ping = true; 
  
  /**
   * <p>Used to hide the phpfreechat linkback logo.
   * Be sure that you are conform to the <a href="http://www.phpfreechat.net/license.en.html">license page</a>
   * before setting this to false!
   * (Default value: true)</p>
   */
  var $display_pfc_logo = true; 

  /**
   * <p>Used to show/hide the images in the channels and pv tabs.
   * (Default value: true)</p>
   */
  var $displaytabimage = true;

  /**
   * <p>Used to show/hide the close button in the channels tabs.
   * (Default value: true)</p>
   */
  var $displaytabclosebutton = true;

  /**
   * <p>Used to show/hide online users list at startup.
   * (Default value: true)</p>
   */
  var $showwhosonline = true;

  /**
   * <p>Used to show/hide the smiley selector at startup.
   * (Default value: true)</p>
   */
  var $showsmileys = true;

  /**
   * <p>Used to show/hide the showwhosonline button.
   * (Default value: true)</p>
   */
  var $btn_sh_whosonline = true;

  /**
   * <p>Used to show/hide the showsmileys button.
   * (Default value: true)</p>
   */
  var $btn_sh_smileys = true;

  /**
   * <p>This is the list of colors that will appears into the bbcode palette.
   * (Default value: contains an array of basic colors: '#FFFFFF', '#000000', ...)</p>
   */
  var $bbcode_colorlist = array('#FFFFFF',
                                '#000000',
                                '#000055',
                                '#008000',
                                '#FF0000',
                                '#800000',
                                '#800080',
                                '#FF5500',
                                '#FFFF00',
                                '#00FF00',
                                '#008080',
                                '#00FFFF',
                                '#0000FF',
                                '#FF00FF',
                                '#7F7F7F',
                                '#D2D2D2');

  /**
   * <p>This is the list of colors that will be used to automaticaly and randomly colorize the nicknames in the chat.
   * (Default value: contains an array of basic colors: '#CCCCCC','#000000')</p>
   */
  var $nickname_colorlist = array('#CCCCCC',
                                  '#000000',
                                  '#3636B2',
                                  '#2A8C2A',
                                  '#C33B3B',
                                  '#C73232',
                                  '#80267F',
                                  '#66361F',
                                  '#D9A641',
                                  '#3DCC3D',
                                  '#1A5555',
                                  '#2F8C74',
                                  '#4545E6',
                                  '#B037B0',
                                  '#4C4C4C',
                                  '#959595');

  /**
   * <p>This parameter specifies which theme the chat will use.
   * A theme is a package that makes it possible to completly change the chat appearance (CSS) and the chat dynamics (JS)
   * You can find official themes in the <code>themes/</code> directory on your local phpfreechat distribution.
   * (Default value: 'default')</p>
   */
  var $theme = 'default';

  /**
   * <p>Indicates where the themes are located.
   * Use this parameter if you want to store your own theme in a special location.
   * (by default the same as <code>theme_default_path</code>)</p>
   */
  var $theme_path = '';

  /**
   * <p>This url indicates the <code>theme_path</code> location.
   * It will be used by the browser to load theme resources : images, css, js.
   * If this parameter is not indicated, the themes will be copied to <code>data_public_path/themes</code>
   * and this parameter value will be set to <code>data_public_url/theme</code>.
   * (Default value: '')</p>
   */
  var $theme_url = '';

  /**
   * <p>Indicate where the official pfc default theme is located.
   * Do not change this parameter if you don't know what you are doing.
   * If you try to add your own theme, check the <code>theme_path</code> parameter.
   * (Default value: '' - empty string means <code>dirname(__FILE__).'/../themes'</code> is used automatically)</p>
   */
  var $theme_default_path = '';

  /**
   * <p>This url indicates the <code>theme_default_path</code> location.
   * Do not change this parameter if you don't know what you are doing.
   * If you try to add your own theme, check the <code>theme_path</code> parameter.
   * (Default value: the theme is copied into <code>data_public_path/themes</code>
   * and this parameter will be set to <code>data_public_url/theme</code>)</p>
   */
  var $theme_default_url = '';

  /**
   * <p>Used to specify the chat container (chat database).
   * Accepted containers are : File and Mysql (maybe others in the future).
   * (Default value: 'File')</p>
   */
  var $container_type = 'File';

  /**
   * <p>Used to specify the script that will handle asynchronous requests.
   * Very useful when the chat (client) script is resource consuming (ex: forum or portal chat integration).
   * (Default value: '' - means this parameter is automatically calculated)</p>
   */
  var $server_script_path = '';
  
  /**
   * <p>This url indicates the <code>server_script_path</code>.
   * It will be used to do AJAX requests from the browser. Therefore, this URL should be a browsable public url.
   * This parameter is useful when using URL rewriting because basic auto-calculation will fail.
   * (Default value: '' - means this parameter is automatically calculated)</p>
   */
  var $server_script_url = '';

  /**
   * <p>Used to specify the script path which first displays the chat.
   * This path will be used to calculate relatives paths for resources: javascript lib and images.
   * Useful when the php configuration is uncommon. This option can be used to force the automatic detection process.
   * (Default value: '' - means this parameter is automatically calculated)</p>
   */
  var $client_script_path = '';

  /**
   * <p>Used to store private data like cache, logs and chat history.
   * Tip: you can optimize your chat performances,
   * see <a href="http://www.phpfreechat.net/faq.en.html#tmpfs">this FAQ entry</a>.
   * (Default value: '' - means <code>dirname(__FILE__)."/../data/private"</code> is used automatically)</p>
   */
  var $data_private_path = '';

  /**
   * This path must be reachable by your web server.
   * Javascript and every resources (theme) files will be stored here.
   * (Default value: '' - means dirname(__FILE__)."/../data/public" is used automatically)
   */
  var $data_public_path = '';

  /**
   * This URL should link to the <code>data_private_path</code> directory so that
   * the clients' browsers will be able to load needed javascript files and theme resources.
   * It can be useful when url rewriting is done on the server.
   * (Default value: '' - means this parameter is automatically calculated from <code>data_private_path</code>)
   */
  var $data_public_url = '';

  /**
   * <p>This is the prototype javascript library URL.
   * Use this parameter to use your external library.
   * (Default value: '' - means <code>data/js/prototype.js</code> is used automatically)</p>
   */
  var $prototypejs_url = '';
  
  /**
   * <p>When debug is true, some traces will be shown on the chat clients
   * (Default value: false)</p>
   */
  var $debug = false;

  /**
   * <p>Can be used to setup the chat time zone.
   * It is the difference in seconds between chat clock and server clock.
   * (Default value: 0)</p>
   */
  var $time_offset = 0;
  
  /**
   * <p>How to display the dates in the chat.
   * (Default value: <code>'d/m/Y'</code>)</p>
   */
  var $date_format = 'd/m/Y';

  /**
   * <p>How to display the time in the chat
   * (Default value: <code>'H:i:s'</code>)</p>
   */
  var $time_format = 'H:i:s';
  
  /**
   * <p>This parameter is useful when your chat server is behind a reverse proxy that
   * forwards client ip address in HTTP_X_FORWARDED_FOR http header.
   * Some discutions about this parameter are available
   * on <a href="http://www.phpfreechat.net/forum/viewtopic.php?id=1344">the forum</a>.
   * (Default value: false)</p>
   */
  var $get_ip_from_xforwardedfor = false;

  /**
   * <p>Most of the chat parameters are stored in a internal cache for performances issues.
   * It means that for all the clients the chat will have the same parameters. However sometime you need
   * to customize some parameters for each clients.
   * For example: the 'language' parameter could depends on the chatter profil so it could interesting to
   * ignore the cache for this parameter.
   * The 'dyn_params' contains the parameters that need to be dynamic (not stored in the cache).
   * (Default value: array())</p>
   */
  var $dyn_params = array();
  
  // ------------------
  // private parameters
  // ------------------
  /**
   * Contains proxies to execute on each commands.
   * Filled in the init step, this parameter cannot be overridden.
   */
  var $proxies              = array();

  var $smileys             = array();
  var $errors              = array();
  var $is_init             = false; // used internaly to know if the chat config is initialized
  var $version             = ''; // the phpfreechat version: taken from the 'version' file content
  
  var $_sys_proxies         = array("lock", "checktimeout", "checknickchange", "auth", "noflood", "censor", "log");
  var $_dyn_params          = array("nick","isadmin","islocked","admins","frozen_channels", "channels", "privmsg", "nickmeta","time_offset","date_format","time_format");
  var $_params_type         = array();
  var $_query_string        = '';
  
  function pfcGlobalConfig( $params = array() )
  {
    // @todo find a cleaner way to forward serverid to i18n functions
    $GLOBALS['serverid'] = isset($params['serverid']) ? $params['serverid'] : '_serverid_';
    // setup the locales for the translated messages
    pfcI18N::Init(isset($params['language']) ? $params['language'] : '');

    // check the serverid is really defined
    if (!isset($params["serverid"]))
      $this->errors[] = _pfc("'%s' parameter is mandatory by default use '%s' value", "serverid", "md5(__FILE__)");
    $this->serverid = $params["serverid"];

    // setup data_private_path because _GetCacheFile needs it
    if (!isset($params["data_private_path"]))
      $this->data_private_path = dirname(__FILE__)."/../data/private";
    else
      $this->data_private_path = $params["data_private_path"];
    
    // check if a cached configuration already exists
    // don't load parameters if the cache exists
    $cachefile = $this->_GetCacheFile();    
    if (!file_exists($cachefile))
    {
      // first of all, save our current state in order to be able to check for variable types later
      $this->_saveParamsTypes();

      if (!isset($params["data_public_path"]))
        $this->data_public_path  = dirname(__FILE__)."/../data/public";
      else
        $this->data_public_path = $params["data_public_path"];

      // if the user didn't specify the server_script_url, then remember it and
      // append QUERY_STRING to it
      if (!isset($params['server_script_url']))
        $this->_query_string = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '' ?
          '?'.$_SERVER['QUERY_STRING'] :
          '';
      
      // load users container or keep default one
      if (isset($params["container_type"]))
        $this->container_type = $params["container_type"];     
      
      // load default container's config
      $ct =& pfcContainer::Instance($this->container_type, true);
      $ct_cfg = $ct->getDefaultConfig();
      foreach( $ct_cfg as $k => $v )
      {
        $attr = "container_cfg_".$k;
        if (!isset($this->$attr))
          $this->$attr = $v;
      }
      
      // load all user's parameters which will override default ones
      foreach ( $params as $k => $v )
      {
        if (!isset($this->$k))
          $this->errors[] = _pfc("Error: undefined or obsolete parameter '%s', please correct or remove this parameter", $k);
        if (preg_match('/^_/',$k))
          $this->errors[] = _pfc("Error: '%s' is a private parameter, you are not allowed to change it", $k);
        
        if ($k == "proxies_cfg")
        {
          // don't replace all the proxy_cfg parameters, just replace the specified ones
          foreach ( $params["proxies_cfg"] as $k2 => $v2 )
          {
            if (is_array($v2))
              foreach( $v2 as $k3 => $v3)
                $this->proxies_cfg[$k2][$k3] = $v3;
            else
              $this->proxies_cfg[$k2] = $v2;
          }
        }
        else
          $this->$k = $v;
      }
    }

    // load dynamic parameter even if the config exists in the cache
    if (isset($params['dyn_params']) && is_array($params['dyn_params']))
      $this->_dyn_params = array_merge($this->_dyn_params,$params['dyn_params']);
    foreach ( $this->_dyn_params as $dp )
      if (isset($params[$dp]))
        $this->$dp = $params[$dp];

    // 'channels' is now a dynamic parameter, just check if I need to initialize it or not
    if (is_array($this->channels) &&
        count($this->channels) == 0 &&
        !isset($params['channels']))
      $this->channels = array(_pfc("My room"));
    
    // now load or save the configuration in the cache
    $this->synchronizeWithCache();

    // to be sure the container instance is initialized
    $ct =& pfcContainer::Instance($this->container_type, true);

    // This is a dirty workaround which fix a infinite loop when:
    // 'frozen_nick' is true
    // 'nick' length is > 'max_nick_len'
    $this->nick = $this->filterNickname($this->nick);
  }

  static function &Instance( $params = array(), $destroy_instance = false )
  {
    static $i;
    if ($destroy_instance)
      $i = NULL;
    else
      if (!isset($i))
        $i = new pfcGlobalConfig( $params );
    return $i;
  }
  
  /**
   * This function saves all the parameters types in order to check later if the types are ok
   */
  function _saveParamsTypes()
  {
    $vars = get_object_vars($this);
    foreach($vars as $k => $v)
    {
      if (is_string($v))                $this->_params_type["string"][]  = $k;
      else if (is_bool($v))             $this->_params_type["bool"][]    = $k;
      else if (is_array($v))            $this->_params_type["array"][]   = $k;
      else if (is_int($v) && $v>0)      $this->_params_type["positivenumeric"][] = $k;
      else $this->_params_type["misc"][] = $k;
    }
  }
  
  /**
   * Initialize the phpfreechat configuration
   * this initialisation is done once at startup then it is stored into a session cache
   */
  function init()
  {
    $ok = true;

    // check the parameters types
    $array_params = $this->_params_type["array"];
    foreach( $array_params as $ap )
    {
      if (!is_array($this->$ap))
        $this->errors[] = _pfc("'%s' parameter must be an array", $ap);
    }
    $numerical_positive_params = $this->_params_type["positivenumeric"];
    foreach( $numerical_positive_params as $npp )
    {
      if (!is_int($this->$npp) || $this->$npp < 0)
        $this->errors[] = _pfc("'%s' parameter must be a positive number", $npp);
    }
    $boolean_params = $this->_params_type["bool"];
    foreach( $boolean_params as $bp )
    {
      if (!is_bool($this->$bp))
        $this->errors[] = _pfc("'%s' parameter must be a boolean", $bp);
    }
    $string_params = $this->_params_type["string"];
    foreach( $string_params as $sp )
    {
      if (!is_string($this->$sp))
        $this->errors[] = _pfc("'%s' parameter must be a charatere string", $sp);
    }

    if ($this->title == "")           $this->title        = _pfc("My Chat");
      
    // first of all, check the used functions
    $f_list["file_get_contents"] = _pfc("You need %s", "PHP 4 >= 4.3.0 or PHP 5");
    $err_session_x = "You need PHP 4 or PHP 5";
    $f_list["session_start"]   = $err_session_x;
    $f_list["session_destroy"] = $err_session_x;
    $f_list["session_id"]      = $err_session_x;
    $f_list["session_name"]    = $err_session_x;    
    $err_preg_x = _pfc("You need %s", "PHP 3 >= 3.0.9 or PHP 4 or PHP 5");
    $f_list["preg_match"]      = $err_preg_x;
    $f_list["preg_replace"]    = $err_preg_x;
    $f_list["preg_split"]      = $err_preg_x;
    $err_ob_x = _pfc("You need %s", "PHP 4 or PHP 5");
    $f_list["ob_start"]        = $err_ob_x;
    $f_list["ob_get_contents"] = $err_ob_x;
    $f_list["ob_end_clean"]    = $err_ob_x;
    $f_list["get_object_vars"] = _pfc("You need %s", "PHP 4 or PHP 5");
    $this->errors = array_merge($this->errors, check_functions_exist($f_list));
    
    //    $this->errors = array_merge($this->errors, @test_writable_dir($this->data_public_path, "data_public_path"));
    $this->errors = array_merge($this->errors, @test_writable_dir($this->data_private_path, "data_private_path"));
    $this->errors = array_merge($this->errors, @test_writable_dir($this->data_private_path."/cache", "data_private_path/cache"));

    
    // install the public directory content
    $dir = dirname(__FILE__)."/../data/public/js";
    $dh = opendir($dir);
    while (false !== ($file = readdir($dh)))
    {
      $f_src = $dir.'/'.$file;
      $f_dst = $this->data_public_path.'/js/'.$file;
      if ($file == "." || $file == ".." || !is_file($f_src)) continue; // skip . and .. generic files
      // install js files only if the destination doesn't exists or if the destination timestamp is older than the source timestamp
      if (!file_exists($f_dst) || filemtime($f_dst) < filemtime($f_src) )
      {
        mkdir_r($this->data_public_path.'/js/');
        copy( $f_src, $f_dst );
      }
      if (!file_exists($f_dst)) $this->errors[] = _pfc("%s doesn't exist, data_public_path cannot be installed", $f_dst);
    }
    closedir($dh);


    // ---
    // test client script
    // try to find the path into server configuration
    if ($this->client_script_path == '')
      $this->client_script_path = pfc_GetScriptFilename();

    if ($this->server_script_url == '' && $this->server_script_path == '')
    {    
      $filetotest = $this->client_script_path;
      // do not take into account the url parameters
      if (preg_match("/(.*)\?(.*)/", $filetotest, $res))
        $filetotest = $res[1];
      if ( !file_exists($filetotest) )
        $this->errors[] = _pfc("%s doesn't exist", $filetotest);   
      $this->server_script_url  = './'.basename($filetotest).$this->_query_string;
    }
  
    // calculate datapublic url
    if ($this->data_public_url == "")
      $this->data_public_url = pfc_RelativePath($this->client_script_path, $this->data_public_path);

    if ($this->server_script_path == '')
      $this->server_script_path = $this->client_script_path;
    
    // ---
    // test server script    
    if ($this->server_script_url == '')
    {
      $filetotest = $this->server_script_path;
      // do not take into account the url parameters
      if (preg_match("/(.*)\?(.*)/",$this->server_script_path, $res))
        $filetotest = $res[1];
      if ( !file_exists($filetotest) )
        $this->errors[] = _pfc("%s doesn't exist", $filetotest);
      $this->server_script_url = pfc_RelativePath($this->client_script_path, $this->server_script_path).'/'.basename($filetotest).$this->_query_string;
    }

    // check if the theme_path parameter are correctly setup
    if ($this->theme_default_path == '' || !is_dir($this->theme_default_path))
      $this->theme_default_path = dirname(__FILE__).'/../themes';
    if ($this->theme_path == '' || !is_dir($this->theme_path))
      $this->theme_path = $this->theme_default_path;

    // If the user didn't give any theme_default_url value,
    // copy the default theme resources in a public directory
    if ($this->theme_default_url == '')
    {
      mkdir_r($this->data_public_path.'/themes/default');
      if (!is_dir($this->data_public_path.'/themes/default'))
        $this->errors[] = _pfc("cannot create %s", $this->data_public_path.'/themes/default');
      else
      {
        $ret = copy_r( dirname(__FILE__).'/../themes/default',
                       $this->data_public_path.'/themes/default' );
        if (!$ret)
          $this->errors[] = _pfc("cannot copy %s in %s",
                                 dirname(__FILE__).'/../themes/default',
                                 $this->data_public_path.'/themes/default');
      }
      $this->theme_default_url = $this->data_public_url.'/themes';
    }
    if ($this->theme_url == '')
    {
      mkdir_r($this->data_public_path.'/themes/'.$this->theme);
      if (!is_dir($this->data_public_path.'/themes/'.$this->theme))
        $this->errors[] = _pfc("cannot create %s", $this->data_public_path.'/themes/'.$this->theme);
      else
      {
        $ret = copy_r( $this->theme_path.'/'.$this->theme,
                       $this->data_public_path.'/themes/'.$this->theme );
        if (!$ret)
          $this->errors[] = _pfc("cannot copy %s in %s",
                                 $this->theme_path.'/'.$this->theme,
                                 $this->data_public_path.'/themes/'.$this->theme);
      }      
      $this->theme_url = $this->data_public_url.'/themes';
    }

    // if the user do not have an existing prototype.js library, we use the embeded one
    if ($this->prototypejs_url == '') $this->prototypejs_url = $this->data_public_url.'/js/prototype.js';

    // ---
    // run specific container initialisation
    $ct =& pfcContainer::Instance();
    $ct_errors = $ct->init($this);
    $this->errors = array_merge($this->errors, $ct_errors);
    
    // check if the wanted language is known
    $lg_list = pfcI18N::GetAcceptedLanguage();
    if ( $this->language != "" && !in_array($this->language, $lg_list) )
      $this->errors[] = _pfc("'%s' parameter is not valid. Available values are : '%s'", "language", implode(", ", $lg_list));

    // calculate the proxies chaine
    $this->proxies = array();
    foreach($this->pre_proxies as $px)
    {
      if (!in_array($px,$this->skip_proxies) && !in_array($px,$this->proxies))
        $this->proxies[] = $px;
        
    }
    foreach($this->_sys_proxies as $px)
    {
      if (!in_array($px,$this->skip_proxies) && !in_array($px,$this->proxies))
        $this->proxies[] = $px;
        
    }
    foreach($this->post_proxies as $px)
    {
      if (!in_array($px,$this->skip_proxies) && !in_array($px,$this->proxies))
        $this->proxies[] = $px;
        
    }

    if (in_array('log',$this->proxies)) {
      // test the LOCK_EX feature because the log proxy needs to write in a file
      $filename = $this->data_private_path.'/filemtime2.test';
      if (is_writable(dirname($filename)))
      {
        $data1 = time();
        file_put_contents($filename, $data1, LOCK_EX);
        $data2 = file_get_contents($filename);
        if ($data1 != $data2) {
              unset($this->proxies[array_search('log',$this->proxies)]);
        }
      }
    }

    // save the proxies path
    $this->proxies_path_default = dirname(__FILE__).'/proxies';
    // check the customized proxies path
    if ($this->proxies_path != '' && !is_dir($this->proxies_path))
      $this->errors[] = _pfc("'%s' directory doesn't exist", $this->proxies_path);
    if ($this->proxies_path == '') $this->proxies_path = $this->proxies_path_default;
    
    // save the commands path
    $this->cmd_path_default = dirname(__FILE__).'/commands';
    if ($this->cmd_path == '') $this->cmd_path = $this->cmd_path_default;
        
    // load smileys from file
    $this->loadSmileyTheme();
    
    // load version number from file
    $this->version = trim(file_get_contents(dirname(__FILE__)."/../version.txt"));
    
    $this->is_init = (count($this->errors) == 0);
  }
  
  function isInit()
  {
    return $this->is_init;
  }
  
  function &getErrors()
  {
    return $this->errors;
  }

  function loadSmileyTheme()
  {
    $theme = file($this->getFilePathFromTheme("smileys/theme.txt"));
    $result = array();
    foreach($theme as $line)
    {
      $line = trim($line);
      if (preg_match("/^#.*/",$line))
        continue;
      else if (preg_match("/([a-z_\-0-9\.]+)(.*)$/i",$line,$res))
      {
        $smiley_file = 'smileys/'.$res[1];
        $smiley_str = trim($res[2])."\n";
        $smiley_str = str_replace("\n", "", $smiley_str);
        $smiley_str = str_replace("\t", " ", $smiley_str);
        $smiley_str_tab = explode(" ", $smiley_str);
        foreach($smiley_str_tab as $str)
          $result[$smiley_file][] = htmlspecialchars(addslashes($str));
      }
    }
    $this->smileys =& $result;
  }

  function getId()
  {
    return $this->serverid;
  }  

  function _GetCacheFile($serverid = "", $data_private_path = "")
  {
    if ($serverid == '')          $serverid = $this->getId();
    if ($data_private_path == '') $data_private_path = $this->data_private_path;
    return $data_private_path.'/cache/'.$serverid.'.php';
  }
  
  function destroyCache()
  {
    $cachefile = $this->_GetCacheFile();
    if (!file_exists($cachefile))
      return false;
    $this->is_init = false;
    // destroy the cache lock file
    $cachefile_lock = $cachefile."_lock";
    if (file_exists($cachefile_lock)) @unlink($cachefile_lock);
    // destroy the cache file
    return @unlink($cachefile);
  }
  
  /**
   * Save the pfcConfig object into cache if it doesn't exists yet
   * else restore the old pfcConfig object
   */
  function synchronizeWithCache()
  {
    $cachefile = $this->_GetCacheFile();
    $cachefile_lock = $cachefile."_lock";

    if (file_exists($cachefile))
    {
      // if a cache file exists, remove the lock file because config has been succesfully stored
      if (file_exists($cachefile_lock)) @unlink($cachefile_lock);

      include $cachefile;
      foreach($pfc_conf as $key => $val)
        // the dynamics parameters must not be cached
        if (!in_array($key,$this->_dyn_params))
          $this->$key = $val;

      return true; // synchronized
    }
    else
    {
      if (file_exists($cachefile_lock))
      {
        // delete too old lockfiles (more than 15 seconds)
        $locktime = filemtime($cachefile_lock);
        if ($locktime+15 < time())
          unlink($cachefile_lock);
        else
          return false; // do nothing if the lock file exists
      }
      else
        @touch($cachefile_lock); // create the lockfile
      
      if (!$this->isInit())
        $this->init();
      $errors =& $this->getErrors();
      if (count($errors) == 0)
      {
      // save the validated config in cache
      $this->saveInCache();
      }
      else
        @unlink($cachefile_lock); // destroy the lock file for the next attempt
      return false; // new cache created
    }
  }
  function saveInCache()
  {
    $cachefile = $this->_GetCacheFile();
    $data = '<?php ';

    $conf = get_object_vars($this);
    $keys = array_keys($conf);
    foreach($keys as $k)
      if (preg_match('/^_.*/',$k))
        unset($conf[$k]);

    // remove dynamic parameters
    foreach($this->_dyn_params as $k)
      unset($conf[$k]);

    $data .= '$pfc_conf = '.var_export($conf,true).";\n";
    $data .= '?>';
    
    file_put_contents($cachefile, $data/*serialize(get_object_vars($this))*/);
  }

  function isDefaultFile($file)
  {
    $fexists1 = file_exists($this->theme_path."/default/".$file);
    $fexists2 = file_exists($this->theme_path."/".$this->theme."/".$file);
    return ($this->theme == "default" ? $fexists1 : !$fexists2);
  }

  function getFilePathFromTheme($file)
  {
    if (file_exists($this->theme_path."/".$this->theme."/".$file))
      return $this->theme_path."/".$this->theme."/".$file;
    else
      if (file_exists($this->theme_default_path."/default/".$file))
        return $this->theme_default_path."/default/".$file;
      else
      {
        $this->destroyCache();
        die(_pfc("Error: '%s' could not be found, please check your themepath '%s' and your theme '%s' are correct", $file, $this->theme_path, $this->theme));
      }
  }

  function getFileUrlFromTheme($file)
  {
    if (file_exists($this->theme_path.'/'.$this->theme.'/'.$file))
      return $this->theme_url.'/'.$this->theme.'/'.$file;
    else
      if (file_exists($this->theme_default_path.'/default/'.$file))
        return $this->theme_default_url.'/default/'.$file;
      else
        return 'notfound';
  }


  function filterNickname($nickname)
  {
    $nickname = trim($nickname);
    require_once dirname(__FILE__)."/../lib/utf8/utf8_substr.php";
    $nickname = (string)utf8_substr($nickname, 0, $this->max_nick_len);
    return $nickname;
  }
}

?>
