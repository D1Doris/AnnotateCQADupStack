#!/usr/bin/env python

import cgi
import os, glob
import mysql.connector as conn


def connectDB():
    db = conn.connect(host="localhost", user="root", passwd="", port=3306)
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

    # And one for the users
    sql = "use " + subforum
    cursor.execute(sql)
    sql = '''create table users
             (userid varchar(50) not null,
             password varchar(60) not null,
	     new_password varchar(60),
	     email varchar(255) not null,
             reputation int not null,
	     active int(10) DEFAULT NULL,
	     contactpermission int(1) DEFAULT NULL,
	     keepupdated int(1) DEFAULT NULL,
	     hash VARCHAR(32) NOT NULL,
             primary key(userid))'''
    # The new_password field is for people resetting their password.
    cursor.execute(sql)
    db.commit()
    # Source for length of password field: http://stackoverflow.com/questions/7522373/how-to-create-tables-with-password-fields-in-mysql


if __name__ == "__main__":
    try:
	userfiles = glob.glob('se-annotate/csv/*_annotation_candidates.csv')
	for f in userfiles:
	    subforum = os.path.basename(f).split('_')[0]
	    #if subforum == 'gis': # for testing
	    db, cursor = connectDB()
	    createDB(db, cursor, subforum)
	    createTables(db, cursor, subforum)
	    # close the connection to the db
	    cursor.close()
    except:
	cgi.print_exception()	
