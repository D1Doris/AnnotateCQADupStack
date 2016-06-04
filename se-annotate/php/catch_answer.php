<?php 

$userid=$_COOKIE['se_userid'];
$subforum=$_COOKIE['subforum'];
$ingelogd=$_COOKIE['ingelogd'];

if($ingelogd != 'ja'){
    notloggedin();
}else{
    // Get question ids from URL
    $querystring = $_SERVER['QUERY_STRING'];
    parse_str($querystring, $urlparams);
    $id1 = $urlparams['id1'];
    $id2 = $urlparams['id2'];
    $pair = $id1.'-'.$id2;

    // Find out which button was clicked
    if (isset($_POST['dup'])) {
        $sql = "Update table_".$userid." SET verdict='1' WHERE pair='".$pair."'";
    }else if (isset($_POST['nodup'])){
        $sql = "Update table_".$userid." SET verdict='0' WHERE pair='".$pair."'";
    }else if (isset($_POST['related'])){
	$sql = "Update table_".$userid." SET verdict='0.75' WHERE pair='".$pair."'";
    }else{ // assume unclear
	$sql = "Update table_".$userid." SET verdict='0.5' WHERE pair='".$pair."'";
    }

    // Add verdict to database
    $conn = connect_to_db($subforum);
    if ($conn->query($sql) === TRUE) {
	header('Location: ./present_pair.php');        
    }else{
	echo "Something went wrong when processing the verdict!";
    }



}

function connect_to_db($subforum){

    $servername = "localhost";
    $dbuser = "someuser";
    $dbpassword = "somepassword";
    $dbname = $subforum;

    // Create connection
    $conn = mysqli_connect($servername, $dbuser, $dbpassword, $dbname);

    // Check connection
    if (!$conn) {
        //die("Connection failed: " . mysqli_connect_error());
        $title = 'Processing verdict';
        $msg = 'Something went wrong when connecting to the database to process the verdict.';
        try_again($title, $msg);
    }
    return $conn;
}


function notloggedin(){
    print '<!DOCTYPE html>
             <html lang="en">
                <head>
                    <link href="../se-annotate.css" rel="stylesheet" type="text/css" />
                    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon"/>
                    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset="UTF-8">
                    <title>Not logged in</title>
                </head>
                <body>
		    <div id="logo">
                        <center><img src="../UoM-logo.jpg" alt="Your logo"/></center>
                    </div>
		    <div id="contentsides">
                    <div id="content">
                    <center>
			I\'m sorry. You have been inactive for too long and have been logged out. <br /><br />
		        All your answers have been saved. Please <a href="../index.html">log in</a> again to continue.
                    </center>
                    </div>
		    </div>
                </body>
             </html>';
}

?>



