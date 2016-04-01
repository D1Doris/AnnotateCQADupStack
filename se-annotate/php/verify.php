<?php

// Written by Doris Hoogeveen March 2016
// Downloaded from https://github.com/D1Doris/AnnotateCQADupStack

if(isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['hash']) && !empty($_GET['hash']) AND isset($_GET['subforum']) && !empty($_GET['subforum']) AND isset($_GET['username']) && !empty($_GET['username'])){

    $subforum = $_GET['subforum'];
    $conn = connect_to_db($subforum);

    $email = mysqli_real_escape_string($conn, $_GET['email']);
    $hash = mysqli_real_escape_string($conn, $_GET['hash']);
    $username = mysqli_real_escape_string($conn, $_GET['username']);

    $sql = "SELECT email, hash, active FROM users WHERE email='".$email."' AND hash='".$hash."' AND active is NULL";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) { # There should be only one result.
	$sql = "UPDATE users SET active='1' WHERE email='".$email."' AND hash='".$hash."' AND active is NULL";
	if ($conn->query($sql) === TRUE) {
	    // User successfully activated
	    $userid = create_user_table($conn, $subforum, $username, $email, $hash);
	    // Find nr of question pairs to be annotated for this subforum
            $sql = "SELECT * FROM se_".$userid;
	    $result = $conn->query($sql);
	    $nrofpairs = $result->num_rows;
	    // Set cookies
	    setcookie("ingelogd","ja",time()+3600,'/');
            setcookie("se_username",$username,time()+3600,'/');
	    setcookie("se_userid",$username,time()+3600,'/');
            setcookie("subforum",$subforum,time()+3600,'/');
	    get_started($nrofpairs, $subforum);
	} else {
            echo "Error: " . $sql . "<br>" . $conn->error;
            $title = 'Activating user';
            $msg = 'Something went wrong when activating the new user. Please email Doris.';
            try_again($title, $msg);
        }	
    }else{ // Should never happen
	$title = 'Activating user';
        $msg = 'Either you have already clicked the verification link, or this user does not exist in the database. Please <a href="../index.html">log in or register</a> on the home page.';
        try_again($title, $msg);
    }
}else{
    $title = 'Activating user';
    $msg = 'Either the email or the hash parameters are missing. Please check if you have copied the complete URL from the email.';
    try_again($title, $msg);
}


function try_again($title, $msg) {
    print '<!DOCTYPE html>
             <html lang="en">
                <head>
		    <link href="../se-annotate.css" rel="stylesheet" type="text/css" />
		    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon"/>
		    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset="UTF-8">
                    <title>'.$title.'</title>
                </head>
                <body>
		    <div id="contentsides">
		    <div id="content">'.$msg.'
		    </div>
		    </div>
		</body>
             </html>';
} 


function connect_to_db($subforum) {
    
    $servername = "localhost";
    $dbuser = "root";
    $dbpassword = "";
    $dbname = $subforum;

    // Create connection
    $conn = mysqli_connect($servername, $dbuser, $dbpassword, $dbname);

    // Check connection
    if (!$conn) {
        //die("Connection failed: " . mysqli_connect_error());
	$title = 'Create user';
        $msg = 'Something went wrong when connecting to the database to add the new user.';
        try_again($title, $msg);
    }
    return $conn;
}


function create_user_table($conn, $subforum, $username, $email, $hash) {
    // First get the usernr from the users table.
    $sql = "SELECT usernr FROM users WHERE email='".$email."' AND hash='".$hash."'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) { # There should be only one result. And it should find something!
	// output data of each row ^
        while($row = $result->fetch_assoc()) {
            $userid = $row["usernr"];
        }	
    }
    // sql to create table
    $sql = "CREATE TABLE se_".$userid." (
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
        $sql = "INSERT INTO se_".$userid."(pair,verdict) VALUES ('".$pairid."', 'noverdict')";

        if ($conn->query($sql) === TRUE) {
            // Do nothing. Record inserted successfully.
            //echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    return $userid;
}


function get_started($nrofpairs, $subforum){
    print '<!DOCTYPE html>
             <html lang="en">
                <head>
                    <link href="../se-annotate.css" rel="stylesheet" type="text/css" />
                    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon"/>
                    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset="UTF-8">
                    <title>Welcome aboard!</title>
                </head>
                <body>
                    <div id="content">
                    <center>
                        Welcome aboard! Thank you for confirming your registration.<br /><br />
			The <b>'.$subforum.'</b> subforum has '.$nrofpairs.' question pairs to be annotated.<br /><br />
		        Please <a href="../index.html">log in</a> to get started!
                    </center>
                    </div>
                </body>
             </html>';
}

?>
