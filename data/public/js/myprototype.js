

function indexOf(array, object)
{
  for (var i = 0; i < array.length; i++)
    if (array[i] == object) return i;
  return -1;
}

function without(array,value) {
  var res = Array();
  for( var i = 0 ; i < array.length; i++)
  {
    if (array[i] != value) res.push(array[i]);
  }
  return res;
}
