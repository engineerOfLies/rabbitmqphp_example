#!/usr/bin/php
<?php
echo "Test PHP BEGIN".PHP_EOL;
$arr = array();
$arr[] = 5;
$arr[] = 'potato';
$arr[] = 8.371;
$arr['tomato'] = "red";
$arr[] = "blue";

function thaFunc($word, array $arrr = NULL)
{
  if ($arrr === NULL)
  {
    echo __FILE__.":".__LINE__.": NULL ARRAY".PHP_EOL;
    return NULL; 
  }
  $return = "";
  foreach ($arrr as $ar)
  {
    echo __FILE__.":".__LINE__.": $word - $ar".PHP_EOL;
    $return .= $ar;
  }
  return $return;
}
var_dump($arr);

echo __FILE__.":".__LINE__.":array size = ".count($arr).PHP_EOL;

foreach ($arr as $yarrr)
{
  echo "array value:\" $yarrr".PHP_EOL;
}
echo "C style:\n";
for ($i = 0;$i < count($arr);$i++)
{
  echo "array value: ".$arr[$i].PHP_EOL;
}

$ret = thaFunc("steve");

$ret = thaFunc("steve",$arr);

echo $ret.PHP_EOL;

echo "Test PHP END".PHP_EOL;

?>
