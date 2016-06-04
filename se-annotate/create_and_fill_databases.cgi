#!/usr/bin/env python

import cgi
import os, glob, re, sys
import mysql.connector as conn
import query_cqadupstack_barebones as qse


def connectDB():
    db = conn.connect(host="localhost", user="someuser", passwd="somepassword", port=3306)
    cursor = db.cursor()
    return db,cursor

def createDB(db, cursor, subforum):
    sql = "create database " + subforum
    cursor.execute(sql)
    db.commit()

def createTables(db, cursor, subforum):
    # Create one table for the posts
    sql = "use " + subforum
    cursor.execute(sql)
    sql = '''create table posts
	     (postid int not null,
	     title varchar(150) not null,
	     body varchar(30000) not null,
	     primary key(postid))'''
    cursor.execute(sql)
    db.commit()

    # Create one table for the demo user
    sql = "use " + subforum
    cursor.execute(sql)
    sql = '''CREATE TABLE table_demo
	     (pairid INT NOT NULL AUTO_INCREMENT,
	     pair VARCHAR(15) NOT NULL,
	     pairtype VARCHAR(20) NOT NULL,
             verdict VARCHAR(20),
             primary key(pairid)
             )'''
    cursor.execute(sql)
    db.commit()

    # types should be one of 'transitive', 'fn' or 'transitive and fn'. At least at this stage.


def populateDB(db, cursor, subforum, zipdir, csvdir):
    # First populate the posts table
    forumfile = zipdir + '/' + subforum + '.zip'
    o = qse.load_subforum(forumfile)
    postids = o.get_all_postids()
    totids = len(postids)
    count = 1
    for postid in postids:
        if count % 10000 == 0:
            print 'Added ' + str(count) + ' out of ' + str(totids) + ' posts to ' + subforum + ' database.'

        title = o.url_cleaning(o.get_posttitle(postid))
        body = o.url_cleaning(o.get_postbody(postid))

	if not re.search('[A-Za-z0-9]', title) and not re.search('[A-Za-z0-9]', body):
	    print "WARNING: Empty title and body for post", postid
	elif not re.search('[A-Za-z0-9]', title):
	    print "WARNING: Empty title for post", postid
	elif not re.search('[A-Za-z0-9]', body):
	    print "WARNING: Empty body for post", postid
        count += 1

        sql = "INSERT INTO posts(postid,title,body) VALUES (%s, %s, %s)"
        cursor.execute(sql, (postid, title, body))
    db.commit()
    print "Populated the posts table."

    # Then fill the demo user's table
    csvfile = csvdir + '/' + subforum + '_annotation_candidates.csv'
    csv_open = open(csvfile, 'r')
    csv = csv_open.readlines()
    csv_open.close()
    
    for row in csv:
	row = row.strip()
	cells = row.split('\t')
	pairid = cells[0] + '-' + cells[1]
	pairtype = cells[2]
	sql = "INSERT INTO table_demo(pair,pairtype,verdict) VALUES (%s, %s, 'noverdict')"
	cursor.execute(sql, (pairid, pairtype))
    db.commit()
    print "Filled table_demo table."    
	


def usage():
    usage_text = '''
    This script can be used to create and fill a mysql database that's used for the annotation of CQADupStack data.

    USAGE: ''' + os.path.basename(__file__) + ''' <zipdir>

    <zipdir> is the directory with the CQADupStack .zip files.
    This can be downloaded from http://nlp.cis.unimelb.edu.au/resources/cqadupstack/.

    '''
    print usage_text
    sys.exit(' ')



if __name__ == "__main__":
    if len(sys.argv[1:]) != 1:
        usage()
    else:
        try:
            zipdir = sys.argv[1]
	    csvdir = 'csv/'
	    annotationfiles = glob.glob(csvdir + '/*_annotation_candidates.csv')
	    for f in annotationfiles:
	        subforum = os.path.basename(f).split('_')[0]
	        db, cursor = connectDB()
	        createDB(db, cursor, subforum)
	        createTables(db, cursor, subforum)
	        populateDB(db, cursor, subforum, zipdir, csvdir)
		print "Closing the connection..."
	        cursor.close()
        except:
	    cgi.print_exception()




