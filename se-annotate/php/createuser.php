<?php

// Written by Doris Hoogeveen March 2016
// Downloaded from https://github.com/D1Doris/AnnotateCQADupStack

$password=$_POST['password'];
$password_again=$_POST['password_again'];
$username=$_POST['username'];
$emailaddress=$_POST['emailadres'];
$reputation=$_POST['reputation'];
$subforum=$_POST['subforum'];
$contactperm=$_POST['contactperm'];
$keepupdated=$_POST['keepupdated'];

$username=htmlspecialchars($username);
$password=htmlspecialchars($password);
$password_again=htmlspecialchars($password_again);

if($password!=$password_again){
    $title = 'Login';
    $msg = 'The two passwords are not the same.';
    try_again($title, $msg);
}else if ( preg_match('/\s/',$username) ){
    $title = 'Login';
    $msg = 'The username contains spaces. This is not allowed.';
    try_again($title, $msg);
}else if((!empty($username)) && (!empty($password)) && (!empty($password_again)) && (!empty($emailaddress)) && (!empty($reputation))){

	$conn = connect_to_db($subforum);

	$password=mysqli_real_escape_string($conn, $password);
	$password_again=mysqli_real_escape_string($conn, $password_again);
	$username=mysqli_real_escape_string($conn, $username);
	$emailaddress=mysqli_real_escape_string($conn, $emailaddress);
	$reputation=mysqli_real_escape_string($conn, $reputation);
	$subforum=mysqli_real_escape_string($conn, $subforum);

	// Check if user already exists
	$sql = "SELECT * FROM users WHERE email='".$emailaddress."'";
	$result = $conn->query($sql);

        if ($result->num_rows > 0) { # There should be only one result.
	    $title = 'Create user';
            $msg = 'We already have a registered user for the email address you supplied.';
            try_again($title, $msg);
	}else if (mb_strlen($password, 'UTF-8') < 8){
	    $title = 'Create user';
            $msg = 'The password needs to contain at least 8 characters.';
            try_again($title, $msg);
	}else{
	    // Add user to database
	    /*TODO: randomly generate Salts */
	    $salt = '$2a$07$usesomadasdsadsadsadasdasdasdsadesillystringfors';
            $digest = crypt($password, $salt);

	    $hash = md5( rand(0,1000) ); // Generate random 32 character hash and assign it to a local variable.
	    // Example output: f4552671f8909587cf485ea990207f3b

   	    $sql = "INSERT INTO users(userid, password, email, reputation, contactpermission, keepupdated, hash) VALUES ('$username', '$digest', '$emailaddress', '$reputation', '$contactperm', '$keepupdated', '$hash')";

            if ($conn->query($sql) === TRUE) {
		send_email($emailaddress, $username, $password, $hash, $subforum);
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
	   	$title = 'Create user';
                $msg = 'Something went wrong when adding the new user to the database.';
                try_again($title, $msg);
            }
	}
} else{
    $title = 'Login';
    $msg = 'One of the fields has not been filled out.';
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
		    <div id="content">'.$msg.'  <a href="../new_user.html">Please try again.</a>
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


function send_email($to, $username, $password, $hash, $subforum) {
    # Source: http://stackoverflow.com/questions/712392/send-email-using-the-gmail-smtp-server-from-a-php-page
    require_once '/path/to/swiftmailer/lib/swift_required.php';

    $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl")
        ->setUsername('gmail.username')
        ->setPassword('password');

    $mailer = Swift_Mailer::newInstance($transport);

    $body = 'Dear '.$username.',

Thank you for registering for the StackExchange Annotation Project of the University of Melbourne!

Your account has been created. You can login with the following credentials after you have activated your account by pressing the url below.

------------------------
Username: '.$username.'
Password: '.$password.'
Subforum: '.$subforum.'
------------------------

Your password will be stored encrypted. If you lose it, we cannot retrieve it. You will have to reset it instead.

Please click this link to activate your account: http://hum.csse.unimelb.edu.au/se-annotate/php/verify.php?subforum='.$subforum.'&username='.urlencode($username).'&email='.$to.'&hash='.urlencode($hash).'

If you would like to annotate questions for a different subforum, please register for that subforum separately.

Kind regards,

Doris Hoogeveen';

    $htmlbody = 'Dear <b>'.$username.'</b>,<br /><br />
Thank you for registering for the StackExchange Annotation Project of the University of Melbourne!<br /><br />
Your account has been created. You can login with the following credentials after you have activated your account by pressing the url below.<br /><br />
------------------------<br />
Username: '.$username.'<br />
Password: '.$password.'<br />
Subforum: '.$subforum.'<br />
------------------------<br /><br />
Your password will be stored encrypted. If you lose it, we cannot retrieve it. You will have to reset it instead.<br /><br />
Please click this link to activate your account:<br />
<a href="http://hum.csse.unimelb.edu.au/se-annotate/php/verify.php?subforum='.$subforum.'&username='.urlencode($username).'&email='.$to.'&hash='.urlencode($hash).'">http://hum.csse.unimelb.edu.au/se-annotate/php/verify.php?subforum='.$subforum.'&username='.urlencode($username).'&email='.$to.'&hash='.urlencode($hash).'</a><br /><br />
If you would like to annotate questions for a different subforum, please register for that subforum separately.
Kind regards,<br />
Doris Hoogeveen 
';


    $message = Swift_Message::newInstance('Registration confirmation for StackExchange Annotation Project')
        ->setFrom(array('your@gmail.com' => 'Your Name'))
        ->setTo(array($to))
        ->setBody($body)
	->addPart($htmlbody, 'text/html');

    $result = $mailer->send($message);
    if($result){

    print '<!DOCTYPE html>
             <html lang="en">
                <head>
                    <link href="../se-annotate.css" rel="stylesheet" type="text/css" />
                    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon"/>
                    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset="UTF-8">
                    <title>Registration</title>
                </head>
                <body>
		    <div id="contentsides">
                    <div id="content">
                    <center>
                        Thank you for registering. A confirmation email has been sent to you.<br /><br />
                        Please click on the link in the email to activate your account.<br /><br />
                        <a href="../index.html">Click here</a> to go back to the login page.
                    </center>
                    </div>
		    </div>
                </body>
             </html>';
    }else{
	print 'Could not send confirm email...';
    }
}



?>
