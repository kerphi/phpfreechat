/**
 * Specific javascript for the phpfreechat-mini theme
 * This script injects HTML and adjust ergonomics for the mini theme (toggle buttons ...)
 */
$(document).ready(function() {
  var pfc = phpFreeChat;
  
  // inject HTML into the generic one
  $(pfc.element).find('.pfc-content').wrap('<div class="pfc-mini-chat-wrapper" />');
  var mini_html_hook = 
          '        <div id="pfc-menu-mini-chat">'
        + '           <a href="#" class="chat"></a>'
        + '           <a href="#" class="users"></a>'
        + '           <a href="#" class="tabs"></a>'
        + '           <div class="clear"></div>'
        + '        </div>';
  $(pfc.element).find('.pfc-content').after(mini_html_hook);
  $(pfc.element).find('.pfc-messages').addClass('pfc-chat-mini');
  $(pfc.element).find('.pfc-compose').addClass('pfc-chat-mini');
  $(pfc.element).find('.pfc-ad-mobile').append('<div class="pub1"></div>');
  
  // comportement bt minichat
  $("#pfc-menu-mini-chat .chat").click(function() {
    if ($("div.pfc-content").is(":hidden")) {
      $("div.pfc-content > div").hide();
      $("div.pfc-content").show();
      $("div.pfc-content .pfc-footer").show();
      $("div.pfc-content .pfc-chat-mini").show();
      $("div.pfc-content .pfc-ad-mobile").show();
      if (pfc.modalbox.isopen) {
        $(".pfc-modal-overlay").show();
        $(".pfc-modal-box").show();
      }
      $("#pfc-menu-mini-chat a").removeClass("active");
      $(this).addClass("active");
    } else {
      if ($(this).hasClass("active")) {
        $("div.pfc-content").hide();
        $("div.pfc-content .pfc-chat-mini").hide();
        $("div.pfc-content .pfc-ad-mobile").hide();
        $(this).removeClass("active");
      } else {
        $("div.pfc-content > div").hide();
        $("div.pfc-content").show();
        $("div.pfc-content .pfc-footer").show();
        $("div.pfc-content .pfc-chat-mini").show();
        $("div.pfc-content .pfc-ad-mobile").show();
        if (pfc.modalbox.isopen) {
          $(".pfc-modal-overlay").show();
          $(".pfc-modal-box").show();
        }
        $("#pfc-menu-mini-chat a").removeClass("active");
        $(this).addClass("active");
      }
    }
  });

  $("#pfc-menu-mini-chat .users").click(function() {
    if ($("div.pfc-content").is(":hidden")) {
      $("div.pfc-content > div").hide();
      $("div.pfc-content").show();
      $("div.pfc-content .pfc-footer").show();
      $("div.pfc-content .pfc-users").show();
      $("div.pfc-content .pfc-ad-mobile").show();
      if (pfc.modalbox.isopen) {
        $(".pfc-modal-overlay").show();
        $(".pfc-modal-box").show();
      }
      $("#pfc-menu-mini-chat a").removeClass("active");
      $(this).addClass("active");
    } else {
      if ($(this).hasClass("active")) {
        $("div.pfc-content").hide();
        $("div.pfc-content .pfc-users").hide();
        $("div.pfc-content .pfc-ad-mobile").hide();
        $(this).removeClass("active");
      } else {
        $("div.pfc-content > div").hide();
        $("div.pfc-content").show();
        $("div.pfc-content .pfc-footer").show();
        $("div.pfc-content .pfc-users").show();
        $("div.pfc-content .pfc-ad-mobile").show();
        if (pfc.modalbox.isopen) {
          $(".pfc-modal-overlay").show();
          $(".pfc-modal-box").show();
        }
        $("#pfc-menu-mini-chat a").removeClass("active");
        $(this).addClass("active");
      }
    }
  });

  $("#pfc-menu-mini-chat .tabs").click(function () {
    if ($("div.pfc-content").is(":hidden")) {
      $("div.pfc-content > div").hide();
      $("div.pfc-content").show();
      $("div.pfc-content .pfc-footer").show();
      $("div.pfc-content .pfc-tabs").show();
      $("div.pfc-content .pfc-ad-mobile").show();
      if (pfc.modalbox.isopen) {
        $(".pfc-modal-overlay").show();
        $(".pfc-modal-box").show();
      }
      $("#pfc-menu-mini-chat a").removeClass("active");
      $(this).addClass("active");
    } else {
      if ($(this).hasClass("active")) {
        $("div.pfc-content").hide();
        $("div.pfc-content .pfc-users").hide();
        $("div.pfc-content .pfc-ad-mobile").hide();
        $(this).removeClass("active");
      } else {
        $("div.pfc-content > div").hide();
        $("div.pfc-content").show();
        $("div.pfc-content .pfc-footer").show();
        $("div.pfc-content .pfc-tabs").show();
        $("div.pfc-content .pfc-ad-mobile").show();
        if (pfc.modalbox.isopen) {
          $(".pfc-modal-overlay").show();
          $(".pfc-modal-box").show();
        }
        $("#pfc-menu-mini-chat a").removeClass("active");
        $(this).addClass("active");
      }
    }
  });

});