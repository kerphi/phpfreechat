#!/usr/bin/env node

var vows = require('vows'),
    assert = require('assert'),
    request = require('request');

vows.describe('Auth route').addBatch({

  'when auth route is visited': {
      topic: function () {
        request({
          method: 'GET',
          url: 'http://127.0.0.1:32773/server/auth',
        }, this.callback);
      },
      'server ask for authentication': function (error, res, body) {
        assert.equal(res.statusCode, 403);
        assert.isNotNull(res.headers['pfc-www-authenticate']);
      },
              
      'and the client sends right credentials': {
        topic: function () {
          request({
            method: 'GET',
            url: 'http://127.0.0.1:32773/server/auth',
            headers: { 'Pfc-Authorization': 'Basic '+new Buffer("test:testpassword").toString('base64') }, 
            jar: false,
          }, this.callback);
        },
        'server tells that user is authenticated with a success code': function (error, res, body) {
          assert.equal(res.statusCode, 200);
        },
        'server open a session and returns a cookie': function(error, res, body) {
          assert.isNotNull(res.headers['set-cookie']);
        },
      },

      'and the client is already authenticated': {
        topic: function () {
          var self = this;
          request({
            method: 'GET',
            url: 'http://127.0.0.1:32773/server/auth',
            headers: { 'Pfc-Authorization': 'Basic '+new Buffer("test2:test2password").toString('base64') }, 
          }, function (err) {
            request({
              method: 'GET',
              url: 'http://127.0.0.1:32773/server/auth',
            }, self.callback);
          });
        },
        'server tells that login is already authenticated': function (error, res, body) {
          assert.equal(res.statusCode, 200);
        },
      },

      'and the client try to auth with a already used login': {
        topic: function () {
          var self = this;
          request({
            method: 'GET',
            url: 'http://127.0.0.1:32773/server/auth',
            headers: { 'Pfc-Authorization': 'Basic '+new Buffer("test3:test3password").toString('base64') }, 
            jar: false,
          }, function (err) {
            request({
              method: 'GET',
              url: 'http://127.0.0.1:32773/server/auth',
              headers: { 'Pfc-Authorization': 'Basic '+new Buffer("test3:test3password").toString('base64') }, 
              jar: false,
            }, self.callback);
          });
        },
        'server tells that login is already used': function (error, res, body) {
          assert.equal(res.statusCode, 403);
        },
      },

  },
}).export(module);
