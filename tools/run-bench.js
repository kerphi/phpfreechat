require('shelljs/global');

// get apache process pid (with childs)
var pid  = cat('../run/apache2.pid').trim();
var pids = [];
exec('/usr/bin/pstree -p ' + pid, {silent: true}).output.split(/(\([0-9]+\))/).forEach(function (elt) {
  if (elt.match(/\([0-9]+\)/)) {
    pids.push(elt.slice(1,-1));
  }
});

// start several user session simulation to stress the server
// exec('make simulate-user-session', {async: true});

// start process monitoring
// -d for disk stats
// -r for memory stats
// -u for cpu stats
// -h for all on one line (but do not display average)
// 1 5 means check each second during 5 seconds
exec('pidstat -h -d -u -r -p ' + pids.join(',') + ' 1 5',
     {silent: true, async: true},
     function(code, output) {
  console.log('Exit code:', code);
  console.log('Program output:', output);
});
