#!/usr/bin/env vows
/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

var vows = require('vows'),
    assert = require('assert'),
    request = require('request'),
    async = require('async'),
    fs = require('fs'),
    querystring = require('querystring'),
    baseurl = 'http://127.0.0.1:32773',
    j1 = request.jar(),
    j2 = request.jar(),
    j3 = request.jar(),
    userdata1 = {},
    userdata2 = {},
    userdata3 = {},
    nickname1 = 'UserBan1',
    nickname2 = 'UserBan2',
    nickname3 = 'UserBan3',
    cid1 = 'cid_ban_1';

try {
  baseurl = fs.readFileSync(__dirname + '/../../serverurl', 'utf8').trim();
} catch (err) {}

vows.describe('Channel ban tests').addBatch({

  'when user1 (operator) joins the channel and banish "UserBan2" nickname, then "UserBan2" tries to joins the channel': {
    topic: function () {
      var self = this;
      var requests = [
        
        // auth u1
        function USER1LOGIN(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/auth',
            headers: { 'Pfc-Authorization': 'Basic '
                       + new Buffer(nickname1+":password").toString('base64') },
            jar: j1,
          }, function (err, res, body) {
            userdata1 = JSON.parse(body);
            callback(err, res, body);
          });
        },
        
        // u1 join cid1
        function USER1JOIN(callback) {
          request({
            method: 'PUT',
            url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata1.id,
            jar: j1,
          }, callback);
        },

        
        // auth u3 (the spectator)
        function USER3LOGIN(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/auth',
            headers: { 'Pfc-Authorization': 'Basic '
                       + new Buffer(nickname3+":password").toString('base64') },
            jar: j3,
          }, function (err, res, body) {
            userdata3 = JSON.parse(body);
            callback(err, res, body);
          });
        },
        
        // u3 join cid1
        function USER3JOIN(callback) {
          request({
            method: 'PUT',
            url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata3.id,
            jar: j3,
          }, callback);
        },
        
        // u1 get the banished list
        function USER1BANLIST(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/channels/' + cid1 + '/ban/',
            jar: j1,
          }, callback);
        },
        
        // u1 banish a not used name
        function USER1BANNOTUSEDNAME(callback) {
          request({
            method: 'PUT',
            url: baseurl + '/server/channels/' + cid1 + '/ban/' + new Buffer('Chûck Norrïs').toString('base64'),
            jar: j1,
          }, callback);
        },        
 
        // u1 get the banished list
        function USER1BANLIST2(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/channels/' + cid1 + '/ban/',
            jar: j1,
          }, callback);
        },
        
        // auth u2
        function USER2LOGIN(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/auth',
            headers: { 'Pfc-Authorization': 'Basic '
                       + new Buffer(nickname2 + ":password").toString('base64') },
            jar: j2,
          }, function (err, res, body) {
            userdata2 = JSON.parse(body);
            callback(err, res, body);
          });
        },
        
        // u2 get the banished list
        function USER2BANLIST(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/channels/' + cid1 + '/ban/',
            jar: j2,
          }, callback);
        },

        // u2 join cid1
        function USER2JOIN(callback) {
          request({
            method: 'PUT',
            url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata2.id,
            jar: j2,
          }, callback);
        },

        // u2 banish u1 (not allowed)
        function USER2BANUSER1(callback) {
          request({
            method: 'PUT',
            url: baseurl + '/server/channels/' + cid1 + '/ban/' + new Buffer(userdata1.name).toString('base64'),
            jar: j2,
          }, callback);
        },

        // u1 banish u2
        function USER1BANUSER2(callback) {
          request({
            method: 'PUT',
            url: baseurl + '/server/channels/' + cid1 + '/ban/' + new Buffer(userdata2.name).toString('base64'),
            qs: { 'reason': 'Bye bye' },
            jar: j1,
          }, callback);
        },

        // u1 get the banished list
        function USER1BANLIST3(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/channels/' + cid1 + '/ban/',
            jar: j1,
          }, callback);
        },

        // u1 check the channels user list
        function USER1GETUSERLIST(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/channels/' + cid1 + '/users/',
            jar: j1,
          }, callback);
        },
        
        // u2 read its pending messages
        function USER2READMSG(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/users/' + userdata2.id + '/pending/',
            jar: j2,
          }, callback);
        },

        // u3 read its pending messages
        function USER3READMSG(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/users/' + userdata3.id + '/pending/',
            jar: j3,
          }, callback);
        },

        // u1 kick u2
        function USER1KICKUSER2(callback) {
          request({
            method: 'DELETE',
            url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata2.id,
            jar: j1,
          }, callback);
        },
        
        // u2 join cid1 (not allowed cause he is banished)
        function USER2JOIN2(callback) {
          request({
            method: 'PUT',
            url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata2.id,
            jar: j2,
          }, callback);
        },

        // u1 unbanish u2
        function USER1UNBANUSER2(callback) {
          request({
            method: 'DELETE',
            url: baseurl + '/server/channels/' + cid1 + '/ban/' + new Buffer(userdata2.name).toString('base64'),
            jar: j1,
          }, callback);
        },

        // u1 get the banished list
        function USER1BANLIST4(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/channels/' + cid1 + '/ban/',
            jar: j1,
          }, callback);
        },

        // u2 join cid1 (he is no more banished)
        function USER2JOIN3(callback) {
          request({
            method: 'PUT',
            url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata2.id,
            jar: j2,
          }, callback);
        },

        // u1 kickban u2
        function USER1KICKBANUSER2(callback) {
          request({
            method: 'PUT',
            url: baseurl + '/server/channels/' + cid1 + '/ban/' + new Buffer(userdata2.name).toString('base64'),
            qs: { 'reason': 'Bye bye', 'kickban' : true },
            jar: j1,
          }, callback);
        },
        
        // u1 check the channels user list (u2 should not be listed anymore)
        function USER1GETUSERLIST2(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/channels/' + cid1 + '/users/',
            jar: j1,
          }, callback);
        },
        
      ];

      // store function names in the steps array
      // so following asserts are easier to read
      var steps = {};
      requests.forEach(function (fn, i) {
        steps[fn.name] = i;
      });

      // run the function array in a sequential order
      // each function result is stored in the 'results' array
      async.series(requests, function (error, results) {
        self.callback(error, results, requests, steps);
      });
    },

    // USER1BANLIST
    'ban list should be empty at beginning': function (error, results, requests, steps) {
      var result = results[steps.USER1BANLIST][0];
      assert.equal(result.statusCode, 200);
      
      var response = {};
      try {
        response = JSON.parse(result.body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }
      assert.isObject(response);
      assert.lengthOf(Object.keys(response), 0);
    },
    
    // USER1BANNOTUSEDNAME
    'banish a not used name should be allowed': function (error, results, requests, steps) {
      var result = results[steps.USER1BANNOTUSEDNAME][0];
      assert.equal(result.statusCode, 201);
    },

    // USER1BANLIST2
    'ban list should contains the not used name': function (error, results, requests, steps) {
      var result = results[steps.USER1BANLIST2][0];
      assert.equal(result.statusCode, 200);
      
      var response = {};
      try {
        response = JSON.parse(result.body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }
      assert.isObject(response);
      assert.lengthOf(Object.keys(response), 1);
      assert.include(Object.keys(response), 'Chûck Norrïs');
    },
    
    // USER2BANLIST
    'ban list should not be getable if you have not joined the channel': function (error, results, requests, steps) {
      var result = results[steps.USER2BANLIST][0];
      assert.equal(result.statusCode, 403);
    },
    
    // USER2JOIN
    'user2 should be allowed to join the channel cause he is not yet banished': function (error, results, requests, steps) {
      var result = results[steps.USER2JOIN][0];
      assert.equal(result.statusCode, 201);
    },    

    // USER2BANUSER1
    'user2 should not be allowed to banish user1 cause he is not a channel operator': function (error, results, requests, steps) {
      var result = results[steps.USER2BANUSER1][0];
      assert.equal(result.statusCode, 403);
    },
    
    // USER1BANUSER2
    'user1 should be allowed to banish user2': function (error, results, requests, steps) {
      var result = results[steps.USER1BANUSER2][0];
      assert.equal(result.statusCode, 201);
    },
    
    // USER1GETUSERLIST
    'user2 should still be listed in the users channel list cause he was not a kickban': function (error, results, requests, steps) {
      var result = results[steps.USER1GETUSERLIST][0];
      assert.equal(result.statusCode, 200);
      
      var response = {};
      try {
        response = JSON.parse(result.body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }

      assert.isArray(response);
      assert.lengthOf(response, 3);
    },
    
    // USER1BANLIST3
    'user2 should be listed in the channel\'s ban list': function (error, results, requests, steps) {
      var result = results[steps.USER1BANLIST3][0];
      assert.equal(result.statusCode, 200);
      
      var response = {};
      try {
        response = JSON.parse(result.body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }

      assert.isObject(response);
      assert.lengthOf(Object.keys(response), 2);
      assert.include(Object.keys(response), userdata2.name);
    },
    
    
    // USER2READMSG
    'user2 should have a pending ban message': function (error, results, requests, steps) {
      var result = results[steps.USER2READMSG][0];
      assert.equal(result.statusCode, 200);

      var response = {};
      try {
        response = JSON.parse(result.body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }
      assert.lengthOf(response, 1);
      assert.equal(response[0].type, 'ban');
      assert.equal(response[0].sender, userdata1.id); // who banished me !
      assert.equal(response[0].body.name, userdata2.name); // who was ban
      assert.equal(response[0].body.reason, 'Bye bye'); // ban reason
    },
    
    // USER3READMSG
    'user3 should has been notified that user2 has been banished': function (error, results, requests, steps) {
      var result = results[steps.USER3READMSG][0];
      assert.equal(result.statusCode, 200);

      var response = {};
      try {
        response = JSON.parse(result.body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }

      assert.lengthOf(response, 3);
      assert.equal(response[2].type, 'ban');
      assert.equal(response[2].body.reason, 'Bye bye');
      assert.equal(response[2].body.name, userdata2.name);      
      assert.equal(response[0].type, 'ban');
      assert.equal(response[0].body.reason, '');
      assert.equal(response[0].body.name, 'Chûck Norrïs');
    },
    
    // USER2JOIN2
    'user2 should not be able to join the channel cause he is banished': function (error, results, requests, steps) {
      var result = results[steps.USER2JOIN2][0];
      assert.equal(result.statusCode, 403);
      
      var response = {};
      try {
        response = JSON.parse(result.body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }

      assert.isObject(response);
      assert.equal(response.errorCode, 40305);
      assert.equal(response.baninfo.opname, nickname1);
      assert.equal(response.baninfo.reason, 'Bye bye');
    },
    
    // USER1UNBANUSER2
    'user1 should be allowed to unbanish user2': function (error, results, requests, steps) {
      var result = results[steps.USER1UNBANUSER2][0];
      assert.equal(result.statusCode, 200);
    },
    
    // USER1BANLIST4
    'user2 name should not be listed anymore in the ban list cause user1 unbanished him': function (error, results, requests, steps) {
      var result = results[steps.USER1BANLIST4][0];
      assert.equal(result.statusCode, 200);
      
      var response = {};
      try {
        response = JSON.parse(result.body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }

      assert.isObject(response);
      assert.lengthOf(Object.keys(response), 1);
    },
    
    // USER2JOIN3
    'user2 should be able to join channel cause he is no more banished': function (error, results, requests, steps) {
      var result = results[steps.USER2JOIN3][0];
      assert.equal(result.statusCode, 201);
    },
    
    // USER1GETUSERLIST2
    'user2 should not be listed anymore in the users channel list cause he was kickbanned': function (error, results, requests, steps) {
      var result = results[steps.USER1GETUSERLIST2][0];
      assert.equal(result.statusCode, 200);
      
      var response = {};
      try {
        response = JSON.parse(result.body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }

      assert.isArray(response);
      assert.lengthOf(response, 2);
    },

  },
}).export(module);
