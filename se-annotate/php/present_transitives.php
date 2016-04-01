<?php 

// Written by Doris Hoogeveen March 2016
// Downloaded from https://github.com/D1Doris/AnnotateCQADupStack

$username=$_COOKIE['se_username']; // Maybe I can leave this out?
$userid=$_COOKIE['se_userid'];
$subforum=$_COOKIE['subforum'];
$ingelogd=$_COOKIE['ingelogd'];

if($ingelogd != 'ja'){
    notloggedin();
}else{
    $conn = connect_to_db($subforum);

    $username=mysqli_real_escape_string($conn, $username);

    $sql = "SELECT pair FROM se_".$userid." WHERE verdict='noverdict' ORDER BY RAND() LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) { // Should be only one result because of the limit set above
            $postids = explode("-", $row["pair"]);
        }	
	$id1 = $postids[0];
	$id2 = $postids[1];
	
	$sql = "SELECT title,body FROM posts Where postid=".$id1;
	$result_id1 = $conn->query($sql);
	while($row_id1 = $result_id1->fetch_assoc()) { // Should be only one result
	    $t1 = $row_id1["title"];
	    $b1 = $row_id1["body"];
	    $b1 = set_img_width($b1);
        }

	$sql = "SELECT title,body FROM posts Where postid=".$id2;
        $result_id2 = $conn->query($sql);
        while($row_id2 = $result_id2->fetch_assoc()) { // Should be only one result
            $t2 = $row_id2["title"];
            $b2 = $row_id2["body"];
	    $b2 = set_img_width($b2);
        }

        # Display chosen pair
        display_pair($t1, $t2, $b1, $b2, $id1, $id2);

    }else{
	print '<!DOCTYPE html>
             <html lang="en">
                <head>
                    <link href="../se-annotate.css" rel="stylesheet" type="text/css" />
                    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon"/>
                    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset="UTF-8">
                    <title>That\'s all Folks!</title>
                </head>
                <body>
		    <div id="contentsides">
                    <div id="content">
                    <center>
                        Congratulations! You have annotated all question pairs for this subforum.<br /><br />
                        Thank you very much for all your help.<br /><br />
                        <a href="../index.html">Click here</a> if you would like to annotate question pairs of another subforum.
                    </center>
                    </div>
		    </div>
                </body>
             </html>';
    }
}

function set_img_width($b){
    $pattern = '/<img [^>]+>/';
    preg_match($pattern, $b, $matches, PREG_OFFSET_CAPTURE);
    foreach ($matches as &$patmatch) { // for all images in the post
	$img = $patmatch[0];
	$widthpat = '/width=\"([0-9]+)\"/';
	preg_match($widthpat, $img, $widthmatches, PREG_OFFSET_CAPTURE);
	if (!empty($widthmatches)) { // max one result because each image only has max 1 width
	    // Het lijkt erop dat dit nooit voorkomt. Misschien verkleint SE de images al.

	    // check if found width is smaller than 657px.
	    //print_r($widthmatches);
	    //print 'Found width:';
	    //print $widthmatches[0][0]."\n";
	    if ((int)$widthmatches[0][0] > 657){ // Klopt dit?
	        $newimg = preg_replace($widthmatches[0][0], 'width="657"', $img);
	        $b = preg_replace($img, $newimg, $b);
	    }
	}else{
	    // check if true width is smaller than 657px;
	    $srcpat = '/src=\"([^ ]+)\"/';
	    preg_match($srcpat, $img, $srcmatches, PREG_OFFSET_CAPTURE);
	    //print_r($srcmatches);
	    // Example output based on <img src="http://i.stack.imgur.com/MJFyb.png" alt="Mockup"> :
	    // Array ( [0] => Array ( [0] => src="http://i.stack.imgur.com/MJFyb.png" [1] => 5 ) [1] => Array ( [0] => http://i.stack.imgur.com/MJFyb.png [1] => 10 ) ) 
	    $size = getimagesize($srcmatches[1][0]);
	    if ($size[0] > 657){
	        $b = preg_replace('/<img /', '<img width="657" ', $b);
	    }
	}
    }
    return $b;
}


