#!/usr/bin/env node

var vows = require('vows'),
    assert = require('assert'),
    request = require('request'),
    async = require('async'),
    querystring = require('querystring'),
    baseurl = 'http://127.0.0.1:32773/server-slim',
    j1 = request.jar(),
    j2 = request.jar(),
    userdata1 = {},
    userdata2 = {},
    cid1 = 'cidtimeout_1',
    cid2 = 'cidtimeout_2';

vows.describe('User data').addBatch({

  'when connected user check his data': {
    topic: function () {
      var self = this;
      
      // user1 auth
      request({
        method: 'GET',
        url: baseurl+'/auth',
        headers: { 'Pfc-Authorization': 'Basic '+new Buffer("testdata1:password").toString('base64') }, 
        jar: j1,
      }, function (err, res, body) {
        userdata1 = JSON.parse(body); 
        
        // check that the userdata are available
        request({
          method: 'GET',
          url: baseurl+'/users/'+userdata1.id+'/',
          jar: j1,
        }, self.callback);
        
      });
            
    },
    'server returns a success code': function (error, res, body) {
      assert.equal(res.statusCode, 200);      
    },
    'server returns user1 data': function (error, res, body) {
       try {
        var data = JSON.parse(body);
      } catch(err) {
        assert.isNull(err, 'response body should be JSON formated');
      }
      assert.equal(data.name, 'testdata1');  
    },
  },

  
}).export(module);
