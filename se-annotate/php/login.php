<?php 

$subforum=$_COOKIE['subforum'];

// If a user is already logged in, don't log them in again, because logging people in is slow.
if ((isset($_COOKIE['access_token'])) && 
    (isset($_COOKIE['se_userid'])) && 
    (isset($_COOKIE['display_name']))) {

	// Check if the logged in user has an account for the chosen subforum.
	// He/she may have selected a different one from the one when they first logged in.
	$conn = connect_to_db($subforum);

        $tablename = "table_".$_COOKIE['se_userid'];
        if ($conn->query("DESCRIBE $tablename")) {
                setcookie("ingelogd","ja",time()+3600,'/');
                header('Location: ./present_pair.php');
		exit();
        }
	// If the table doesn't exist, we need to go through the normal steps below.        

}

$access_token = get_access_token();
$userdata = retrieve_user_data($access_token, $subforum);
check_user($userdata, $subforum);



function get_access_token(){

	$querystring = $_SERVER['QUERY_STRING'];
	parse_str($querystring, $urlparams);
	$code = $urlparams['code']; // Dit lijkt op de client_secret. Het is eenzelfde soort code.

	// Send an http POST request
	// Bron: http://stackoverflow.com/questions/5647461/how-do-i-send-a-post-request-with-php
	// De non-curl versie werkte niet voor me.

	$url = 'https://stackexchange.com/oauth/access_token';
	$data = array('client_id' => 'your_client_id', 'client_secret' => 'your_client_secret', 'code' => "$code", 'redirect_uri' => 'http://your_server/se-annotate/php/login.php');

	//open connection
	$ch = curl_init();

	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_POST,TRUE);
	curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($data));
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, TRUE);

	//execute post
	$result = curl_exec($ch); // Something like: access_token=HI8qsWtdzVkP6ulZAxo5Yw))&expires=864001

	// Parse result
	parse_str($result, $authparams);
	$access_token = $authparams['access_token'];
	setcookie("access_token",$access_token,time()+3600,'/');

	curl_close($ch);	

	return $access_token;
}


function retrieve_user_data($access_token, $subforum){

	$url = 'https://api.stackexchange.com/2.2/me'; // order=desc&sort=reputation are needed I believe..
	$data = array('order' => 'desc', 'sort' => 'reputation', 'site' => "$subforum", 'key' => 'your_key', 'access_token' => "$access_token", 'filter' => 'default');
	$params = http_build_query($data);
	$fullurl = $url."?".$params;

	//open connection
	$ch = curl_init();

	//set the url, number of GET vars, GET data
	curl_setopt($ch,CURLOPT_URL,$fullurl);
	curl_setopt($ch,CURLOPT_HTTPGET,True);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,True);
	curl_setopt($ch,CURLOPT_HTTPHEADER,array(
	    'Content-Type: application/json',
	    'Accept: application/json'
	));

	//execute post
	$result = curl_exec($ch); // $results is een gzipped wrapper object.
	$json = json_decode(gzdecode($result),true);

	/* This returns something like:

	array(4) { ["items"]=> 
		   array(1) { [0]=> 
			      array(16) { ["badge_counts"]=> 
					  array(3) { ["bronze"]=> int(2) 
						     ["silver"]=> int(0) 
						     ["gold"]=> int(0) 
						   } 
				    	  ["account_id"]=> int(5725638) 
				    	  ["is_employee"]=> bool(false) 
				    	  ["last_access_date"]=> int(1464584318) 
				    	  ["reputation_change_year"]=> int(20) 
				    	  ["reputation_change_quarter"]=> int(15) 
				    	  ["reputation_change_month"]=> int(15) 
				          ["reputation_change_week"]=> int(0) 
				    	  ["reputation_change_day"]=> int(0) 
				    	  ["reputation"]=> int(21) 
				    	  ["creation_date"]=> int(1452558859) 
				    	  ["user_type"]=> string(10) "registered" 
				    	  ["user_id"]=> int(5776298) 
				    	  ["link"]=> string(50) "http://stackoverflow.com/users/5776298/monozygotic" 
				    	  ["profile_image"]=> string(91) "https://www.gravatar.com/avatar/9e2f65eb3337890bec8efc8f19e90d2b?s=128&d=identicon&r=PG&f=1" 
				    	  ["display_name"]=> string(11) "Monozygotic" 
				  	} 
			     } 
	            ["has_more"]=> bool(false) 
		    ["quota_max"]=> int(10000) 
		    ["quota_remaining"]=> int(9987)
		  }

	*/

	curl_close($ch);

	// Parse output and return the fields we're interested in in an array.

	$reputation = $json["items"][0]["reputation"];
	$gold_badge = False;
	if ($json["items"][0]["badge_counts"]["gold"] > 0){
	    $gold_badge = True;
	}
	$account_id = $json["items"][0]["account_id"];
	$display_name = $json["items"][0]["display_name"];

	$returnarray = array("reputation" => $reputation, "gold_badge" => $gold_badge, "account_id" => $account_id, "display_name" => $display_name);
	return $returnarray;
}



function check_user($userdata,$subforum){

	$reputation = $userdata["reputation"];
	$gold_badge = $userdata["gold_badge"];
	$account_id = $userdata["account_id"];
	$display_name = $userdata["display_name"];

	if (!$account_id){
            	$title = 'Login';
            	$msg = 'Your StackExchange account is not associated with this subforum.<br />Please go back to the <a href="../index.html">login page</a> and try a different one.';
	    	try_again($title, $msg);
        }else if (($reputation < 3000) && (!$gold_badge)){
		$title = 'Login';
                $msg = 'You need to have a gold badge or more than 3000 reputation points to annotate for this subforum.<br />Please go back to the <a href="../index.html">login page</a> and try a different one.';
                try_again($title, $msg);
	}else{
		maybe_add_user_to_database($account_id,$display_name,$subforum);
	}
}


function maybe_add_user_to_database($account_id,$display_name,$subforum){

	setcookie("se_userid",$account_id,time()+3600,'/');
        setcookie("display_name",$display_name,time()+3600,'/');

	$conn = connect_to_db($subforum);

	$tablename = "table_$account_id";
	if ($conn->query("DESCRIBE $tablename")) {
    		setcookie("ingelogd","ja",time()+3600,'/');
                header('Location: ./present_pair.php');
	} else {
    		header('Location: ./createuser.php');
	}

}


function gzdecode($data){ 
	$g=tempnam('/tmp','ff'); 
	@file_put_contents($g,$data); 
	ob_start(); 
	readgzfile($g); 
	$d=ob_get_clean(); 
	unlink($g); 
	return $d; 
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
		    <div id="logo">
                        <center><img src="../UoM-logo.jpg" alt="Your logo"/></center>
                    </div>
		    <div id="contentsides">
		    <div id="content"><center>'.$msg.'</center>
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
