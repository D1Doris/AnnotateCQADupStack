<?php //controleren van gebruikersnaam en wachtwoord

// Written by Doris Hoogeveen March 2016
// Downloaded from https://github.com/D1Doris/AnnotateCQADupStack

$password=$_POST['password'];
$username=$_POST['username'];
$email=$_POST['email'];
$subforum=$_POST['subforum'];

$username=htmlspecialchars($username);
$password=htmlspecialchars($password);


if((!empty($username)) && (!empty($password)) && (!empty($email))){

	$conn = connect_to_db($subforum);

	$password=mysqli_real_escape_string($conn, $password);
	$username=mysqli_real_escape_string($conn, $username);
	$email==mysqli_real_escape_string($conn, $email);
	$subforum=mysqli_real_escape_string($conn, $subforum);

	$sql = "SELECT * FROM users WHERE userid='".$username."' AND email='".$email."'";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) { # Should be only one result.
	    // Check if user has verified his/her password

    	    // output data of each row
    	    while($row = $result->fetch_assoc()) {
		if ($row["active"]=='0'){
		    $title = 'Login';
                    $msg = 'You have not verified your email address yet. Please click the link in the email that has been sent to you.';
                    try_again($title, $msg);
		}else{
		    $stored_password = $row["password"];
		    if(crypt($password, $stored_password) === $stored_password){
			$active = $row["active"];
			$newactive = $active + 1;
			$userid = $row["usernr"];
			$sql = "UPDATE users SET active='".$newactive."' WHERE userid='".$username."' AND email='".$email."'";
        		if ($conn->query($sql) === TRUE) {
            		    // Successfully updated nr of logins
			} // No else, because if we fail we don't care too much. I can actually get rid of the if-statement all together..
                        setcookie("ingelogd","ja",time()+3600,'/');
                        setcookie("se_username",$username,time()+3600,'/');
			setcookie("se_userid",$userid,time()+3600,'/');
                        setcookie("subforum",$subforum,time()+3600,'/');
                        header('Location: ./present_transitives.php');
                    }else{
                        $title = 'Login';
                        $msg = 'The supplied combination of user and password is not known for this subforum. <a href="../index.html">Please try again.</a>';
                        try_again($title, $msg);
                    }
		}
    	    }
	} else {
	    $title = 'Login';
            $msg = 'The supplied combination of user, email address and password is not known for this subforum. <a href="../index.html">Please try again.</a>';
            try_again($title, $msg);
	}

} else{
    $title = 'Login';
    $msg = 'No value has been given for either the username, the email address or the password. <a href="../index.html">Please try again.</a>';
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
    $conn = new mysqli($servername, $dbuser, $dbpassword, $dbname);
    // Check connection
    if ($conn->connect_error) {
        //die("Connection failed: " . $conn->connect_error);
        $title = 'Create user';
        $msg = 'Something went wrong when connecting to the database to add the new user.';
        try_again($title, $msg);
    }

    return $conn;
}



?>
