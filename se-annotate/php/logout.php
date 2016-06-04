<?php

// Change status of ingelogd cookie.
setcookie("ingelogd","nee",time()+3600,'/');
$access_token=$_COOKIE['access_token'];


// Remove cookie. Source: http://www.pontikis.net/blog/create-cookies-php-javascript
//unset($_COOKIE['ingelogd']);
// empty value and set expiration one hour in the past
//$res = setcookie('ingelogd', '', time() - 3600);

invalidate_access_token($access_token);
delete_cookies();

function delete_cookies(){
	// Bron: http://stackoverflow.com/questions/2310558/how-to-delete-all-cookies-of-my-website-in-php
 	if (isset($_SERVER['HTTP_COOKIE'])) {
    	    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    	    foreach($cookies as $cookie) {
        	$parts = explode('=', $cookie);
        	$name = trim($parts[0]);
        	setcookie($name, '', time()-1000);
        	setcookie($name, '', time()-1000, '/');
    	    }
	}
}


function invalidate_access_token($access_token){

	$url = "https://api.stackexchange.com/2.2/access-tokens/$access_token/invalidate";

        //open connection
        $ch = curl_init();

	//set the url, number of GET vars, GET data
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HTTPGET,True);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,True);
        curl_setopt($ch,CURLOPT_HTTPHEADER,array(
            'Content-Type: application/json',
            'Accept: application/json'
        ));

        $result = curl_exec($ch);

        curl_close($ch);

	logged_out();
}


function logged_out() {
    print '<!DOCTYPE html>
             <html lang="en">
                <head>
                    <link href="../se-annotate.css" rel="stylesheet" type="text/css" />
                    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon"/>
                    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset="UTF-8">
                    <title>Logged out</title>
                </head>
                <body>
                    <div id="logo">
                        <center><img src="../UoM-logo.jpg" alt="Your logo"/></center>
                    </div>
                    <div id="contentsides">
                    <div id="content">
		    <div id="back">
    			<a href="../index.html">Back to login page</a>
		    </div>
		    <center>You have been logged out. Thank you for your help!</center>
                    </div>
                    </div>
                </body>
             </html>';
}



?>
