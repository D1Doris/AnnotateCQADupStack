#!/usr/bin/env python
 
import os, re, sys
import nltk, json, codecs
import pydoc, math
import zipfile, random, datetime
from operator import truediv
from scipy.misc import comb
from random import randrange

# Written by Doris Hoogeveen Nov 2015. For a usage please call the script without arguments.

def load_subforum(subforumzipped):
    ''' Takes a subforum.zip file as input and returns a StackExchange Subforum class object.'''
    return Subforum(subforumzipped)

class Subforum():
    def __init__(self, zipped_catfile):
	''' This class takes a StackExchange subforum.zip file as input and makes it queryable via the methods below. '''
	# Check to see if supplied file exists and is a valid zip file.
	if not os.path.exists(zipped_catfile):
	    sys.exit('The supplied zipfile does not exist. Please supply a valid StackExchange subforum.zip file.')
 	if not zipfile.is_zipfile(zipped_catfile):
	    sys.exit('Please supply a valid StackExchange subforum.zip file.')

	self.cat = os.path.basename(zipped_catfile).split('.')[0]
	self._unzip_and_load(zipped_catfile)	

    def _unzip_and_load(self, zipped_catfile):
	ziplocation = os.path.dirname(zipped_catfile)
	cat = os.path.basename(zipped_catfile).split('.')[0]
	questionfile = ziplocation + '/' + cat + '/' + cat + '_questions.json'
	answerfile = ziplocation + '/' + cat + '/' + cat + '_answers.json'
	commentfile = ziplocation + '/' + cat + '/' + cat + '_comments.json'
	userfile = ziplocation + '/' + cat + '/' + cat + '_users.json'
	if os.path.exists(questionfile) and os.path.exists(answerfile) and os.path.exists(commentfile) and os.path.exists(userfile):
	    pass # All good, we don't need to unzip anything
	else:
	    zip_ref = zipfile.ZipFile(zipped_catfile, 'r')
	    zip_ref.extractall(ziplocation)

	qf = codecs.open(questionfile, 'r', encoding='utf-8')
        self.postdict = json.load(qf)

	af = codecs.open(answerfile, 'r', encoding='utf-8')
        self.answerdict = json.load(af)

        cf = codecs.open(commentfile, 'r', encoding='utf-8')
        self.commentdict = json.load(cf)

	uf = codecs.open(userfile, 'r', encoding='utf-8')
        self.userdict = json.load(uf)



    def get_all_postids(self):
        ''' Takes no input and returns a list of ALL post ids. '''
        return self.postdict.keys()


    def get_posttitle(self, postid):
	''' Takes a post id as input and returns the title of the post. '''
	return self.postdict[postid]["title"]

    def get_postbody(self, postid):
	''' Takes a post id as input and returns the body of the post. '''
	return self.postdict[postid]["body"]


    def url_cleaning(self, s):
	''' Only remove possible duplicates and stackexchange urls. '''
	posduppat = re.compile('<blockquote>(.|\n)+?Possible Duplicate(.|\n)+?</blockquote>', re.MULTILINE)
	s = re.sub(posduppat, '', s)

        #s = re.sub('(<a href=\"[^\"]+\">)?https?://([a-z]+\.)?stackexchange\.com[^ ]+', "stackexchange-url", s)
        #s = re.sub('(<a href=\"[^\"]+\">)?https?://stackoverflow\.com[^ ]+', "stackexchange-url", s)
	#if not re.search('(<a href=\"[^\"]+\">)?https?://stackoverflow\.com[^ ]+', s):
	#    s = re.sub('<a href=\"[^ ]+stackexchange\.com[^ ]+\"[^>]+>([^<]+)</a>', '\1', s)
	#else:
	#    s = re.sub('<a href=\"[^ ]+stackexchange\.com[^ ]+\"[^>]+>([^<]+)</a>', "stackexchange-url", s)

	#s = re.sub('<a href=\"stackexchange-url( rel=\"nofollow\")?>([^<]+)</a>', '\2', s)
	#s = re.sub('<a href=\"stackexchange-url ', 'stackexchange-url ', s)

	# Let's simplify the patterns above:
	pat = re.compile('(<a[^>]+stackexchange[^>]+>([^<]+)</a>)')
	allmatches = re.findall(pat, s)
	for match in allmatches:
	    linktext = match[1]
	    tosub = re.escape(match[0])
	    if linktext == "":
		s = re.sub(tosub, 'stackexchange-url', s)
	    else:
		try: # let's keep it simple. If linktext contains weird stuff causing things to break, then we just get rid of it.
		    s = re.sub(tosub, 'stackexchange-url ("' + linktext + '")', s)
		except:
		    s = re.sub(tosub, 'stackexchange-url', s)

	pat = re.compile('(<a[^>]+stackoverflow[^>]+>([^<]+)</a>)')
        allmatches = re.findall(pat, s)
        for match in allmatches:
            linktext = match[1]
	    tosub = re.escape(match[0])
            if linktext == "":
                s = re.sub(tosub, 'stackexchange-url', s)
            else:
		try: # let's keep it simple. If linktext contains weird stuff causing things to break, then we just get rid of it.
		    s = re.sub(tosub, 'stackexchange-url ("' + linktext + '")', s)
		except:
		    s = re.sub(tosub, 'stackexchange-url', s)

	# make headers a bit smaller
	s = re.sub('<h3>', '<b>', s)
	s = re.sub('</h3>', '</b>', s)
	s = re.sub('<h2>', '<b>', s)
	s = re.sub('</h2>', '</b>', s)
	s = re.sub('<h1>', '<b>', s)
	s = re.sub('</h1>', '</b>', s)

	return s


