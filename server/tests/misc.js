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

vows.describe('Misc routes').addBatch({

  'when /skipintro route is visited first time': {
    topic: function () {
      request({
        method: 'GET',
        url: baseurl + '/server/skipintro',
        jar: false,
      }, this.callback);
    },
    'it should return 404': function (error, res, body) {
      assert.equal(res.statusCode, 404);
    },
  },

}).addBatch({

  'when a PUT is sent to the /skipintro route': {
    topic: function () {
      request({
        method: 'PUT',
        url: baseurl + '/server/skipintro',
        jar: false,
      }, this.callback);
    },
    'it should return 200 because request should have been taken into account': function (error, res, body) {
      assert.equal(res.statusCode, 200);
    },
  },

}).addBatch({

  'when /skipintro route is visited for the seconde time (after the PUT)': {
    topic: function () {
      var self = this;
      request({
        method: 'GET',
        url: baseurl + '/server/skipintro',
        jar: false,
      }, self.callback);
    },
    'it should return 200 because the skipintro flag should have been positionned': function (error, res, body) {
      assert.equal(res.statusCode, 200);
    },
  },


}).export(module);
