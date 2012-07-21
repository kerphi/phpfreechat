Work in progress...

## Options (client side):

* serverUrl [String:'../server']: where is located the pfc's server folder
* loaded [Function:null]: a callback executed when pfc's interface is totaly loaded

## Events (client side):

* pfc-loaded : triggered when pfc's interface is totaly loaded


## Routes design (server side):


* `/auth`
* `/channels/`
* `/channels/:cid/`
* `/channels/:cid/name`
* `/channels/:cid/msg/:mid` (one message in a channel)
* `/channels/:cid/users/` (list users in a channel)
* `/channels/:cid/users/:uid/` (gives info on the user)
* `/users/`
* `/users/:uid/name`
* `/users/:uid/email`
* `/users/:uid/msg/`
* `/users/:uid/msg/:mid` (private messages)