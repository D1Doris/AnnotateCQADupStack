#!/usr/bin/env python

import cgi
import os, glob
import mysql.connector as conn
import query_cqadupstack_barebones as qse


def connectDB(subforum):
    db = conn.connect(host="localhost", user="root", passwd="", port=3306, db=subforum)
    cursor = db.cursor()
    return db,cursor

def populateDB(db, cursor, subforum):
    forumfile = 'cqadupstack/' + subforum + '.zip'
    o = qse.load_subforum(forumfile)
    postids = o.get_all_postids()
    totids = len(postids)
    count = 1
    for postid in postids:
	if count % 10000 == 0:
	    print 'Added ' + str(count) + ' out of ' + str(totids) + ' posts to ' + subforum + ' database.'

	title = o.url_cleaning(o.get_posttitle(postid))
	body = o.url_cleaning(o.get_postbody(postid))
	count += 1

	sql = "INSERT INTO posts(postid,title,body) VALUES (%s, %s, %s)"
        cursor.execute(sql, (postid, title, body))
    print "Done."
    db.commit()



if __name__ == "__main__":
    try:
	userfiles = glob.glob('se-annotate/csv/*_annotation_candidates.csv')
        for f in userfiles:
            subforum = os.path.basename(f).split('_')[0]
	    #if subforum == 'android': # for testing
	    db, cursor = connectDB(subforum)
	    populateDB(db, cursor, subforum)
	    cursor.close()
    except:
	cgi.print_exception()	
