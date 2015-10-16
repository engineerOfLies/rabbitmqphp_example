#!/usr/bin/php
<?php
$connection = new MongoClient( "mongodb://testadmin:test1234@ds041633.mongolab.com:41633/sys_integration" );
$mongodb = $connection->selectDB('sys_integration');
$collection = new MongoCollection($mongodb,'loghistory');
var_dump($connection);
var_dump($collection);
//$collection->insert(array("username"=>"steeeve"));

//echo "data inserted\n";
$cursor = $collection->find(array("username"=>"steeeve"));
echo "find results:\n";
foreach ($cursor as $doc)
{
  var_dump($doc);
}
?>
