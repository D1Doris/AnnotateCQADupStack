<?php


$subforum=$_POST['subforum'];
setcookie("subforum",$subforum,time()+3600,'/');

// Find out which button was clicked

if (isset($_POST['demo'])) {

    setcookie("se_userid","demo",time()+3600,'/');
    setcookie("display_name","Demo User",time()+3600,'/');
    setcookie("ingelogd","ja",time()+3600,'/');
    header('Location: ./present_pair.php');

}else{

    // This needs to happen first: https://api.stackexchange.com/docs/authentication 
    header('Location: https://stackexchange.com/oauth?client_id=your_client_id&scope=private_info&redirect_uri=http%3A%2F%2Fyour_server%2Fse-annotate%2Fphp%2Flogin.php');

}

?>
