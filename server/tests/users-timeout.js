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

vows.describe('User timeout')

.addBatch({
  'user1 and user2 join a channel, user1 do nothing during 3 seconds, and user2 check his messages each second': {
    topic: function () {
      var self = this;
      var tmsg = [];
      var j1 = request.jar();
      var j2 = request.jar();
      var cid = 'cidtimeout1';
      var userdata1 = {};
      var userdata2 = {};

      // user1 auth
      request({
        method: 'GET',
        url: baseurl + '/server/auth',
        headers: { 'Pfc-Authorization': 'Basic '
                   + new Buffer("testtimeout1:password").toString('base64') },
        jar: j1,
      }, function (err, res, body) {
        userdata1 = JSON.parse(body);

        // user1 join
        request({
          method: 'PUT',
          url: baseurl + '/server/channels/' + cid + '/users/' + userdata1.id,
          jar: j1,
        }, function (err, res, body) {
          
          // this user is timouted (simulates a browser close or connection problem)
          setTimeout(function () {
            
            // check that the user has been well disconnected
            request({
              method: 'GET',
              url: baseurl + '/server/users/' + userdata1.id + '/',
              jar: j1,
            }, function (err, res, body) {
              self.callback(null, tmsg, userdata1, userdata2);
            });
            
          }, 5500);
      
          
          // then user2 auth
          request({
            method: 'GET',
            url: baseurl + '/server/auth',
            headers: { 'Pfc-Authorization': 'Basic '
                       + new Buffer("testtimeout2:password").toString('base64') },
            jar: j2,
          }, function (err, res, body) {
            userdata2 = JSON.parse(body);
            
            // user2 join
            request({
              method: 'PUT',
              url: baseurl + '/server/channels/' + cid + '/users/' + userdata2.id,
              jar: j2,
            }, function (err, res, body) {

              // user2 check pending message each seconds
              // (simulates the browser periodic refresh)
              // these requests makes the garbage collector active
              function user2readmsg() {
                setTimeout(function () {
                  request({
                    method: 'GET',
                    url: baseurl + '/server/users/' + userdata2.id + '/pending/',
                    jar: j2,
                  }, function (err, res, body) {
                    tmsg = tmsg.concat(JSON.parse(body)); // get the timeout leave message of user1
                  });
                  user2readmsg();
                }, 1000);
              }
              user2readmsg();
            });
            
          });
          
        });
      });
      
      
    },
    
    'server tells that user1 has leave because of a timeout': function (error, tmsg, userdata1, userdata2) {
      assert.lengthOf(tmsg, 1);
      assert.equal(tmsg[0].type, 'leave');
      assert.equal(tmsg[0].body, 'timeout');
      assert.equal(tmsg[0].sender, userdata1.id);
    },
  },
})

.addBatch({
  'user1 and user2 join a channel, wait for a timeout, then user1 join again and check his messages': {
    topic: function () {
      var self = this;
      var tmsg = [];
      var j1 = request.jar();
      var j2 = request.jar();
      var cid = 'cidtimeout2';
      var userdata1 = {};
      var userdata2 = {};
      
      function user1auth(flag) {
        //console.log('1');
        // user1 auth
        request({
          method: 'GET',
          url: baseurl + '/server/auth',
          headers: { 'Pfc-Authorization': 'Basic '
                    + new Buffer("testtimeout11:password").toString('base64') },
          jar: j1,
        }, function (err, res, body) {
          if (!flag) {
            userdata1 = JSON.parse(body);
          }
          user1join(flag);
        });
      }

      function user1join(flag) {
        //console.log('2');
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

      function user2auth() {
        //console.log('3');
        request({
          method: 'GET',
          url: baseurl + '/server/auth',
          headers: { 'Pfc-Authorization': 'Basic '
                      + new Buffer("testtimeout22:password").toString('base64') },
          jar: j2,
        }, function (err, res, body) {
          userdata2 = JSON.parse(body);
          user2join();
        });
      }

      function user2join() {
        //console.log('4');
        // user2 join
        request({
          method: 'PUT',
          url: baseurl + '/server/channels/' + cid + '/users/' + userdata2.id,
          jar: j2,
        }, function (err, res, body) {
          waituser1user2timeout();
        });
      }
      
      function waituser1user2timeout() {
        //console.log('5');
        // this user is timouted (simulates a browser close or connection problem)
        setTimeout(function () {
          user1auth(true); // auth again after a timeout
        }, 5500);
      }

      function user1getmsg() {
        //console.log('6');
        request({
          method: 'GET',
          url: baseurl + '/server/users/' + userdata1.id + '/pending/',
          jar: j1,
        }, function (err, res, body) {
          tmsg = JSON.parse(body);
          self.callback(null, tmsg, userdata1, userdata2);
        });
      }
      
      user1auth(); // run the topic
    },
    
    'server should not tell anything to user1 (because user1/2 timeout message should not be displayed)':
    function (error, tmsg, userdata1, userdata2) {
      assert.lengthOf(tmsg, 0);
    },
  },
})


.export(module);
