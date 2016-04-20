# script-pour-requete-http

Usage: python requete.py [URL] (OPTIONS)

Options:
 -t    affiche le temps de réponse
 -h    affiche le header de la réponse
 -n    n'affiche pas le contenu de la page
 -c    Ajoute des cookies: - c "Cookie=valeur;Autre_cookie=42"
 -p    Ajoute de variable POST :  -p "User=toto;Pass=god"
 -r    Défini le réferer : -r "http://www.ici.fr"
 -u    Défini l'user agent : -u "Iphone"

Exemple:
  python requete.py http://google.fr
  python requete.py http://site.fr/ -c "id=1234987" -p "User=toto;Pass=god" -h
  python requete.py "http://site.fr/index.php?page=osef&lang=fr" -n -t

L'user agent par défaut est : 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'
Le réferer si il n'est pas précisé est par défaut l'adresse racine du site.

