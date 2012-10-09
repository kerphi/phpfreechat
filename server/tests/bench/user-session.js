/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

var vows = require('vows'),
    Faker = require('Faker'),
    assert = require('assert'),
    request = require('request'),
    async = require('async'),
    fs = require('fs'),
    querystring = require('querystring'),
    baseurl = 'http://127.0.0.1:32773',
    j1 = request.jar(),
    userdata = {},
    cid1 = 'user-session-c1',
    cid2 = 'user-session-c2';
    
try {
  baseurl = fs.readFileSync(__dirname + '/../../serverurl', 'utf8');
} catch (err) {}

vows.describe('Basic user session').addBatch({

  'when a user join a channel, post and read messages, leave channel ...': {
    topic: function () {
      var self = this;
      
      // auth user
      function USERLOGIN(callback) {
        request({
          method: 'GET',
          url: baseurl + '/server/auth',
          headers: { 'Pfc-Authorization': 'Basic '
                      + new Buffer("user" + Math.floor(Math.random()*100000) + ":password").toString('base64') },
          jar: j1,
        }, function (err, res, body) {
          userdata = JSON.parse(body);
          callback(err, res, body);
        });
      };
      
      // user join channel1
      function USERJOIN1(callback) {
        request({
          method: 'PUT',
          url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata.id,
          jar: j1,
        }, callback);
      };
      
      // user leave channel1
      function USERLEAVE1(callback) {
        request({
          method: 'DELETE',
          url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata.id,
          jar: j1,
        }, callback);
      };

      // user join channel2
      function USERJOIN2(callback) {
        request({
          method: 'PUT',
          url: baseurl + '/server/channels/' + cid2 + '/users/' + userdata.id,
          jar: j1,
        }, callback);
      };
      
      // user leave channel2
      function USERLEAVE2(callback) {
        request({
          method: 'DELETE',
          url: baseurl + '/server/channels/' + cid2 + '/users/' + userdata.id,
          jar: j1,
        }, callback);
      };      
      
      // user send a message on channel1 with 5 random words
      function USERSENDMSG1(callback) {
        request({
          method: 'POST',
          url: baseurl + '/server/channels/' + cid1 + '/msg/',
          json: { body: Faker.Lorem.words(5).join(' ') },
          jar: j1,
        }, callback);
      };
      
      // user send a message on channel2 with 5 random words
      function USERSENDMSG2(callback) {
        request({
          method: 'POST',
          url: baseurl + '/server/channels/' + cid2 + '/msg/',
          json: { body: Faker.Lorem.words(5).join(' ') },
          jar: j1,
        }, callback);
      };
      
      // user read it's pending messages
      function USERREADMSG(callback) {
        request({
          method: 'GET',
          url: baseurl + '/server/users/' + userdata.id + '/msg/',
          jar: j1,
        }, callback);
      };      

      
      // Run the user session
      USERLOGIN(function () {

         // read pending messages each 5 second
        function readmsg_loop() {
          setTimeout(function () {
            console.log('.');
            USERREADMSG();
            readmsg_loop();
          }, 4900+Math.floor(Math.random()*200));
        }
        readmsg_loop();
        
        // join the first channel between 0 and 2 seconds
        setTimeout(function () {
          USERJOIN1(function () {
            var join1 = true;
            // send 1 message each 2 second on channel1 during 60 seconds
            function sendmsg1_loop() {
              if (join1) {
                setTimeout(function () {
                  USERSENDMSG1();
                  console.log('1');
                  sendmsg1_loop();
                }, 1900+Math.floor(Math.random()*200));
              }
            }
            sendmsg1_loop();
            
            // leave channel1 after 60 seconds
            setTimeout(function () {
              join1 = false;
              USERLEAVE1();
            }, 60000);
          });
        }, Math.floor(Math.random()*2000));
        
        
        // join the second channel between 0 and 2 seconds
        setTimeout(function () {
          USERJOIN2(function () {
            var join2 = true;
            // send 1 message each 2 second on channel2 during 60 seconds
            function sendmsg2_loop() {
              if (join2) {
                setTimeout(function () {
                  USERSENDMSG2();
                  console.log('2');
                  sendmsg2_loop();
                }, 1900+Math.floor(Math.random()*200));
              }
            }
            sendmsg2_loop();
            
            // leave channel2 after 60 seconds
            setTimeout(function () {
              join2 = false;
              USERLEAVE2(function () {
                self.callback(null); // finish the vows test
              });
            }, 60000);
          });
        }, Math.floor(Math.random()*2000));
        
      });
      
    },

    'user session should finish after 60 seconds': function (error) {
      assert.ok(true);
    },

  },
}).export(module);
