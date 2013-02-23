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
    cid1 = 'ciddata_1',
    cid2 = 'ciddata_2';

try {
  baseurl = fs.readFileSync(__dirname + '/../../serverurl', 'utf8').trim();
} catch (err) {}

vows.describe('User data')

.addBatch({
  'when connected user check his own data': {
    topic: function () {
      var self = this;
      
      // user1 auth
      request({
        method: 'GET',
        url: baseurl + '/server/auth',
        headers: { 'Pfc-Authorization': 'Basic '
                   + new Buffer("testdata1:password").toString('base64') },
        jar: j1,
      }, function (err, res, body) {
        userdata1 = JSON.parse(body);
        
        // check that the userdata are available
        request({
          method: 'GET',
          url: baseurl + '/server/users/' + userdata1.id + '/',
          jar: j1,
        }, self.callback);
        
      });
            
    },
    'server returns a success code': function (error, res, body) {
      assert.equal(res.statusCode, 200);
    },
    'server returns user data': function (error, res, body) {
      var data = {};
      try {
        data = JSON.parse(body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }
      assert.equal(data.name, 'testdata1');
    },
  },
})

.addBatch({
  'when a user not on the same channel check user data of another user': {
    topic: function () {
      var self = this;
      
      // user2 auth
      request({
        method: 'GET',
        url: baseurl + '/server/auth',
        headers: { 'Pfc-Authorization': 'Basic '
                   + new Buffer("testdata2:password").toString('base64') },
        jar: j2,
      }, function (err, res, body) {
        userdata2 = JSON.parse(body);
        
        // check that the userdata are available
        request({
          method: 'GET',
          url: baseurl + '/server/users/' + userdata1.id + '/',
          jar: j2,
        }, self.callback);
        
      });
            
    },
    'server returns a forbidden code': function (error, res, body) {
      assert.equal(res.statusCode, 403);
    },
  },
})


.addBatch({
  'when user1 and user2 are on the same channel and user2 check user1 data': {
    topic: function () {
      var self = this;
      
      // user1 join
      request({
        method: 'PUT',
        url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata1.id,
        jar: j1,
      }, function (err, res, body) {
        // user2 join
        request({
          method: 'PUT',
          url: baseurl + '/server/channels/' + cid1 + '/users/' + userdata2.id,
          jar: j2,
        }, function (err, res, body) {
        
          // check that user2 can read user1 data
          request({
            method: 'GET',
            url: baseurl + '/server/users/' + userdata1.id + '/',
            jar: j2,
          }, self.callback);
          
        });
      });
    },
    'server returns a success code': function (error, res, body) {
      assert.equal(res.statusCode, 200);
    },
    'server returns user1 data': function (error, res, body) {
      var data = {};
      try {
        data = JSON.parse(body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }
      assert.equal(data.name, 'testdata1');
    },
  },
})

.export(module);
