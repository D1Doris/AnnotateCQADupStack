<?php

// Written by Doris Hoogeveen March 2016
// Downloaded from https://github.com/D1Doris/AnnotateCQADupStack

// Change status of ingelogd cookie.
setcookie("ingelogd","nee",time()+3600,'/');

header('Location: ../index.html');
exit();

?>
