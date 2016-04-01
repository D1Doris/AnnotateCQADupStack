<?php

// Written by Doris Hoogeveen March 2016
// Downloaded from https://github.com/D1Doris/AnnotateCQADupStack

if(isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['hash']) && !empty($_GET['hash']) AND isset($_GET['subforum']) && !empty($_GET['subforum']) AND isset($_GET['username']) && !empty($_GET['username'])){
    
    $subforum = $_GET['subforum'];
    $conn = connect_to_db($subforum);

    $email = mysqli_real_escape_string($conn, $_GET['email']);
    $hash = mysqli_real_escape_string($conn, $_GET['hash']);
    $username = mysqli_real_escape_string($conn, $_GET['username']);

    $sql = "SELECT new_password FROM users WHERE email='".$email."' AND hash='".$hash."'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) { # There should be only one result.
	// output data of each row ^
    	while($row = $result->fetch_assoc()) {
	    $newpassword = $row["new_password"];
    	}

	$sql = "UPDATE users SET password='$newpassword' WHERE email='".$email."' AND hash='".$hash."'";
	if ($conn->query($sql) === TRUE) {
	    // Password successfully updated
	    get_started_again();
	} else {
            echo "Error: " . $sql . "<br>" . $conn->error;
            $title = 'Password reset';
            $msg = 'Something went wrong when resetting the new password. Please email Doris.';
            try_again($title, $msg);
        }	
    }else{ // Should never happen
	$title = 'Password reset';
        $msg = 'This user does not exist in the database. <a href="../new_user.html">Please register first</a>.';
        try_again($title, $msg);
    }
}else{
    $title = 'Password reset';
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


function get_started_again(){
    print '<!DOCTYPE html>
             <html lang="en">
                <head>
                    <link href="../se-annotate.css" rel="stylesheet" type="text/css" />
                    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon"/>
                    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset="UTF-8">
                    <title>Welcome back!</title>
                </head>
                <body>
                    <div id="content">
                    <center>
                        Welcome back! Your password has been successfully reset.<br /><br />
		        Please <a href="../index.html">log in</a> with your new credentials to continue the annotation!
                    </center>
                    </div>
                </body>
             </html>';
}

?>
