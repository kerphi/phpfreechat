#!/usr/bin/env vows
/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

var vows = require('vows'),
    assert = require('assert'),
    request = require('request'),
    async = require('async'),
    fs = require('fs'),
    querystring = require('querystring'),
    baseurl = 'http://127.0.0.1:32773';

try {
  baseurl = fs.readFileSync(__dirname + '/../../serverurl', 'utf8').trim();
} catch (err) {}

vows.describe('User reload his window')

.addBatch({
  'user1 and user2 join a channel, user1 reload his window after 1 second, user2 check his messages each second': {
    topic: function () {
      var self = this;
      var tmsg = [];
      var j1 = request.jar();
      var j2 = request.jar();
      var cid = 'cidclose2';
      var userdata1 = {};
      var userdata2 = {};
      
      function user1auth(flag) {
//        console.log('0');
        request({
          method: 'GET',
          url: baseurl + '/server/auth',
          headers: { 'Pfc-Authorization': 'Basic '
                    + new Buffer("userreload1:password").toString('base64') },
          jar: j1,
        }, function (err, res, body) {
          if (!flag) {
            userdata1 = JSON.parse(body);
          }
          user1join(flag);
        });
      }

      function user1join(flag) {
//        console.log('1');
        // user1 join
        request({
          method: 'PUT',
          url: baseurl + '/server/channels/' + cid + '/users/' + userdata1.id,
          jar: j1,
        }, function (err, res, body) {
          if (!flag) {
            user2auth();
          } else {
            user1getmsg();
          }
        });
      }

      function user1reload() {
//        console.log('2');
        // user1 close
        request({
          method: 'PUT',
          url: baseurl + '/server/users/' + userdata1.id + '/closed',
          jar: j2,
          json: 1,
        }, function (err, res, body) {
          user1auth(true); // auth, join, getmsg
        });
      }
      
      function user2auth() {
//        console.log('3');
        request({
          method: 'GET',
          url: baseurl + '/server/auth',
          headers: { 'Pfc-Authorization': 'Basic '
                      + new Buffer("userreload2:password").toString('base64') },
          jar: j2,
        }, function (err, res, body) {
          userdata2 = JSON.parse(body);
          user2join();
        });
      }

      function user2join() {
//        console.log('4');
        // user2 join
        request({
          method: 'PUT',
          url: baseurl + '/server/channels/' + cid + '/users/' + userdata2.id,
          jar: j2,
        }, function (err, res, body) {
          user1reload();
          user2getmsg();
        });
      }

      var nb_check_u2 = 3;
      function user2getmsg() {
//        console.log('6');
        setTimeout(function () {
          request({
            method: 'GET',
            url: baseurl + '/server/users/' + userdata2.id + '/pending/',
            jar: j2,
          }, function (err, res, body) {
            tmsg = tmsg.concat(JSON.parse(body));
            if (nb_check_u2--) {
              user2getmsg();
            } else {
              self.callback(null, tmsg, userdata1, userdata2);
            }
          });
        }, 1000);
      }
      
      var nb_check_u1 = 3;
      function user1getmsg() {
//        console.log('7');
        setTimeout(function () {
          request({
            method: 'GET',
            url: baseurl + '/server/users/' + userdata1.id + '/pending/',
            jar: j1,
          }, function (err, res, body) {
            tmsg = tmsg.concat(JSON.parse(body));
            if (nb_check_u1--) {
              user1getmsg();
            }
          });
        }, 1000);
      }
      
      user1auth(); // run the topic
    },
    
    'server should not tell that user1 has been disconnected (because user1 just reload his window)':
    function (error, tmsg, userdata1, userdata2) {
      tmsg.forEach(function (m) {
        assert.notEqual(m.type, 'close');
        assert.notEqual(m.type, 'timeout');
      });
    },
  },
})


.export(module);
