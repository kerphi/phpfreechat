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

  'when user1 joins the channel (he is alone) then user2 joins the channel (they are two in the channel)': {
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
        
        // [2] u1 get the operator list
        function USER1OPLIST(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/channels/' + cid1 + '/op/',
            jar: j1,
          }, callback);
        },
        
        // [3] auth u2
        function USER2LOGIN(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/auth',
            headers: { 'Pfc-Authorization': 'Basic '
                       + new Buffer("testop2:password").toString('base64') },
            jar: j2,
          }, function (err, res, body) {
            userdata2 = JSON.parse(body);
            callback(err, res, body);
          });
        },
        
        // [4] u2 get the operator list
        function USER2OPLIST1(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/channels/' + cid1 + '/op/',
            jar: j2,
          }, callback);
        },
        
        // [5] u2 join cid1
        function USER2JOIN(callback) {
          request({
            method: 'PUT',
            url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata2.id,
            jar: j2,
          }, callback);
        },
        
        // [6] u2 get the operator list
        function USER2OPLIST2(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/channels/' + cid1 + '/op/',
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
      var codes = [ 200, 201, 200, 200, 403, 201, 200 ];
      results.forEach(function (r, i) {
        assert.equal(r[0].statusCode, codes[i], 'response ' + i + ' code is wrong (expected ' + codes[i] + ' got ' + r[0].statusCode + ')');
      });
    },

    'user1 should be operator when he is alone (first_is_op feature)': function (error, results, requests, steps) {
      var join_response = {};
      try {
        join_response = JSON.parse(results[steps.USER1JOIN][0].body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }
      assert.lengthOf(join_response.op, 1);
      assert.include(join_response.op, userdata1.id);
    },

    'user2 should not be operator when he just joined the channel (first_is_op feature)': function (error, results, requests, steps) {
      var join_response = {};
      try {
        join_response = JSON.parse(results[steps.USER2JOIN][0].body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }
      assert.lengthOf(join_response.op, 1);
      assert.include(join_response.op, userdata1.id);
      assert.isTrue(join_response.op.indexOf(userdata2.id) == -1);
    },    
    
    'user1 should be allowed to retrieve the channel operator list once he joins the channel': function (error, results, requests, steps) {
      var response = {};
      try {
        response = JSON.parse(results[steps.USER1OPLIST][0].body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }

      assert.isArray(response);
      assert.lengthOf(response, 1);
    },    

    'user2 should not be allowed to retrieve the channel operator list if he did not joined the channel': function (error, results, requests, steps) {
      assert.equal(results[steps.USER2OPLIST1][0].statusCode, 403);
    },
    
    'user2 should be allowed to retrieve the channel operator list once he joined the channel': function (error, results, requests, steps) {
      assert.equal(results[steps.USER2OPLIST2][0].statusCode, 200);
      
      var response = {};
      try {
        response = JSON.parse(results[steps.USER2OPLIST2][0].body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }

      assert.isArray(response);
      assert.lengthOf(response, 1);
    },    
    
  },
}).export(module);
