<?php
/**
 * Created by PhpStorm.
 * User: Nick
 * Date: 10/17/2014
 * Time: 7:19 PM
 */

$username = strval($_GET['username']);
$setString = strval($_GET['set']);

$set = json_decode($setString);

$setID = $set -> setID;

$values = $set -> values;

$numValues = count($values);

$exerciseID = $set->exerciseID;

class saveNewSetValues extends SQLite3
{
    function __construct()
    {
        $this->open('../users.db');
    }
}

$db = new saveNewSetValues();

for($i = 0; $i < $numValues; $i++) {

    $value = $values[$i]->value;

    $sql = "insert into exerciseSetValues (username, exerciseID, setID, value)".
        "values ('".$username."',".$exerciseID.",".$setID.",'".$value."');";

    $ret = $db->exec($sql);
}

//retrieve the id of the values to put back into the array
$sql2 = "select valueID from exerciseSetValues where setID = ".$setID.";";

$ret2 = $db->query($sql2);

$retArray = array();

while($row = $ret2->fetchArray(SQLITE3_ASSOC) ) {
    $retArray[] = $row['valueID'];
}

$setValuesIDsArray = json_encode($retArray);

echo $setValuesIDsArray;