function display_pair($t1, $t2, $b1, $b2, $id1, $id2){
    $actionurl = './catch_answer.php?id1='.$id1.'&id2='.$id2;

    print '<!DOCTYPE html>
             <html lang="en">
                <head>
                    <link href="../se-annotate.css" rel="stylesheet" type="text/css" />
                    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon" />
                    <META HTTP-EQUIV="Content-Type" CONTENT="text/html"; charset="latin1">';
    if (($subforum == 'stats') || ($subforum == 'tex')){
	print '
		    <script type="text/x-mathjax-config">
  MathJax.Hub.Config({
    tex2jax: {
      inlineMath: [ ["$","$"] ],
      processEscapes: true
    }
  });
		    </script>
		    <script type="text/javascript" async
  			src="https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-MML-AM_CHTML">
		    </script>';
    }
    print '
                    <title>Improving StackExchange - Judging Duplicates</title>
                </head>
                <body>
		<?php include_once("./analyticstracking.php") ?>
	    <div id="contentsides">
            <div id="content">
            <div id="nav">
                <a href="../index.html">Home</a> | <a href="./logout.php">Log out</a>
            </div>
		<center><h3>Would you consider the next two questions to be duplicates?</h3>
	    <form method="POST" action="'.$actionurl.'">
        
            <table>
                <tr class="light">
                    <td class="post">
          <b>'.$t1.'</b><br />
          --------------<br />
          '.$b1.'
                    </td>
                </tr>
                <tr class="dark">
                    <td>
                            &nbsp;
                    </td>
                </tr>
                <tr class="light">
                    <td class="post">
          <b>'.$t2.'</b><br />
	  --------------<br />
          '.$b2.'
                    </td>
                </tr>
                <tr class="dark">
                    <td>
                            &nbsp;
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><center>
                    <input type="submit" value="YES" name="dup" class="button" />
                    <input type="submit" value="NO" name="nodup" class="button" />
                    <input type="submit" value="Really can\'t tell" name="unclear" class="button" />
                    </center>
                    </td>
                </tr>
            </table>
	    </form><br /><br />

	    <small>If you have any problems with the site, <script language="JavaScript"><!-- 
RlvihaTOxRUREsdaLpxVD="&#100;&#111;&#114;i&#115;&#46;&#104;o&#111;&#103;&#101;v&#101;&#101;&#110;"
wJANRfeXO="&#64;"
XThhXnmjcnZwnxjsGcxubpuWiwxEg="&#103;&#109;&#97;&#105;l&#46;&#99;&#111;&#109;"
wwEFCmVBlxobUjiMITZqTJHYNExE="&#101;&#109;&#97;&#105;l&#32;&#68;&#111;&#114;i&#115;&#32;&#72;&#111;o&#103;&#101;&#118;&#101;e&#110;"
fmPjDTufnRIK="&#101;&#109;&#97;&#105;l&#32;&#68;&#111;&#114;i&#115;&#32;&#72;&#111;o&#103;&#101;&#118;&#101;e&#110;"
document.write("<a href=\'mailto:"+RlvihaTOxRUREsdaLpxVD+""+wJANRfeXO+""+XThhXnmjcnZwnxjsGcxubpuWiwxEg+"\' title=\'"+fmPjDTufnRIK+"\'>"+wwEFCmVBlxobUjiMITZqTJHYNExE+"<\/a>")
// --></script>.</small>

	    </center></div></div><br />
            </body>
               </html>';

}


function connect_to_db($subforum){

    $servername = "localhost";
    $dbuser = "root";
    $dbpassword = "";
    $dbname = $subforum;

    // Create connection
    $conn = mysqli_connect($servername, $dbuser, $dbpassword, $dbname);

    // Check connection
    if (!$conn) {
        //die("Connection failed: " . mysqli_connect_error());
        $title = 'Selecting question pair';
        $msg = 'Something went wrong when connecting to the database to select a question pair for annotation.';
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
		    <div id="contentsides">
                    <div id="content">
                    <center>
			You need to be logged in to view this page. <a href="../index.html">Please log in here.</a>
                    </center>
                    </div>
		    </div>
                </body>
             </html>';
}

?>
