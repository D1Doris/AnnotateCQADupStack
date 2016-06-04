<?php

$userid=$_COOKIE['se_userid'];
$display_name=$_COOKIE['display_name'];
$subforum=$_COOKIE['subforum'];

$conn = connect_to_db($subforum);

create_user_table($conn, $subforum, $userid);

// Find nr of question pairs to be annotated for this subforum
$sql = "SELECT * FROM table_".$userid;
$result = $conn->query($sql);
$nrofpairs = $result->num_rows;

// Set cookies
setcookie("ingelogd","ja",time()+3600,'/');

get_started($nrofpairs, $subforum, $display_name);




function create_user_table($conn, $subforum, $userid) {

    $tablename = "table_$userid";

    // sql to create table
    $sql = "CREATE TABLE ".$tablename." (
            pairid INT NOT NULL AUTO_INCREMENT,
            pair VARCHAR(15) NOT NULL,
            verdict VARCHAR(20),
            primary key(pairid)
            )";

    if ($conn->query($sql) === TRUE) {
        // Do nothing. Everything went fine.
    } else {
        echo "Error creating table: " . $conn->error;
    }

    // Fill the new table with question pairs
    $file = "../csv/".$subforum."_annotation_candidates.csv";
    try {
        $csv  = new SplFileObject($file, 'r');
    } catch (RuntimeException $e ) {
        printf("Error opening csv: %s\n", $e->getMessage());
    }

    while(!$csv->eof() && ($row = $csv->fgetcsv()) && $row[0] !== null) {
        // $row is a numerical keyed array with a string per each field (zero based): Array ( [0] => 158 [1] => 1349 )
        // Maar helaas is ie soms Array ( [0] => 44460 48537 ) ondanks dat beide csv files comma separated zijn...
        if (sizeof($row) == 1) {
            $row = preg_split("/[^0123456789]+/", $row[0]);
        }
        $pairid = $row[0]."-".$row[1];
        $sql = "INSERT INTO ".$tablename."(pair,verdict) VALUES ('".$pairid."', 'noverdict')";

        if ($conn->query($sql) === TRUE) {
            // Do nothing. Record inserted successfully.
            //echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    return;
}


function get_started($nrofpairs, $subforum, $display_name){
    print '<!DOCTYPE html>
             <html lang="en">
                <head>
                    <link href="../se-annotate.css" rel="stylesheet" type="text/css" />
                    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon"/>
                    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset="UTF-8">
                    <title>Welcome aboard!</title>
                </head>
                <body>
                    <div id="logo">
                        <center><img src="../UoM-logo.jpg" alt="Your logo"/></center>
                    </div>
                    <div id="contentsides">
                    <div id="content">
                    <center>
                        Welcome aboard, '.$display_name.'!<br /><br />
                        The <b>'.$subforum.'</b> subforum has '.$nrofpairs.' question pairs to be annotated.<br /><br />
                        Please <a href="./present_pair.php">click here</a> to get started!
                    </center>
                    </div>
                    </div>
                </body>
             </html>';
}



function connect_to_db($subforum) {
    
    $servername = "localhost";
    $dbuser = "someuser";
    $dbpassword = "somepassword";
    $dbname = $subforum;

    // Create connection
    $conn = mysqli_connect($servername, $dbuser, $dbpassword, $dbname);

    // Check connection
    if (!$conn) {
        //die("Connection failed: " . mysqli_connect_error());
	$title = 'Create user';
        $msg = 'Something went wrong when connecting to the database to add the new user. <a href="../new_user.html">Please try again</a> or contact Doris Hoogeveen.';
        try_again($title, $msg);
    }
    return $conn;
}


?>
