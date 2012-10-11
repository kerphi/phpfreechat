require('shelljs/global');
var package = require('../package.json');

// start N user session to stress the server
var nb_users = 50;
while (nb_users-- > 0) {
  exec('make simulate-user-session', {silent: true, async: true});
}

// get apache process pid (with childs)
var pid  = cat('../run/apache2.pid').trim();

// start process monitoring after 10 seconds
// and stop it 10 seconds before stress end
// (time to have something stabilized)
setTimeout(monitor_pids, 10000);
setTimeout(calculate_ressources_average, 50000);
var records = [];
var monitor = true;
function monitor_pids() {
  var pids = get_pids();
  // -d for disk stats
  // -r for memory stats
  // -u for cpu stats
  // -h for all on one line (but do not display average)
  // 1 1 means check each second during 1 second
  exec('pidstat -h -d -u -r -p ' + pids.join(',') + ' 1 1', {silent: true, async: true}, function (code, output) {
    var record = { pid: [], cpu: 0, mem_vsz: 0, mem_rss: 0, disk_read: 0, disk_write:0 };
    
    // parse the results
    output.split('\n').forEach(function (line) {
      // cleanup the line
      line = line.trim();
      if (line == '') {
        return;
      }
      
      // fill the record
      if (line.match(/^[0-9]/)) {
        var fields = line.split(/ +/);
        record.timestamp  = parseInt(fields[0]);
        record.pid.push(parseInt(fields[1]));
        record.cpu        += parseFloat(fields[5].replace(',', '.'));
        record.mem_vsz    += parseInt(fields[9]);
        record.mem_rss    += parseInt(fields[10]);
        record.disk_read  += parseFloat(fields[12].replace(',', '.')*1000);
        record.disk_write += parseFloat(fields[13].replace(',', '.')*1000);
      }

    });
    records.push(record);
  });
  
  if (monitor) {
    process.stdout.write('.'); // to have an indicator that something is working
    setTimeout(monitor_pids, 2000); // monitoring each 2 seconds
  }
}
function calculate_ressources_average() {
    monitor = false; // stop monitoring
    
    // agregate and calculate heuristic
    var average = { cpu: 0, mem_vsz: 0, mem_rss: 0, disk_read: 0, disk_write:0 };
    records.forEach(function (r) {
      average.cpu        += r.cpu;
      average.mem_vsz    += r.mem_vsz;
      average.mem_rss    += r.mem_rss;
      average.disk_read  += r.disk_read;
      average.disk_write += r.disk_write;
    });
    average.cpu        = Math.round(average.cpu/records.length);
    average.mem_vsz    = Math.round(average.mem_vsz/records.length);
    average.mem_rss    = Math.round(average.mem_rss/records.length);
    average.disk_read  = Math.round(average.disk_read/records.length);
    average.disk_write = Math.round(average.disk_write/records.length);
    
    console.log('');
    //console.log(average);
     
    var heuristic = ((average.disk_write / 14000000) + (average.disk_read / 40000))*2 +
                    (average.mem_rss / 42000)*5 +
                    (average.cpu / 60)*10;
    console.log('[' + new Date().toUTCString() + '] [' 
                    + package.name + '-' + package.version 
                    + '] Bench result: ' + heuristic.toFixed(2)
                    + ' (cpu=' + average.cpu + '% mem=' + Math.round(average.mem_rss/1024) 
                    + 'Mo dread=' + Math.round(average.disk_read/1000) + 'k dwrite=' + Math.round(average.disk_write/1000) + 'k)');
    
}

function get_pids() {
  var pids = [];
  exec('/usr/bin/pstree -p ' + pid, {silent: true}).output.split(/(\([0-9]+\))/).forEach(function (elt) {
    if (elt.match(/\([0-9]+\)/)) {
      pids.push(elt.slice(1,-1));
    }
  });
  return pids;
}

