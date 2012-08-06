#!/usr/bin/env node

var vows = require('vows'),
    assert = require('assert'),
    request = require('request'),
    baseurl = 'http://127.0.0.1:32773/server-slim',
    j1 = request.jar(),
    j2 = request.jar(),
    userdata1 = {},
    userdata2 = {},
    cid1 = 'cid_1',
    cid2 = 'cid_2';

vows.describe('Channels route').addBatch({

  'when a client join a channel': {
    topic: function () {
      var self = this;

      // auth
      request({
        method: 'GET',
        url: baseurl+'/auth',
        headers: { 'Pfc-Authorization': 'Basic '+new Buffer("testch:testchpassword").toString('base64') }, 
        jar: j1,
      }, function (err, res, body) {
        userdata1 = JSON.parse(body);

        // join the channel cid1
        request({
          method: 'PUT',
          url: baseurl+'/channels/'+cid1+'/users/'+userdata1.id,
          jar: j1,
        }, self.callback);

      });
    },
    'server returns ok': function (error, res, body) {
      assert.equal(res.statusCode, 200);
    },
    'server returns the user list': function (error, res, body) {
      try {
        var userlist = JSON.parse(body);
      } catch(err) {
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
          url: baseurl+'/auth',
          headers: { 'Pfc-Authorization': 'Basic '+new Buffer("testch2:testch2password").toString('base64') }, 
          jar: j2,
        }, function (err, res, body) {
          userdata2 = JSON.parse(body);

          // join the channel cid1
          request({
            method: 'PUT',
            url: baseurl+'/channels/'+cid1+'/users/'+userdata2.id,
            jar: j2,
          }, self.callback);

        });
      },
      'server returns ok': function (error, res, body) {
        assert.equal(res.statusCode, 200);
      },
      'server returns the user list': function (error, res, body) {
        try {
          var userlist = JSON.parse(body);
        } catch(err) {
          assert.isNull(err, 'response body should be JSON formated');
        }

        assert.lengthOf(Object.keys(userlist), 2);
        assert.include(Object.keys(userlist), userdata1.id);
        assert.include(Object.keys(userlist), userdata2.id);
      },
      'the user list contains full userdata': function (error, res, body) {
        try {
          var userlist = JSON.parse(body);
        } catch(err) {
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
        url: baseurl+'/channels/'+cid1+'/users/',
        jar: j1,
      }, self.callback);

    },
    'server returns ok': function (error, res, body) {
      assert.equal(res.statusCode, 200);
    },
    'server returns user ids of this channel': function (error, res, body) {
      try {
        var userids = JSON.parse(body);
      } catch(err) {
        assert.isNull(err, 'response body should be JSON formated');
      }
      assert.lengthOf(userids, 2);
      assert.include(userids, userdata1.id);
      assert.include(userids, userdata2.id);
    },
  },

}).export(module);
