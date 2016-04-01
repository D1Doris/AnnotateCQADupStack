The scripts in this directory can be used to set up an annotation system for the CQADupStack data, downloadable from http://nlp.cis.unimelb.edu.au/resources/cqadupstack/.

More information on this data can be found in the paper and the README of the script linked on the page mentioned above.

Please cite the following paper when making use of CQADupStack:

@inproceedings{hoogeveen2015, <br />
 author = {Hoogeveen, Doris and Verspoor, Karin M. and Baldwin, Timothy}, <br />
 title = {CQADupStack: A Benchmark Data Set for Community Question-Answering Research}, <br />
 booktitle = {Proceedings of the 20th Australasian Document Computing Symposium (ADCS)}, <br />
 series = {ADCS '15}, <br />
 year = {2015}, <br />
 isbn = {978-1-4503-4040-3}, <br />
 location = {Parramatta, NSW, Australia}, <br />
 pages = {3:1--3:8}, <br />
 articleno = {3}, <br />
 numpages = {8}, <br />
 url = {http://doi.acm.org/10.1145/2838931.2838934}, <br />
 doi = {10.1145/2838931.2838934}, <br />
 acmid = {2838934}, <br />
 publisher = {ACM}, <br />
 address = {New York, NY, USA}, <br />
} 

For licensing information please see the LICENCE file.

=== HOW TO SET UP THE ANNOTATION SYSTEM ===

1. Install PHP and MySQL on your server.

2. Install swiftmailer (http://swiftmailer.org/)

3. Set the right user and password for the database in the following files:
    * createdatabases.cgi
    * insertrecords.cgi
    * se-annotate/php/catch_answer.php
    * se-annotate/php/createuser.php
    * se-annotate/php/email_for_forgotten_username.php
    * se-annotate/php/email_for_reset_password.php
    * se-annotate/php/login.php
    * se-annotate/php/present_transitives.php
    * se-annotate/php/reset_password.php
    * se-annotate/php/verify.php

    One day I'll make a configuration file for this, and clean up the repeated code.

4. Download CQADupStack (http://nlp.cis.unimelb.edu.au/resources/cqadupstack/) and make sure the subforum zipfiles end up in a directory called 'cqadupstack'.

5. Prepare the question pairs you would like to be annotated. For this we need one comma-separated csv file per subforum, located in se-annotate/csv/subforum_annotation_candidates.csv. These files should contain question ids in column A and B, where each row is a question pair.

6. Change se-annotate/favicon.ico to your own favicon.

7. Add your own email address as contact for problems at the bottom of the following pages:
    * se-annotate/index.html
    * se-annotate/new_user.html
    * se-annotate/php/present_transitives.php

    I suggest using http://www.closetnoc.org/mungemaster/mungemaster.pl to obfuscate your email address.

8. Change se-annotate/php/analyticstracking.php to your own analyticstracking.php script, or adjust the USERID (see https://www.google.com/analytics/), or delete it and the following line:<br />
<?php include_once("./analyticstracking.php") ?> from se-annotate/php/present_transitives.php<br />
You can also add it to other pages you would like to track, like the login page.<br />

9. In se-annotate/php/createuser.php, in the function send_email():
    * Insert your email details (in two places). It has to be a gmail address.
    * Adjust the swiftmailer path.
    * Change the server name in the email body.
    * Actually change the rest of the email body too, to suit your own setup.

    Then do the same in se-annotate/php/email_for_forgotten_username.php and se-annotate/php/email_for_reset_password.php

10. Run createdatabases.cgi and then insertrecords.cgi to fill the database with the pairs to be annotated.

11. Test in browser: servername/path/to/se-annotate/

12. Feel free to contact me if you run into problems or if you have suggestions for improvements: doris dot hoogeveen it's a gmail address.
