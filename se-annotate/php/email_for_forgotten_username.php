<?php

// Written by Doris Hoogeveen March 2016
// Downloaded from https://github.com/D1Doris/AnnotateCQADupStack

$emailaddress=$_POST['emailadres'];
$subforum=$_POST['subforum'];

if(!empty($emailaddress)){

        $conn = connect_to_db($subforum);

	$emailaddress=mysqli_real_escape_string($conn, $emailaddress);
	$subforum=mysqli_real_escape_string($conn, $subforum);

        // Check if user already exists
        $sql = "SELECT * FROM users WHERE email='".$emailaddress."'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) { # There should be only one result.

	    // If we find the email address for this subforum, retrieve the username and email it to the email address.

	    // output data of each row ^. Should be only one.
            while($row = $result->fetch_assoc()) {
                $username = $row["userid"];
            }
  	    send_email($emailaddress, $username, $subforum);
        }else{
	    $title = 'Retrieve username';
            $msg = 'The email address you supplied does not exist yet for this subforum. <a href="../new_user.html">Please register first.</a>';
            try_again($title, $msg);
        }
} else{
    $title = 'Retrieve username';
    $msg = 'No email address has been supplied. <a href="../forgot_username.html">Please try again.</a>';
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


function send_email($to, $username, $subforum) {
    # Source: http://stackoverflow.com/questions/712392/send-email-using-the-gmail-smtp-server-from-a-php-page
    require_once '/path/to/swiftmailer/lib/swift_required.php';

    $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl")
        ->setUsername('gmail.username')
        ->setPassword('password');

    $mailer = Swift_Mailer::newInstance($transport);



    $body = 'Dear '.$username.',

We found your username!

------------------------
Username: '.$username.'
Subforum: '.$subforum.'
------------------------

You can now continue your annotations at http://hum.csse.unimelb.edu.au/se-annotate/.

Kind regards,

Doris Hoogeveen';

    $htmlbody = 'Dear '.$username.',<br /><br />
We found your username!<br /><br />
------------------------<br />
Username: '.$username.'<br />
Subforum: '.$subforum.'<br />
------------------------<br /><br />

You can now continue your annotations at <a href="http://hum.csse.unimelb.edu.au/se-annotate/">http://hum.csse.unimelb.edu.au/se-annotate/</a><br /><br />
Kind regards,<br />
Doris Hoogeveen 
';

    $message = Swift_Message::newInstance('Retrieved username for StackExchange Annotation Project')
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
                    <title>Retrieved username</title>
                </head>
                <body>
		    <div id="contentsides">
                    <div id="content">
                    <center>
                        We found your username! It has been emailed to you.<br /><br />
                        <a href="../index.html">Click here</a> to go back to the login page.
                    </center>
                    </div>
		    </div>
                </body>
             </html>';
    }else{
        print 'Could not send username retrieval email...';
    }
}




?>
