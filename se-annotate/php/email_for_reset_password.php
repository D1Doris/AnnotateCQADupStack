<?php

// Written by Doris Hoogeveen March 2016
// Downloaded from https://github.com/D1Doris/AnnotateCQADupStack

$password=$_POST['password'];
$password_again=$_POST['password_again'];
$username=$_POST['username'];
$emailaddress=$_POST['emailadres'];
$subforum=$_POST['subforum'];

$username=htmlspecialchars($username);
$password=htmlspecialchars($password);

if($password!=$password_again){
    $title = 'Reset password';
    $msg = 'The two passwords are not the same. <a href="../index.html">Please try again.</a>';
    try_again($title, $msg);
}else if(mb_strlen($password, 'UTF-8') < 8){
    $title = 'Reset password';
    $msg = 'The password needs to contain at least 8 characters. <a href="../index.html">Please try again.</a>';
    try_again($title, $msg);    
}else if((!empty($username)) && (!empty($password)) && (!empty($password_again)) && (!empty($emailaddress))){

        $conn = connect_to_db($subforum);

	$password=mysqli_real_escape_string($conn, $password);
	$password_again=mysqli_real_escape_string($conn, $password_again);
	$username=mysqli_real_escape_string($conn, $username);
	$emailaddress=mysqli_real_escape_string($conn, $emailaddress);
	$subforum=mysqli_real_escape_string($conn, $subforum);

        // Check if user already exists
        $sql = "SELECT * FROM users WHERE userid='".$username."'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) { # There should be only one result.
	    // Add new password to database
            /*TODO: randomly generate Salts */
            $salt = '$2a$07$usesomadasdsadsadsadasdasdasdsadesillystringfors';
            $digest = crypt($password, $salt);

            $hash = md5( rand(0,1000) ); // Generate random 32 character hash and assign it to a local variable.
            // Example output: f4552671f8909587cf485ea990207f3b

	    $sql = "UPDATE users SET new_password='$digest' WHERE email='".$emailaddress."' AND userid='$username'";
	    $sql2 = "UPDATE users SET hash='$hash' WHERE email='".$emailaddress."' AND userid='$username'";
            if (($conn->query($sql) === TRUE) && ($conn->query($sql2) === TRUE)){
                // New password stored
		send_email($emailaddress, $username, $password, $hash, $subforum);
	    }else{
		echo "Error: " . $sql . "<br>" . $conn->error;
                $title = 'Reset password';
                $msg = 'Something went wrong when adding the new password to the database. <a href="../index.html">Please try again.</a>';
                try_again($title, $msg);
	    }
        }else{
	    $title = 'Create user';
            $msg = 'The username you supplied does not exist yet for this subforum. <a href="../new_user.html">Please register first.</a>';
            try_again($title, $msg);
        }
} else{
    $title = 'Login';
    $msg = 'One of the fields has not been filled out. <a href="../index.html">Please try again.</a>';
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


function send_email($to, $username, $password, $hash, $subforum) {
    # Source: http://stackoverflow.com/questions/712392/send-email-using-the-gmail-smtp-server-from-a-php-page
    require_once '/path/to/swiftmailer/lib/swift_required.php';

    $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl")
        ->setUsername('gmail.username')
        ->setPassword('password');

    $mailer = Swift_Mailer::newInstance($transport);



    $body = 'Dear '.$username.',

Your password has been reset. You can login with the following credentials after you have activated your new password by pressing the url below.

------------------------
Username: '.$username.'
Password: '.$password.'
Subforum: '.$subforum.'
------------------------

Your password will be stored encrypted. If you lose it, we cannot retrieve it. You will have to reset it again instead.

Please click the following link to activate your new password: http://hum.csse.unimelb.edu.au/se-annotate/php/reset_password.php?subforum='.$subforum.'&username='.urlencode($username).'&email='.$to.'&hash='.urlencode($hash).'

Kind regards,

Doris Hoogeveen';

    $htmlbody = 'Dear '.$username.',<br /><br />
Your password has been reset. You can login with the following credentials after you have activated your new password by pressing the url below.<br /><br />
------------------------<br />
Username: '.$username.'<br />
Password: '.$password.'<br />
Subforum: '.$subforum.'<br />
------------------------<br /><br />
Your password will be stored encrypted. If you lose it, we cannot retrieve it. You will have to reset it again instead.<br /><br />
Please click this link to activate your new password:<br />
<a href="http://hum.csse.unimelb.edu.au/se-annotate/php/reset_password.php?subforum='.$subforum.'&username='.urlencode($username).'&email='.$to.'&hash='.urlencode($hash).'">http://hum.csse.unimelb.edu.au/se-annotate/php/reset_password.php?subforum='.$subforum.'&username='.urlencode($username).'&email='.$to.'&hash='.urlencode($hash).'</a><br /><br />
Kind regards,<br />
Doris Hoogeveen 
';

    $message = Swift_Message::newInstance('Password reset for StackExchange Annotation Project')
        ->setFrom(array('your@gmail.com' => 'Your Name'))
        ->setTo(array($to))
        ->setBody($body)
        ->addPart($htmlbody, 'text/html')
	->setCharset('ISO-8859-1'); // Should fix this and make it utf-8

    $result = $mailer->send($message);
    if($result){

    print '<!DOCTYPE html>
             <html lang="en">
                <head>
                    <link href="../se-annotate.css" rel="stylesheet" type="text/css" />
                    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon"/>
                    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset="UTF-8">
                    <title>Reset password</title>
                </head>
                <body>
		    <div id="contentsides">
                    <div id="content">
                    <center>
                        Your password has been reset. A confirmation email has been sent to you.<br />
                        Please click on the link in the email to activate your new password.<br /><br />
                        <a href="../index.html">Click here</a> to go back to the login page.
                    </center>
                    </div>
		    </div>
                </body>
             </html>';
    }else{
        print 'Could not send password reset email...';
    }
}




?>
