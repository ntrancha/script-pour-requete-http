#!/usr/bin/python2.7

import urllib
import urllib2
import time
import os, sys
import re

#User_agent par defaut
User_agent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'



#Pour chronometrer le temps de reponse du serveur
class Timer:

    def __enter__(self):
        self.start = time.clock()
        return self

    def __exit__(self, *args):
        self.end = time.clock()
        self.interval = self.end - self.start




class HTTP:

    # Peut etre initialise avec les paramatres ou sans
    def __init__(self, url='', user_agent=User_agent, cookie='', referer='', post=''):

	self.url        = url			# N'oubliez pas le http://
	self.user_agent = user_agent
	self.cookies    = cookie		# cookie=valeur;id=42
	self.referer    = referer
	self.post       = post			# user=toto;pass=123
	self.content	= ''			# Contenu de la page HTML
	self.header     = ''			# Header reponse serveur
	self.time       = -1			# Temps de la requete
	self.error      = -1			#   1 = OK   0 = KO
	self.length     = -1			# Taille de la requete du serveur
	self.aff_time   =  0
	self.aff_length =  0
	self.aff_header =  0
	self.aff_page   =  1

    def url_check(self):
	# Rajout de http:// si absent de l'url ou du referer
	if self.url	!= '' and self.url[:7]     != 'http://':
	    self.url	 = 'http://'  +self.url
	if self.referer != '' and self.referer[:7] != 'http://':
	    self.referer = 'http://' + self.referer


    def referer_default(self):
	# Si aucun referer n'est parametre, la racine du site et prise par defaut
        host = self.url.split('/')
        self.referer = "http://" + host[2]

    def request(self, kwargs):
	if len(kwargs) < 1:
	    return {}
	if kwargs.get('url'):
	    self.url = kwargs.get('url', None)
	    self.url_check()
	    if kwargs.get('user_agent'):
		self.user_agent = kwargs.get('user_agent', None)
	    else:
		self.user_agent = User_agent
	    if kwargs.get('referer'):
		self.referer = kwargs.get('referer', None)
	    else:
		self.referer_default()
	    if kwargs.get('cookies'):
		self.cookies = kwargs.get('cookies', None)
	    else:
		self.cookies = ''
	    if kwargs.get('post'):
		self.post = kwargs.get('post', None)
	    else:
		self.post = ''
	    if kwargs.get('time'):
		self.aff_time = kwargs.get('time', None)
	    else:
		self.aff_time = 0
	    if kwargs.get('length'):
		self.aff_length = kwargs.get('length', None)
	    else:
		self.aff_length = 0
	    if kwargs.get('header'):
		self.aff_header = kwargs.get('header', None)
	    else:
		self.aff_header = 0
	    if kwargs.get('content') and kwargs.get('content') == '0':
		self.aff_page = 0
	    else:
		self.aff_page = 1
	    if self.get():
       	    	self.display()
		return ({'time':self.time,'length':self.length,'header':self.header,'content':self.content})
	    return {}


    # Envoie une requete avec les parametres precedement passe
    def get(self):

	# On reinitialise les variables
	self.content  = ''
	self.header   = ''
	self.time     = -1
	self.error    = -1
	self.length   = -1

	# Sort de la fonction si l'url est vide
	if self.url == '':
	    return 0
	# Verification de l'url
	self.url_check()

	# Lancement du timer
        try:
            with Timer() as t:
		self.__download()
	# Fin du timer
        finally:
	    # Si aucune erreurs
	    if self.error == 0:
		# Recuperation: timer, header et contenu de la page HTML
	        self.time     = t.interval
	        self.content  = self.__requete.read()
	        self.header   = self.__requete.info()
		# Recuperation de la taille de la requete du serveur
		decoupe = str(self.header)
		lignes = decoupe.split("\n")
		# On parcour le header pour trouver la ligne Content-Length
		for var in lignes:
		    if re.search('Content-Length*', var):
			length_split = var.split(" ")
			self.length = length_split[1]
		return 1

	return 0


    # Affichage
    def display(self):

        # Si aucune erreurs
	if self.error == 0:
	    if self.aff_page   == 1:
	        print self.content	# Affiche la page sauf si -n
	    if self.aff_header == 1:
	        print self.header	# Affiche le header si -i
	    if self.aff_time   == 1:
	        print self.time		# Affiche le temps si -t
	    if self.aff_length == 1:
	        print self.length	# Affiche la taille de la requete du serveur si -l


    def __download(self):

	# Si aucun referer n'est parametre, la racine du site et prise par defaut
        if self.referer == '':
            host = self.url.split('/')
            referer = "http://" + host[2]
	else:
	    referer = self.referer

	# Preparation du header de la requete
        if self.cookies == '':
	    header={'User-agent': self.user_agent, 'Referer' : referer}
        else:
            header={'User-agent': self.user_agent, 'Cookie': self.cookies, 'Referer' : referer}

	# encodage des variables POST
    	data = urllib.urlencode(self.__param(self.post))
	# Preparation de la requete
    	req  = urllib2.Request(self.url, data, header)

    	try:
	    # Envoi de la requete
            self.__requete = urllib2.urlopen(req)
	    self.error    = 0
    	except urllib2.HTTPError:
	    # En cas d'erreur
	    self.error 	  = 1
	    self.__requete = None


    # Formate les variables post pour les requetes
    def __param(self, parametre):

	params = {}
	if parametre == '':
            return params

	var = parametre.split(';')		# Les variables sont separes les une des autres par des ;
	count = 0
	while count < len(var):			# On parcours toutes les variables
	    split = var[count].split('=')	# La variable et suivit d'un = puis de sa valeur
	    param = {split[0] : split[1]}	# On place la varible et sa valeur dans un array
	    params.update(param)		# On l'ajout a la liste
	    count += 1

	return params


    # Pour le main
    def argv(self, argv):

	self.url = argv[1]
	if argv[1] == '-h':
	    self.__help()

    	if len(argv) > 1:
            num = 1
            # Parcours les arguments passe au programme
            while len(argv) > num + 1:
                num += 1
	        if argv[num]   == '-h':
	            self.__help()
	        if argv[num]   == '-t' or argv[num] == '-T' or argv[num]    == '--time':
		    self.aff_time   =     1
	        if argv[num]   == '-i' or argv[num] == '-I' or argv[num]    == '--info':
		    self.aff_header =     1
	        if argv[num]   == '-l' or argv[num] == '-L' or argv[num]    == '--length':
		    self.aff_length =     1
	        if argv[num]   == '-n' or argv[num] == '-N'  or argv[num]   == '--no-page':
		    self.aff_page   =     0
		if argv[num-1] == '-c' or argv[num] == '-C'  or argv[num-1] == '--cookies':
		    self.cookies    =     argv[num]
		if argv[num-1] == '-r' or argv[num] == '-R'  or argv[num-1] == '--referer':
		    self.referer    =     argv[num]
		if argv[num-1] == '-u' or argv[num] == '-U'  or argv[num-1] == '--user_agent':
		    self.user_agent =     argv[num]
		if argv[num-1] == '-p' or argv[num] == '-P'  or argv[num-1] == '--post':
		    self.post       =     argv[num]


    # Affiche l'aide
    def __help(self):
	print 'python',sys.argv[0],'[URL] -u "User_agent" -t -h'
	sys.exit(0)



