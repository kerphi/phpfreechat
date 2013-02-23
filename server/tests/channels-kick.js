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
    userdata1 = {},
    userdata2 = {},
    cid1 = 'kickchannel',
    cid2 = 'kickchannel',
    login1 = 'testkickuser1',
    login2 = 'testkickuser2';
    
try {
  baseurl = fs.readFileSync(__dirname + '/../../serverurl', 'utf8').trim();
} catch (err) {}

vows.describe('Channel kick tests').addBatch({

  'when user1 joins the channel then user2 joins the channel then user1 kick user2': {
    topic: function () {
      var self = this;
      var requests = [
        
        // auth u1
        function USER1LOGIN(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/auth',
            headers: { 'Pfc-Authorization': 'Basic '
                       + new Buffer(login1 + ":password").toString('base64') },
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
                
        // auth u2
        function USER2LOGIN(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/auth',
            headers: { 'Pfc-Authorization': 'Basic '
                       + new Buffer(login2 + ":password").toString('base64') },
            jar: j2,
          }, function (err, res, body) {
            userdata2 = JSON.parse(body);
            callback(err, res, body);
          });
        },
        
        // u2 join cid1
        function USER2JOIN(callback) {
          request({
            method: 'PUT',
            url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata2.id,
            jar: j2,
          }, callback);
        },

        // u2 try to kick u1
        function USER2KICKUSER1(callback) {
          request({
            method: 'DELETE',
            url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata1.id,
            jar: j2,
          }, callback);
        },

        // u1 try to kick u2
        function USER1KICKUSER2(callback) {
          request({
            method: 'DELETE',
            url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata2.id,
            jar: j1,
          }, callback);
        },
        
        // u1 check the channel's user list
        function CHECKUSERLIST(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/channels/' + cid1 + '/users/',
            jar: j1,
          }, callback);
        },
        
        // u1 read it's pending messages
        function USER1READMSG(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/users/' + userdata1.id + '/pending/',
            jar: j1,
          }, callback);
        },

        // u2 read it's pending messages
        function USER2READMSG(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/users/' + userdata2.id + '/pending/',
            jar: j2,
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

    'user2 should not be allowed to kick user1': function (error, results, requests, steps) {
      var result = results[steps.USER2KICKUSER1][0];
      assert.equal(result.statusCode, 403);
      
      var response = {};
      try {
        response = JSON.parse(result.body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }
      assert.equal(response.errorCode, 40304);
    },

    'user1 should be allowed to kick user2': function (error, results, requests, steps) {
      var result = results[steps.USER1KICKUSER2][0];
      assert.equal(result.statusCode, 200);      
    },    

    'after being kicked user2 should not be listed anymore in the channel': function (error, results, requests, steps) {
      var result = results[steps.CHECKUSERLIST][0];
      assert.equal(result.statusCode, 200);
      
      var response = {};
      try {
        response = JSON.parse(result.body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }
      assert.lengthOf(response, 1);
      assert.include(response, userdata1.id);
    },    
    
    'user1 should not be notified user2 has been kicked cause user1 initiated the kick': function (error, results, requests, steps) {
      var result = results[steps.USER1READMSG][0];
      assert.equal(result.statusCode, 200);
      
      var response = {};
      try {
        response = JSON.parse(result.body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }

      assert.notEqual(response[0].type, 'kick');
    },
    
    'user2 should be notified he has been kicked': function (error, results, requests, steps) {
      var result = results[steps.USER2READMSG][0];
      assert.equal(result.statusCode, 200);
      
      var response = {};
      try {
        response = JSON.parse(result.body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }
      assert.equal(response[0].type, 'kick');
      assert.equal(response[0].sender, userdata1.id); // who kicked me !
      assert.isNotNull(response[0].body.target);
      assert.equal(response[0].body.target, userdata2.id); // who was kicked
    },
  },
}).export(module);
