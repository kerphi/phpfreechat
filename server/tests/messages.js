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
    cid1 = 'cid_1',
    cid2 = 'cid_2';
    
try {
  baseurl = fs.readFileSync(__dirname + '/../../serverurl', 'utf8').trim();
} catch (err) {}

vows.describe('Send and receive messages').addBatch({

  'when two users join a channel and post a message': {
    topic: function () {
      var self = this;
      var requests = [
        // [0] auth u1
        function USER1LOGIN(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/auth',
            headers: { 'Pfc-Authorization': 'Basic '
                       + new Buffer("testm1:password").toString('base64') },
            jar: j1,
          }, function (err, res, body) {
            userdata1 = JSON.parse(body);
            callback(err, res, body);
          });
        },
        // [1] auth u2
        function USER2LOGIN(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/auth',
            headers: { 'Pfc-Authorization': 'Basic '
                       + new Buffer("testm2:password").toString('base64') },
            jar: j2,
          }, function (err, res, body) {
            userdata2 = JSON.parse(body);
            callback(err, res, body);
          });
        },
        // [2] u1 join cid1
        function USER1JOIN(callback) {
          request({
            method: 'PUT',
            url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata1.id,
            jar: j1,
          }, callback);
        },
        // [3] u2 join cid1
        function USER2JOIN(callback) {
          request({
            method: 'PUT',
            url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata2.id,
            jar: j2,
          }, callback);
        },
        // [4] u2 send a message on cid1
        function USER2SENDMSG(callback) {
          request({
            method: 'POST',
            url: baseurl + '/server/channels/' + cid1 + '/msg/',
            json: 'my user2 message',
            jar: j2,
          }, callback);
        },
        // [5] u1 read it's pending messages
        function USER1READMSG(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/users/' + userdata1.id + '/pending/',
            jar: j1,
          }, callback);

        },
        // [6] u1 send a message on cid1
        function USER1SENDMSG(callback) {
          request({
            method: 'POST',
            url: baseurl + '/server/channels/' + cid1 + '/msg/',
            json: 'my user1 message',
            jar: j1,
          }, callback);
        },
        // [7] u2 read it's pending messages
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

    'server returns success status codes': function (error, results, requests, steps) {
      var codes = [ 200, 200, 201, 201, 201, 200, 201, 200 ];
      results.forEach(function (r, i) {
        assert.equal(r[0].statusCode, codes[i], 'response ' + i + ' code is wrong (expected ' + codes[i] + ' got ' + r[0].statusCode + ')');
      });
    },

    'server stores and returns user1 messages': function (error, results, requests, steps) {
      var messages = [];
      try {
        messages = JSON.parse(results[steps.USER1READMSG][0].body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }
      assert.equal(messages.length, 2, 'user1 should have received two messages (join and normal message)');

      messages.forEach(function (m) {
        assert.equal(m.recipient, 'channel|' + cid1);
        assert.equal(m.sender, userdata2.id);
        if (m.type == 'msg') {
          assert.equal(m.body, 'my user2 message');
        } else if (m.type == 'join') {
          assert.equal(m.body.userdata.name, 'testm2');
        } else {
          assert.ok(false);
        }
      });
    },

    'server stores and returns user2 messages': function (error, results, requests, steps) {
      var messages = [];
      try {
        messages = JSON.parse(results[steps.USER2READMSG][0].body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }

      assert.equal(messages.length, 1, 'user2 should have received one message (user1 message)');

      assert.equal(messages[0].sender, userdata1.id);
      assert.equal(messages[0].type, 'msg');
      assert.equal(messages[0].body, 'my user1 message');
    },
  },
}).export(module);
