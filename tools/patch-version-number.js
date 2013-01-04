#!/usr/bin/env node
/**
 * Script in charge of patch the pfc's version number into differents files
*/

var fs = require('fs');
var argv = require('optimist').argv;
var glob = require('glob');
var version = argv.version; 

if (RegExp('^[0-9]\.[0-9]+\.[0-9]+$').test(version)) {
  
  var c = '';
  
  // config.php: $GLOBALS['pfc_version'] = '2.0.3';
  c = fs.readFileSync('server/config.php', 'utf8');
  c = c.replace(RegExp(".*pfc_version.*\n"), function (line) {
    return line.replace(RegExp('[0-9]\.[0-9]+\.[0-9]+'), version);
  });
  fs.writeFileSync('server/config.php', c, 'utf8');

  // package.json: "version": "2.0.3",
  c = fs.readFileSync('package.json', 'utf8');
  c = c.replace(RegExp('[0-9]\.[0-9]+\.[0-9]+'), version);
  fs.writeFileSync('package.json', c, 'utf8');  
  
  // README.md: phpfreechat-2.0.0
  glob.sync(__dirname + '/../doc/*.md').forEach(function (docf) {
    c = fs.readFileSync(docf, 'utf8');
    // must not convert [phpfreechat-x.x.x] because used by bench section
    c = c.replace(RegExp('/phpfreechat-[0-9]\.[0-9]+\.[0-9]+', 'g'), '/phpfreechat-' + version); // in html tags
    c = c.replace(RegExp(' phpfreechat-[0-9]\.[0-9]+\.[0-9]+', 'g'), ' phpfreechat-' + version); // in text
    c = c.replace(RegExp('(.*)d \\[phpfreechat-[0-9]\.[0-9]+\.[0-9]+', 'g'), '$1d [phpfreechat-' + version); // in text (quick start)
    fs.writeFileSync(docf, c, 'utf8');  
    process.stdout.write(docf + ' patched\n');
  });
  
} else {
  process.stderr.write('Wrong version format (should be n.n.n)\n');
}