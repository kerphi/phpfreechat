#!/usr/bin/env vows
/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

var vows = require('vows'),
    assert = require('assert'),
    request = require('request'),
    async = require('async'),
    fs = require('fs'),
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

vows.describe('Channels route').addBatch({

  'when a client join a channel': {
    topic: function () {
      var self = this;

      // auth
      request({
        method: 'GET',
        url: baseurl + '/server/auth',
        headers: { 'Pfc-Authorization': 'Basic '
                   + new Buffer("testch:testchpassword").toString('base64') },
        jar: j1,
      }, function (err, res, body) {
        userdata1 = JSON.parse(body);

        // join the channel cid1
        request({
          method: 'PUT',
          url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata1.id,
          jar: j1,
        }, self.callback);

      });
    },
    'server returns ok': function (error, res, body) {
      assert.equal(res.statusCode, 201);
    },
    'server returns the user list': function (error, res, body) {
      var userlist = {};
      try {
        userlist = JSON.parse(body).users;
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }

      assert.lengthOf(Object.keys(userlist), 1);
      assert.include(Object.keys(userlist), userdata1.id);
    },

    'and another user joins': {
      topic: function () {
        var self = this;

        // auth user2
        request({
          method: 'GET',
          url: baseurl + '/server/auth',
          headers: { 'Pfc-Authorization': 'Basic '
                     + new Buffer("testch2:testch2password").toString('base64') },
          jar: j2,
        }, function (err, res, body) {
          userdata2 = JSON.parse(body);

          // join the channel cid1
          request({
            method: 'PUT',
            url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata2.id,
            jar: j2,
          }, self.callback);

        });
      },
      'server returns ok': function (error, res, body) {
        assert.equal(res.statusCode, 201);
      },
      'server returns the user list': function (error, res, body) {
        var userlist = {};
        try {
          userlist = JSON.parse(body).users;
        } catch (err) {
          assert.isNull(err, 'response body should be JSON formated');
        }

        assert.lengthOf(Object.keys(userlist), 2);
        assert.include(Object.keys(userlist), userdata1.id);
        assert.include(Object.keys(userlist), userdata2.id);
      },
      'the user list contains full userdata': function (error, res, body) {
        var userlist;
        try {
          userlist = JSON.parse(body).users;
        } catch (err) {
          assert.isNull(err, 'response body should be JSON formated');
        }
        assert.equal(userlist[userdata1.id].name, userdata1.name);
        assert.equal(userlist[userdata2.id].name, userdata2.name);
      },
    },
  },

}).addBatch({

  'when a joined user want to list users\'s channel': {
    topic: function () {
      var self = this;

      // list users in channel1
      request({
        method: 'GET',
        url: baseurl + '/server/channels/' + cid1 + '/users/',
        jar: j1,
      }, self.callback);

    },
    'server returns ok': function (error, res, body) {
      assert.equal(res.statusCode, 200);
    },
    'server returns user ids of this channel': function (error, res, body) {
      var userids = [];
      try {
        userids = JSON.parse(body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }
      assert.lengthOf(userids, 2);
      assert.include(userids, userdata1.id);
      assert.include(userids, userdata2.id);
    },
  },

}).addBatch({

  'when two users join a channel and one user leave the channel': {
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
        // [4] u2 list channel users
        function USER1LISTWITHU2(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/channels/' + cid1 + '/users/',
            jar: j1,
          }, callback);
        },
        // [5] u2 leave cid1
        function USER2LEAVE(callback) {
          request({
            method: 'DELETE',
            url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata2.id,
            jar: j2,
          }, callback);
        },
        // [6] u2 list channel users
        function USER1LISTWITHOUTU2(callback) {
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

    'server returns success status codes': function (error, results, requests, steps) {
      var codes = [ 200, 200, 200, 200, 200, 200, 200 ];
      results.forEach(function (r, i) {
        assert.equal(r[0].statusCode, codes[i], 'response ' + i + ' code is wrong (expected ' + codes[i] + ' got ' + r[0].statusCode + ')');
      });
    },

    'server returns array with only user1 id': function (error, results, requests, steps) {
      var userids = [];
      try {
        userids = JSON.parse(results[steps.USER1LISTWITHOUTU2][0].body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }
      assert.lengthOf(userids, 1);
      assert.deepEqual([ userdata1.id ], userids);
    },
     
  },


}).export(module);