####################################################################################
	
def usage():
    usage_text = '''
    This script is a barebones version of query_cqadupstack.py which can be downloaded from https://github.com/D1Doris/CQADupStack/.

    It is called from insertrecords.cgi (see https://github.com/D1Doris/AnnotateCQADupStack), to fill a database with
    posts from CQADupStack, the StackExchange data which can be downloaded from http://nlp.cis.unimelb.edu.au/resources/cqadupstack/,
    so they can be annotated.

    The script contains a main function called load_subforum(). It has one argument: a StackExchange (CQADupStack) subforum.zip file.
    load_subforum() uses this file to create a 'Subforum' object and returns this.

    Subforum objects can be queried using the following methods:

'''

    strhelp = pydoc.render_doc(Subforum, "Help on %s")
    i = strhelp.find('below')
    strhelp = strhelp[i+9:]
    usage_text += strhelp
    usage_text += '\n\n    -----------------------------'
    usage_text += '\n\n    Here are some examples of how to use the script:'
    usage_text += '''\n\n    >>> import query_cqadupstack_barebones as qcqa
    >>> o = qcqa.load_subforum('/home/hoogeveen/datasets/CQADupStack/webmasters.zip')
    >>> o.get_posttitle('18957')
    u'What do you consider a "mobile" device?'
    >>> o.get_postbody('18957')'''
    usage_text += u'<p>I\'m implementing a mobile-friendly version of our corporate web site and will be using <a href="http://wurfl.sourceforge.net/" rel="nofollow" title="WURFL">WURFL</a> to detect mobile browsers and redirect them to our mobile site.  Having recently purchased an Android tablet, I\'ve found that many sites consider it to be a mobile device even though it has a large 10" screen and it\'s perfectly capable of handling sites designed using standard desktop resolutions.</p>\n\n<p>My plan is to use WURFL, examine the device capabilities and treat anything with a resolution width of less than 700px as a mobile device, but I\'d like some input as to that sweet spot for determining mobile vs desktop.</p>\n'
    usage_text += '\n\n    -----------------------------'
    usage_text += '\n\n    Please see the README file that came with this script for more information on the data.\n'
    print usage_text 
    sys.exit(' ')

if __name__ == "__main__":
    usage()
