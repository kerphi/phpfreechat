  <img id="pfc_minmax" onclick="pfc.swap_minimize_maximize()" src="<?php echo $c->getFileUrlFromTheme('images/'.($start_minimized?'maximize':'minimize').'.gif'); ?>" alt=""/>
  <h2 id="pfc_title"><?php echo $title; ?></h2>
 
  <div id="pfc_content_expandable">                

  <div id="pfc_channels">
    <ul id="pfc_channels_list"></ul>
    <div id="pfc_channels_content"></div>
  </div>

  <div id="pfc_input_container">

    <table style="margin:0;padding:0;border-collapse:collapse;">
      <tbody>
      <tr>
      <td class="pfc_td1">
        <p id="pfc_handle"
           <?php if (! $frozen_nick) {
             echo ' title="' . _pfc("Enter your nickname here") . '"' 
               . ' onclick="pfc.askNick(\'\')"'
               . ' style="cursor: pointer"';
           }
           ?>
           ><?php echo phpFreeChat::FilterSpecialChar($u->nick); ?></p>      
      </td>
      <td class="pfc_td2">
        <input type="text"
               id="pfc_words"
               title="<?php echo _pfc("Enter your message here"); ?>"
               maxlength="<?php echo $max_text_len; ?>"/>
      </td>
      <td class="pfc_td3">
        <input type="button"
               id="pfc_send"
               value="<?php echo _pfc("Send"); ?>"
               title="<?php echo _pfc("Click here to send your message"); ?>"
               onclick="pfc.doSendMessage()"/>
      </td>
      </tr>
      </tbody>
    </table>

    <div id="pfc_cmd_container">           
<?php if ($display_pfc_logo) { ?>
      <a href="http://www.phpfreechat.net"
         id="pfc_logo"<?php if($openlinknewwindow) echo ' onclick="window.open(this.href,\'_blank\');return false;"'; ?>>
        <img src="http://www.phpfreechat.net/pub/logo2_80x15.png" width="80" height="15"
             alt="<?php echo _pfc("PHP FREE CHAT [powered by phpFreeChat-%s]", $version); ?>"
             title="<?php echo _pfc("PHP FREE CHAT [powered by phpFreeChat-%s]", $version); ?>" />
      </a>
<?php } ?>

<?php if ($display_ping) { ?>
      <span id="pfc_ping" title="<?php echo _pfc("Ping"); ?>"></span>
<?php } ?>

      <div class="pfc_btn">
        <img src="<?php echo $c->getFileUrlFromTheme('images/logout.gif'); ?>"
             alt="" title=""
             id="pfc_loginlogout"
             onclick="pfc.connect_disconnect()" />
      </div>

      <div class="pfc_btn">
        <img src="<?php echo $c->getFileUrlFromTheme('images/color-on.gif'); ?>"
             alt="" title=""
             id="pfc_nickmarker"
             onclick="pfc.nickmarker_swap()" />
      </div>

      <div class="pfc_btn">
        <img src="<?php echo $c->getFileUrlFromTheme('images/clock-on.gif'); ?>"
             alt="" title=""
             id="pfc_clock"
             onclick="pfc.clock_swap()" />
      </div>

      <div class="pfc_btn">
        <img src="<?php echo $c->getFileUrlFromTheme('images/sound-on.gif'); ?>"
             alt="" title=""
             id="pfc_sound"
             onclick="pfc.sound_swap()" />
      </div>

      <?php if ($c->btn_sh_smileys) { ?>
      <div class="pfc_btn">
        <img src="<?php echo $c->getFileUrlFromTheme('images/smiley-on.gif'); ?>"
        alt="" title=""
        id="pfc_showHideSmileysbtn"
        onclick="pfc.showHideSmileys()" />
      </div>
      <?php } ?>

      <?php if ($c->btn_sh_whosonline) { ?>
      <div class="pfc_btn">
        <img src="<?php echo $c->getFileUrlFromTheme('images/online-on.gif'); ?>"
             alt="" title=""
             id="pfc_showHideWhosOnlineBtn"
             onclick="pfc.showHideWhosOnline()" />
      </div>
      <?php } ?>

      <div class="pfc_btn">
        <img src="<?php echo $c->getFileUrlFromTheme('smileys/emoticon_evilgrin.png'); ?>"
             alt="Disable/enable censorship" title="Disable/enable censorship"
             id="pfc_nocensor"
             onclick="pfc.toggleCensor()" />
      </div>

 </div>

    <div id="pfc_bbcode_container">
      <div id="pfc_bt_strong_btn" class="pfc_btn">
        <img src="<?php echo $c->getFileUrlFromTheme('images/bt_strong.gif'); ?>"
             id="pfc_bt_strong"
             alt="<?php echo _pfc("Bold"); ?>"
             title="<?php echo _pfc("Bold"); ?>"
             class="pfc_bt_strong"
             onclick="pfc.insert_text('[b]','[/b]',true)" />
      </div>
      <div id="pfc_bt_italics_btn" class="pfc_btn">
        <img src="<?php echo $c->getFileUrlFromTheme('images/bt_em.gif'); ?>"
             id="pfc_bt_italics"
             alt="<?php echo _pfc("Italics"); ?>"
             title="<?php echo _pfc("Italics"); ?>"
             class="pfc_bt_italics"
             onclick="pfc.insert_text('[i]','[/i]',true)" />
      </div>
      <div id="pfc_bt_underline_btn" class="pfc_btn">
        <img src="<?php echo $c->getFileUrlFromTheme('images/bt_ins.gif'); ?>"
             id="pfc_bt_underline"
             alt="<?php echo _pfc("Underline"); ?>"
             title="<?php echo _pfc("Underline"); ?>"
             class="pfc_bt_underline"
             onclick="pfc.insert_text('[u]','[/u]',true)" />
      </div>
      <div id="pfc_bt_delete_btn" class="pfc_btn">
        <img src="<?php echo $c->getFileUrlFromTheme('images/bt_del.gif'); ?>"
             id="pfc_bt_delete"
             alt="<?php echo _pfc("Delete"); ?>"
             title="<?php echo _pfc("Delete"); ?>"
             class="pfc_bt_delete"
             onclick="pfc.insert_text('[s]','[/s]',true)" />
      </div>
<!--
      <div id="pfc_bt_mail_btn" class="pfc_btn">
        <img src="<?php echo $c->getFileUrlFromTheme('images/bt_mail.gif'); ?>"
             id="pfc_bt_mail"
             alt="<?php echo _pfc("Mail"); ?>"
             title="<?php echo _pfc("Mail"); ?>"
             class="pfc_bt_mail"
             onclick="pfc.insert_text('[email]','[/email]',true)" />
      </div>
-->
      <div id="pfc_bt_color_btn" class="pfc_btn">
        <img src="<?php echo $c->getFileUrlFromTheme('images/bt_color.gif'); ?>"
             alt="<?php echo _pfc("Color"); ?>"
             title="<?php echo _pfc("Color"); ?>"
             id="pfc_bt_color"
             class="pfc_bt_color"
             onclick="pfc.minimize_maximize('pfc_colorlist','inline')" />
      </div>
      <div id="pfc_colorlist"></div>
    </div> <!-- pfc_bbcode_container -->

  </div>

    <div id="pfc_errors"></div>

    <div id="pfc_smileys"></div>

  </div>

  <div id="pfc_sound_container"></div>
