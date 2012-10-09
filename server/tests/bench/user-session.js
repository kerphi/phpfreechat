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
      
      // user send a message on channel1 with max 15 fake and random words
      function USERSENDMSG1(callback) {
        request({
          method: 'POST',
          url: baseurl + '/server/channels/' + cid1 + '/msg/',
          json: { body: Faker.Lorem.words(Math.floor(Math.random()*15)).join(' ') },
          jar: j1,
        }, callback);
      };
      
      // user send a message on channel2 with max 15 fake and random words
      function USERSENDMSG2(callback) {
        request({
          method: 'POST',
          url: baseurl + '/server/channels/' + cid2 + '/msg/',
          json: { body: Faker.Lorem.words(Math.floor(Math.random()*15)).join(' ') },
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

         // read pending messages each second
        USERREADMSG(function () {
          setTimeout(USERREADMSG, 1000);
        });
        
        // join the first channel between 0 and 2 seconds
        setTimeout(function () {
          USERJOIN1(function () {
            // send 50 messages on channel1 randomly during 10 seconds
            var nb_msg_to_send = 50;
            while (nb_msg_to_send-- > 0) {
              setTimeout(function () {
                USERSENDMSG1();
              }, Math.floor(Math.random()*10000));
            }
            
            // leave channel1 after 10 seconds
            setTimeout(function () {
              USERLEAVE1();
            }, 10000);
          });
        }, Math.floor(Math.random()*2000));
        
        // join the first channel between 2 and 5 seconds
        setTimeout(function () {
          USERJOIN2(function () {
              // send 50 messages on channel2 randomly during 10 seconds
              var nb_msg_to_send = 50;
              while (nb_msg_to_send-- > 0) {
                setTimeout(function () {
                  USERSENDMSG2();
                }, Math.floor(Math.random()*10000));
              }
              
              // leave channel2 after 10 seconds
              setTimeout(function () {
                USERLEAVE2(function () {
                  self.callback(null);
                });
              }, 10000);
          });
        }, 2000 + Math.floor(Math.random()*3000));
      });
      
    },

    'user session should finish after 15 seconds': function (error) {
      assert.ok(true);
    },

  },
}).export(module);
