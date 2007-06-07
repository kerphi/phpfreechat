    // Cookie handling 
    var Cookie =
    {
        read: function (name)
        {
      		  // Work around for Firefox when 'HTTP only' cookies are in use
            if (typeof(document.cookie) != "string" && navigator.product == "Gecko") delete HTMLDocument.prototype.cookie;

            var arrCookies = document.cookie.split ('; ');
            for (var i=0; i<arrCookies.length; i++)
            {
                var arrCookie = arrCookies[i].split ('=');
                
                if (arrCookie[0] == name)
                {
                    return decodeURIComponent (arrCookie[1]);
                }
            }
            return false;
        },
    
        write: function (name, value, expires, path)
        {
      		  // Work around for Firefox when 'HTTP only' cookies are in use
            if (typeof(document.cookie) != "string" && navigator.product == "Gecko") delete HTMLDocument.prototype.cookie;

            if (expires)
            {
                var date = new Date ();
                date.setTime (date.getTime () + (((((expires * 24) * 60) * 60) * 1000)));
                expires = '; expires=' + date.toGMTString ();
            }
            else expires = '';
    
            if (!path) path = '/';
    
            document.cookie = name+'='+encodeURIComponent (value)+expires+'; path='+path;
        },
    
        remove: function (name)
        {
            this.write (name, '', -1);
        }
    }
    
    // Detects if can set a cookie in the browser
    function browserSupportsCookies()
    {
        Cookie.write('cookiesEnabled', 1);
        var boolCookiesEnabled = Cookie.read('cookiesEnabled');
        Cookie.remove('cookiesEnabled');
        if (boolCookiesEnabled != 1)
        {
            return false;
        }
        return true;
    }
    
    // Detects if the browser supports Ajax 
    function browserSupportsAjax()
    {
        if (typeof XMLHttpRequest == "undefined" && typeof ActiveXObject == "undefined" && window.createRequest == "undefined")
        {
            return false;
        }
        return true
    }
    
    // Detects if the browser can use ActiveX if necessary
    function ActiveXEnabledOrUnnecessary ()
    {
        if (typeof ActiveXObject != "undefined")
        {
            var xhr = null;
            try{
                xhr=new ActiveXObject("Msxml2.XMLHTTP");
            }catch (e){
                try{
                    xhr=new ActiveXObject("Microsoft.XMLHTTP");
                }catch (e2){
                    try{
                        xhr=new ActiveXObject("Msxml2.XMLHTTP.4.0");
                    }catch (e3){
                        xhr=null;
                    }
                }
            }
            if (xhr == null)
            {
                return false
            }
        }
        
        return true;
    }