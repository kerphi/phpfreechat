Hi,

the only changes I have made is that I have added this line in file pfcclient.js.
------------------
line  += (id % 2 == 0) ? '<div class=pfc_message1>' : '<div class=pfc_message2>';

---------------------

it is in the foor-loop in function "handleComingRequest: function( cmds )" at line 738.

mine is looking like this:
-----------------

  handleComingRequest: function( cmds )
  {
    var msg_html = $H();
    
    //alert(cmds.inspect());
    
    //    var html = '';
    for(var mid = 0; mid < cmds.length ; mid++)
    {
      var id          = cmds[mid][0];
      var date        = cmds[mid][1];
      var time        = cmds[mid][2];
      var sender      = cmds[mid][3];
      var recipientid = cmds[mid][4];
      var cmd         = cmds[mid][5];
      var param       = cmds[mid][6];
      var fromtoday   = cmds[mid][7];
      var oldmsg      = cmds[mid][8];
      
      // format and post message
      
      var line = '';
      
      //CSS-zilveer
      
      line  += (id % 2 == 0) ? '<div class=pfc_message1>' : '<div class=pfc_message2>';
------------
i will also include this file.


/regards, Isa Acar (zilveer), send me a mail if you have any questions: zilveer@gmail.com
