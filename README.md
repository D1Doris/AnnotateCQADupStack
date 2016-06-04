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

2. Set the right user and password for the database in the following files:
    * create_and_fill_databases.cgi
    * se-annotate/php/catch_answer.php
    * se-annotate/php/createuser.php
    * se-annotate/php/login.php
    * se-annotate/php/present_pair.php

    One day I'll make a configuration file for this, and clean up the repeated code.

3. Register your application on stackapps.com. You will then receive a Client ID, a Client Secret and a Key. These, plus your server name, need to be added to:
    * se-annotate/php/login.php
    * se-annotate/php/authenticate.php

4. Download CQADupStack (http://nlp.cis.unimelb.edu.au/resources/cqadupstack/).

5. Prepare the question pairs you would like to be annotated. For this we need one comma-separated csv file per subforum, located in se-annotate/csv/subforum_annotation_candidates.csv. These files should contain question ids in column A and B, where each row is a question pair, and a type in column C, to identify how this pair was chosen. This can be any string you like, with a maximum of 20 characters (unless you change that in create_and_fill_databases.cgi).

6. Change se-annotate/favicon.ico to your own favicon.

7. Change se-annotate/UoM-logo.jpg to your own logo.

8. Add your own email address as contact for problems at the bottom of the following pages:
    * se-annotate/index.html
    * se-annotate/infopage.html
    * se-annotate/php/present_pair.php

    I suggest using http://www.closetnoc.org/mungemaster/mungemaster.pl to obfuscate your email address.

9. Add information on your project to se-annotate/infopage.html

10. Change se-annotate/php/analyticstracking.php to your own analyticstracking.php script, or adjust the USERID (see https://www.google.com/analytics/), or delete it and the following line:<br />
<?php include_once("./analyticstracking.php") ?> from se-annotate/php/present_transitives.php<br />
You can also add it to other pages you would like to track, like the login page.<br />

11. Run create_and_fill_databases.cgi to create and fill the database with the pairs to be annotated.

12. Test in browser: servername/path/to/se-annotate/

13. Feel free to contact me if you run into problems or if you have suggestions for improvements: doris dot hoogeveen it's a gmail address.
