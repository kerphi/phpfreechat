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
  baseurl = fs.readFileSync(__dirname + '/../../serverurl', 'utf8').trim();
} catch (err) {}

vows.describe('Channel operator rights tests').addBatch({

  'when user1 joins the channel (he is alone but operator) then user2 joins the channel (he is not an operator)': {
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

        
        // `server/channels/:cid/op/:uid`         - GET    - tells if :uid is operator on :cid
        // [7] u2 check a operator user status
        function USER2CHECKOP1(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/channels/' + cid1 + '/op/' + userdata1.id,
            jar: j2,
          }, callback);
        },
        // [8] u2 check a none operator user status
        function USER2CHECKOP2(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/channels/' + cid1 + '/op/' + userdata2.id,
            jar: j2,
          }, callback);
        },
        
        // `server/channels/:cid/op/:uid`         - PUT    - add :uid to the operator list on :cid channel (try to)
        // [9] u2 try to give op rights to him
        function USER2GIVEOP(callback) {
          request({
            method: 'PUT',
            url: baseurl + '/server/channels/' + cid1 + '/op/' + userdata2.id,
            jar: j2,
          }, callback);
        },
        // [10] u1 try to give op rights to u2
        function USER1GIVEOP(callback) {
          request({
            method: 'PUT',
            url: baseurl + '/server/channels/' + cid1 + '/op/' + userdata2.id,
            jar: j1,
          }, callback);
        },
        // [11] u2 get the operator list
        function USER2OPLIST3(callback) {
          request({
            method: 'GET',
            url: baseurl + '/server/channels/' + cid1 + '/op/',
            jar: j2,
          }, callback);
        },
        
        // `server/channels/:cid/op/:uid`         - DELETE - removes operator rights to :uid on :cid channel (try to)
        // [12] u1 try to remove op rights to u2
        function USER1REMOVEOP(callback) {
          request({
            method: 'DELETE',
            url: baseurl + '/server/channels/' + cid1 + '/op/' + userdata2.id,
            jar: j1,
          }, callback);
        },
        // [13] u2 try to remove op rights to u1
        function USER2REMOVEOP(callback) {
          request({
            method: 'DELETE',
            url: baseurl + '/server/channels/' + cid1 + '/op/' + userdata1.id,
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

    'user1 should be operator when he is alone (first_is_op feature)': function (error, results, requests, steps) {
      var result = results[steps.USER1JOIN][0];
      assert.equal(result.statusCode, 201);
      
      var response = {};
      try {
        response = JSON.parse(result.body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }
      assert.lengthOf(response.op, 1);
      assert.include(response.op, userdata1.id);
    },

    'user2 should not be operator when he just joined the channel (first_is_op feature)': function (error, results, requests, steps) {
      var result = results[steps.USER2JOIN][0];
      assert.equal(result.statusCode, 201);

      var response = {};
      try {
        response = JSON.parse(result.body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }
      assert.lengthOf(response.op, 1);
      assert.include(response.op, userdata1.id);
      assert.isTrue(response.op.indexOf(userdata2.id) == -1);
    },    
    
    'user1 should be allowed to retrieve the channel operator list once he joins the channel': function (error, results, requests, steps) {
      var result = results[steps.USER1OPLIST][0];
      var response = {};
      try {
        response = JSON.parse(result.body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }

      assert.isArray(response);
      assert.lengthOf(response, 1);
    },    

    'user2 should not be allowed to retrieve the channel operator list if he did not joined the channel': function (error, results, requests, steps) {
      var result = results[steps.USER2OPLIST1][0];
      assert.equal(result.statusCode, 403);
    },
    
    'user2 should be allowed to retrieve the channel operator list once he joined the channel': function (error, results, requests, steps) {
      var result = results[steps.USER2OPLIST2][0];
      assert.equal(result.statusCode, 200);
      
      var response = {};
      try {
        response = JSON.parse(result.body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }

      assert.isArray(response);
      assert.lengthOf(response, 1);
    },    
    
    'user2 should be able to check the user1 operator status': function (error, results, requests, steps) {
      var result = results[steps.USER2CHECKOP1][0];
      assert.equal(result.statusCode, 200, 'should returns 200 (user1 IS an operator on this channel)');
    },
    
    'user2 should be able to check the user2 operator status': function (error, results, requests, steps) {
      var result = results[steps.USER2CHECKOP2][0];
      assert.equal(result.statusCode, 404, 'should returns 404 (user2 is NOT an operator on this channel)');
    },
    
    'user2 should not be able to give op rights to him': function (error, results, requests, steps) {
      var result = results[steps.USER2GIVEOP][0];
      assert.equal(result.statusCode, 403, 'should returns 403 (user2 is NOT an operator so he cannot give op rights to other users)');
    },    
    
    'user1 should be able to give op rights to user2': function (error, results, requests, steps) {
      var result = results[steps.USER1GIVEOP][0];
      assert.equal(result.statusCode, 200, 'should returns 200 (user1 IS an operator so he can give op rights to other users)');
    },
    
    'once user1 gave op rights to user2, the operator list should contain user1 and user2': function (error, results, requests, steps) {
      var result = results[steps.USER2OPLIST3][0];
      assert.equal(result.statusCode, 200);
      
      var response = {};
      try {
        response = JSON.parse(result.body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }

      assert.isArray(response);
      assert.lengthOf(response, 2);
      assert.include(response, userdata1.id);
      assert.include(response, userdata2.id);
    },    
    
    'user1 should be able to remove op rights to user2': function (error, results, requests, steps) {
      var result = results[steps.USER1REMOVEOP][0];
      assert.equal(result.statusCode, 200, 'should returns 200 (user1 IS an operator so he can remove op rights to other users)');
    },
    'user2 should not be able to remove op rights to user1': function (error, results, requests, steps) {
      var result = results[steps.USER2REMOVEOP][0];
      assert.equal(result.statusCode, 403, 'should returns 403 (user2 is NOT an operator so he cannot remove op rights to other users)');
    },
    
    
  },
}).export(module);
