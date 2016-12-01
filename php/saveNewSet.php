<?php
/**
 * Created by PhpStorm.
 * User: Nick
 * Date: 10/16/2014
 * Time: 9:33 PM
 */


$username = strval($_POST['username']);
$setString = strval($_POST['set']);

$set = json_decode($setString);

$exerciseID = $set->exerciseID;

class saveNewSet extends SQLite3
{
    function __construct()
    {
        $this->open('../users.db');
    }
}

$db = new saveNewSet();

//save new set into db
$sql = "insert into exerciseSets (username, exerciseID)".
    "values ('".$username."',".$exerciseID.");";

$ret = null;
$running = true;
while ($running) {

    $ret = $db->exec($sql);

    if ($ret) {
        $running = false;
    }
}

//retrieve the id of the set to put back into the array
$sql2 = "select setID from exerciseSets where exerciseID = ".$exerciseID.";";

$ret2 = null;
$running = true;
while ($running) {

    $ret2 = $db->query($sql2);

    if ($ret2) {
        $running = false;
    }
}

$setIDArray = array();

while($row = $ret2->fetchArray(SQLITE3_ASSOC) ) {
    $setIDArray[] = $row['setID'];
}

$setID = 0;
for ($i = 0; $i < count($setIDArray); $i++) {
    if ($setIDArray[$i] > $setID) {
        $setID = $setIDArray[$i];
    }
}

$set->setID = $setID;

//insert values into DB
$values = $set->values;
for ($valueNum = 0; $valueNum < count($values); $valueNum++) {
    $value = $values[$valueNum]->value;

    $sql = "insert into exerciseSetValues (username, exerciseID, setID, value)" .
        "values ('" . $username . "'," . $exerciseID . "," . $setID . ",'" . $value . "');";

    $running = true;
    while ($running) {

        $ret = $db->exec($sql);

        if ($ret) {
            $running = false;
        }
    }

    //retrieve the id of the values to put back into the array
    $sql2 = "select valueID from exerciseSetValues where setID = " . $setID . ";";

    $running = true;
    while ($running) {

        $ret2 = $db->query($sql2);

        if ($ret2) {
            $running = false;
        }
    }

    $valueIDArray = array();

    while ($row = $ret2->fetchArray(SQLITE3_ASSOC)) {
        $valueIDArray[] = $row['valueID'];
    }

    $valueID = 0;
    for ($i = 0; $i < count($valueIDArray); $i++) {
        if ($valueIDArray[$i] > $valueID) {
            $valueID = $valueIDArray[$i];
        }
    }

    $set->values[$valueNum]->ID = $valueID;
    $set->values[$valueNum]->setID = $setID;
    $set->values[$valueNum]->exerciseID = $exerciseID;


}


$setString = json_encode($set);

echo $setString;
