#!/usr/bin/env node

var vows = require('vows'),
    assert = require('assert'),
    request = require('request'),
    async = require('async'),
    querystring = require('querystring'),
    baseurl = 'http://127.0.0.1:32773',
    j1 = request.jar(),
    j2 = request.jar(),
    userdata1 = {},
    userdata2 = {},
    cid1 = 'cid_1',
    cid2 = 'cid_2';

vows.describe('Messages sending and receiving').addBatch({

  'when two users join a channel and post message': {
    topic: function () {
      var self = this;
      var requests = [
        // [0] auth u1
        function user1Login(callback) {
          request({
            method: 'GET',
            url: baseurl+'/server/auth',
            headers: { 'Pfc-Authorization': 'Basic '+new Buffer("testm1:password").toString('base64') }, 
            jar: j1,
          }, function (err, res, body) {
            userdata1 = JSON.parse(body); 
            callback(err, res, body);
          });
        },
        // [1] auth u2
        function user2Login(callback) {
          request({
            method: 'GET',
            url: baseurl+'/server/auth',
            headers: { 'Pfc-Authorization': 'Basic '+new Buffer("testm2:password").toString('base64') }, 
            jar: j2,
          }, function (err, res, body) {
            userdata2 = JSON.parse(body); 
            callback(err, res, body);
          });
        },
        // [2] u1 join cid1
        function user1JoinChannel(callback) {
          request({
            method: 'POST',
            url: baseurl+'/server/channels/'+cid1+'/users/',
            jar: j1,
          }, callback);
        },
        // [3] u2 join cid1
        function user2JoinChannel(callback) {
          request({
            method: 'POST',
            url: baseurl+'/server/channels/'+cid1+'/users/',
            jar: j2,
          }, callback);
        },
        // [4] u2 send a message on cid1
        function user2SendMessageToChannel(callback) {
          request({
            method: 'POST',
            url: baseurl+'/server/channels/'+cid1+'/msg/',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, // importent or PHP will not parse body
            body: querystring.stringify({ body: 'my message' }),
            jar: j2,
          }, callback);
        },
        // [5] u1 read it's pending messages
        function user1ReadMessages(callback) {
          request({
            method: 'GET',
            url: baseurl+'/server/users/'+userdata1.id+'/msg/',
            jar: j1,
          }, callback);
        },        
      ];
      async.series(requests, function (error, results) {
        self.callback(error, results, requests);
      });

    },
    'server returns success status codes': function (error, results, requests) {
      var codes = [ 200, 200, 200, 200, 200, 200 ];
      results.forEach(function (r, i) {
        assert.equal(r[0].statusCode, codes[i], 'response '+ i +' code is wrong (expected '+ codes[i] +' got '+ r[0].statusCode +')');
      });
    },
    'server stores and returns messages': function (error, results, requests) {
      var messages = JSON.parse(results[5][0].body);       
      
      assert.equal(messages.length, 2, 'user1 should have received two messages (join and normal message)');

      assert.equal(messages[0].sender, userdata2.id);      
      assert.equal(messages[0].type, 'msg');
      assert.equal(messages[0].body, 'my message');
    },
  },
}).export(module);
