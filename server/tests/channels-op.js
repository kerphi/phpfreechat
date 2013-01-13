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
    cid1 = 'cid_op_1',
    cid2 = 'cid_op_2';
    
try {
  baseurl = fs.readFileSync(__dirname + '/../../serverurl', 'utf8');
} catch (err) {}

vows.describe('First is operator tests').addBatch({

  'when a user join a channel and is alone': {
    topic: function () {
      var self = this;
      var requests = [
        // [0] auth u1
        function USER1LOGIN(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/auth',
            headers: { 'Pfc-Authorization': 'Basic '
                       + new Buffer("testop1:password").toString('base64') },
            jar: j1,
          }, function (err, res, body) {
            userdata1 = JSON.parse(body);
            callback(err, res, body);
          });
        },
        // [1] u1 join cid1
        function USER1JOIN(callback) {
          request({
            method: 'PUT',
            url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata1.id,
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
      var codes = [ 200, 201 ];
      results.forEach(function (r, i) {
        assert.equal(r[0].statusCode, codes[i], 'response ' + i + ' code is wrong (expected ' + codes[i] + ' got ' + r[0].statusCode + ')');
      });
    },

    'join command response has an operator list with the user1 id into (first_is_op feature)': function (error, results, requests, steps) {
      var join_response = {};
      try {
        join_response = JSON.parse(results[steps.USER1JOIN][0].body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }
      assert.lengthOf(join_response.op, 1);
      assert.include(join_response.op, userdata1.id);
    },

  },
}).export(module);
