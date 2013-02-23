#!/usr/bin/env vows
/*jslint node: true, browser: false, jquery: false, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

var vows = require('vows'),
    assert = require('assert'),
    request = require('request'),
    fs = require('fs'),
    baseurl = 'http://127.0.0.1:32773';

try {
  baseurl = fs.readFileSync(__dirname + '/../../serverurl', 'utf8').trim();
} catch (err) {}

vows.describe('Auth route').addBatch({

  'when auth route is visited without credentials': {
    topic: function () {
      request({
        method: 'GET',
        url: baseurl + '/server/auth',
        jar: false,
      }, this.callback);
    },
    'server ask for authentication': function (error, res, body) {      
      assert.equal(res.statusCode, 403);
      assert.isNotNull(res.headers['pfc-www-authenticate']);
    },
  },

}).addBatch({

  'when client sends right credentials': {
    topic: function () {
      request({
        method: 'GET',
        url: baseurl + '/server/auth',
        headers: { 'Pfc-Authorization': 'Basic '
                   + new Buffer("test:testpassword").toString('base64') },
        jar: false,
      }, this.callback);
    },
    'server tells that user is authenticated with a success code': function (error, res, body) {
      assert.equal(res.statusCode, 200);
      
      try {
        var x = JSON.parse(body);
      } catch (err) {
        console.log(body);
        assert.isNull(err, 'response body should be JSON formated');
      }

      assert.equal(JSON.parse(body).name, 'test', 'test username should be returned');
      assert.isNotNull(JSON.parse(body).id, 'a user id should be returned');
    },
    'server open a session and returns a cookie': function (error, res, body) {
      assert.isNotNull(res.headers['set-cookie']);
    },
  },

}).addBatch({

  'when client already has a session and try to authenticate': {
    topic: function () {
      var self = this;
      request({
        method: 'GET',
        url: baseurl + '/server/auth',
        headers: { 'Pfc-Authorization': 'Basic '
                   + new Buffer("test2:test2password").toString('base64') },
      }, function (err) {
        request({
          method: 'GET',
          url: baseurl + '/server/auth',
          headers: { 'Pfc-Authorization': 'Basic '
                     + new Buffer("test3:test3password").toString('base64') },
        }, self.callback);
      });
    },
    'server just tells that user is authenticated with the first session': function (error, res, body) {
      assert.equal(res.statusCode, 200);
      assert.equal(JSON.parse(body).name, 'test2', 'test2 login should be found (first session credentials)');
    },
  },

}).addBatch({

  'when client try to authenticate with a already used login': {
    topic: function () {
      var self = this;
      request({
        method: 'GET',
        url: baseurl + '/server/auth',
        headers: { 'Pfc-Authorization': 'Basic '
                   + new Buffer("test4:test4password").toString('base64') },
        jar: false,
      }, function (err) {
        request({
          method: 'GET',
          url: baseurl + '/server/auth',
          headers: { 'Pfc-Authorization': 'Basic '
                     + new Buffer("test4:test4password").toString('base64') },
          jar: false,
        }, self.callback);
      });
    },
    'server tells that login is already used': function (error, res, body) {
      assert.equal(res.statusCode, 403);
    },
  },

}).addBatch({

  'when client has a session and try to logout': {
    topic: function () {
      var self = this;
      var j = request.jar(); // to have no cookies in cache
      request({
        method: 'GET',
        url: baseurl + '/server/auth',
        headers: { 'Pfc-Authorization': 'Basic '
                   + new Buffer("test5:test5password").toString('base64') },
        jar: j,
      }, function (err) {
        request({
          method: 'DELETE',
          url: baseurl + '/server/auth',
          jar: j,
        }, self.callback);
      });

    },
    'server tells that user is disconnected and returns users data': function (error, res, body) {
      assert.equal(res.statusCode, 201);

      try {
        var x = JSON.parse(body);
      } catch (err) {
        assert.isNull(err, 'response body should be JSON formated');
      }

      assert.equal(JSON.parse(body).name, 'test5', 'test5 login should be returned');
    },
  },

}).export(module);