if __name__ == '__main__':

    # Initialisation
    requete = HTTP()

    # Si les parametres sont passes en argument
    if len(sys.argv) > 1:
        requete.argv(sys.argv)
        if requete.get() == 1:
       	    requete.display()
    else:
	# Exemple
	requete.request({'url'     : 'perdu.com',		# URL
		         'header'  :  0,			# AFFICHE LE HEADER		DE LA REQUETE SERVEUR
		     	 'time'    :  0,			# AFFICHE DU TEMPS DE REPONSE	DE LA REQUETE SERVEUR
		     	 'length'  :  0,			# AFFICHE LA TAILLE DU CONTENU	DE LA REQUETE SERVEUR
		     	 'content' : '0',			# AFFICHE LE CONTENU		DE LA REQUETE SERVEUR
		     	 'referer' : 'http://toto.fr',		# CHANGE LE REFERER		DANS LA REQUETE CLIENT
		     	 'agent'   : 'iphone',			# CHANGE L USER AGENT		DANS LA REQUETE CLIENT
		     	 'cookies' : 'var=42;var2=toto',	# AJOUTE DES COOKIES		DANS LA REQUETE CLIENT
		     	 'post'    : 'var=42;var2=toto'})	# AJOUTE DES AVARIABLES POST	DANS LA REQUETE CLIENT
        if requete.error == 0:
	    print requete.content
	    print requete.header
	    print requete.time
	    print requete.length
        sys.exit(0)
    sys.exit(0)
