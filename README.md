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

1. Install PHP and MySQL on your server.<br />
2. Install swiftmailer (http://swiftmailer.org/)<br />
3. Set the right user and password for the database in the following files:<br />
   	- createdatabases.cgi<br />
	- insertrecords.cgi<br />
	- se-annotate/php/catch_answer.php<br />
	- se-annotate/php/createuser.php<br />
	- se-annotate/php/email_for_forgotten_username.php<br />
	- se-annotate/php/email_for_reset_password.php<br />
	- se-annotate/php/login.php<br />
	- se-annotate/php/present_transitives.php<br />
	- se-annotate/php/reset_password.php<br />
	- se-annotate/php/verify.php<br />
One day I'll make a configuration file for this, and clean up the repeated code.<br />
4. Download CQADupStack (http://nlp.cis.unimelb.edu.au/resources/cqadupstack/) and make sure the subforum zipfiles end up in a directory called 'cqadupstack'.<br />
5. Prepare the question pairs you would like to be annotated. For this we need one comma-separated csv file per subforum, located in se-annotate/csv/subforum_annotation_candidates.csv. These files should contain question ids in column A and B, where each row is a question pair.<br />
6. Change se-annotate/favicon.ico to your own favicon.<br />
7. Add your own email address as contact for problems at the bottom of the following pages:<br />
	- se-annotate/index.html<br />
	- se-annotate/new_user.html<br />
	- se-annotate/php/present_transitives.php<br />
I suggest using http://www.closetnoc.org/mungemaster/mungemaster.pl to obfuscate your email address.<br />
8. Change se-annotate/php/analyticstracking.php to your own analyticstracking.php script, or adjust the USERID (see https://www.google.com/analytics/),
   or delete it and the following line:<br />
   <?php include_once("./analyticstracking.php") ?> from se-annotate/php/present_transitives.php<br />
   You can also add it to other pages you would like to track, like the login page.<br />
9. In se-annotate/php/createuser.php, in the function send_email():<br />
	- Insert your email details (in two places). It has to be a gmail address.<br />
	- Adjust the swiftmailer path.<br />
	- Change the server name in the email body.<br />
	- Actually change the rest of the email body too, to suit your own setup.<br />
Then do the same in se-annotate/php/email_for_forgotten_username.php and se-annotate/php/email_for_reset_password.php<br />
10. Run createdatabases.cgi and then insertrecords.cgi to fill the database with the pairs to be annotated.<br />
11. Test in browser: servername/path/to/se-annotate/<br />
12. Feel free to contact me if you run into problems or if you have suggestions for improvements: doris dot hoogeveen it's a gmail address.
