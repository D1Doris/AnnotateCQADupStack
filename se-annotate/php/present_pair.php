<?php 

$userid=$_COOKIE['se_userid'];
$subforum=$_COOKIE['subforum'];
$ingelogd=$_COOKIE['ingelogd'];

if($ingelogd != 'ja'){
    notloggedin();
}else{
    $conn = connect_to_db($subforum);

    # Find total nr of questions for this subforum
    $sql = "SELECT * FROM table_".$userid;
    $result = $conn->query($sql);
    $totalnrofpairs = $result->num_rows;

    $sql = "SELECT pair FROM table_".$userid." WHERE verdict='noverdict' ORDER BY RAND() LIMIT 1";
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

	# Find nr of question pairs that have been annotated.
	$sql = "SELECT pair FROM table_".$userid." WHERE verdict='noverdict'";
    	$fullresult = $conn->query($sql);
	$not_annotated = $fullresult->num_rows;
	$annotated = $totalnrofpairs - $not_annotated + 1;

        # Display chosen pair
        display_pair($t1, $t2, $b1, $b2, $id1, $id2, $totalnrofpairs, $annotated, $userid);

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
		    <div id="logo">
                        <center><img src="../UoM-logo.jpg" alt="Your logo"/></center>
                    </div>
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
	if ($userid == '2'){ // This is the demo user
	    // Reset all demo verdicts to 'noverdict';
	    $sql = "Update table_demo SET verdict='noverdict'";

    	    if ($conn->query($sql) === TRUE) {
        	// Reset was successful. Do nothing.
    	    }else{
        	echo "Something went wrong when resetting the demo verdicts!";
    	    }	
	}
    }
}

function set_img_width($b){
    $pattern = '/<img [^>]+>/';
    preg_match_all($pattern, $b, $outerarray, PREG_OFFSET_CAPTURE);
    foreach ($outerarray as &$matches){ // For some reason there's a useless array in the middle...
	foreach ($matches as &$patmatch) { // for all images in the post
	    $img = $patmatch[0];
	    $widthpat = '/width=\"([0-9]+)\"/';
	    preg_match($widthpat, $img, $widthmatches, PREG_OFFSET_CAPTURE);
	    if (!empty($widthmatches)) { // max one result because each image only has max 1 width
	        // Het lijkt erop dat dit nooit voorkomt. Misschien verkleint SE de images al.
	        // check if found width is smaller than 657px.
	        if ((int)$widthmatches[0][0] > 657){ // Klopt dit?
	            $newimg = preg_replace($widthmatches[0][0], 'width="657"', $img);
	            $b = preg_replace($img, $newimg, $b);
	        }
	    }else{
	        // check if true width is smaller than 657px;
	        $newimg = preg_replace('/<img /', '<img style="max-width:657px;width: expression(this.width > 657 ? 657: true);" ', $img);
	        $newimg = '<br /><center>'.$newimg.'</center><br />';
	        $b = preg_replace('/'.preg_quote($img, '/').'/', $newimg, $b);
	    }
	}
    }
    return $b;
}


function display_pair($t1, $t2, $b1, $b2, $id1, $id2, $totalnrofpairs, $annotated, $userid){
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
            </div>';

    if ($userid == "demo"){
	print '<center><h1>THIS IS A DEMO</h1></center>';
    }

    print '
		<center><h3>Would you consider the next two questions to be duplicates? ('.$annotated.'/'.$totalnrofpairs.')</h3>
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
		    <input type="submit" value="Related, but not duplicates" name="related" class="button" />
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
    $dbuser = "someuser";
    $dbpassword = "somepassword";
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
		    <div id="logo">
                        <center><img src="../UoM-logo.jpg" alt="Your logo"/></center>
                    </div>
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
