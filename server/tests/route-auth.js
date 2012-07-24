#!/usr/bin/env node

var vows = require('vows'),
    assert = require('assert'),
    request = require('request');

// Create a Test Suite
vows.describe('Auth route').addBatch({
    'when route is visited for the first time': {
        topic: function () {
          request({
            method: 'GET',
            url : 'http://127.0.0.1:32773/server/auth',
          }, this.callback);
        },
        'server ask for authentication': function (error, res, body) {
          assert.equal(res.statusCode, 403);
          assert.isNotNull(res.headers['pfc-www-authenticate']);
        }
    },
}).export(module); // Run it
