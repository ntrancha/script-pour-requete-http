<?php
# **************************************************************************** #
#                                                                              #
#                                                         :::      ::::::::    #
#    requete.php                                        :+:      :+:    :+:    #
#                                                     +:+ +:+         +:+      #
#    By: ntrancha <ntrancha@student.42.fr>          +#+  +:+       +#+         #
#                                                 +#+#+#+#+#+   +#+            #
#    Created: 2019/05/10 10:45:53 by ntrancha          #+#    #+#              #
#    Updated: 2019/06/10 14:10:01 by ntrancha         ###   ########.fr        #
#                                                                              #
# **************************************************************************** #

$eof  = "\r\n";
$code = "";
$file = "";

$test = 0;
while ($test <= $argc){
	if (isset($argv[$test]) AND ($argv[$test] == "-H" OR $argv[$test] == "-h" OR $argv[$test] == "--help"))
	{
		echo "Usage CLI : php requete.php [URL] [OPTIONS] [VALEURS]...\n"
		."Options :\n"
		." -F ou --file       : requete depuis un fichier\n"
		." -P ou --post       : variable(s) post\n"
		." -C ou --cookie     : variable(s) cookie\n"
		." -R ou --referer    : referer\n"
		." -U ou --user-agent : user-agent\n"
		." -L ou --list       : liste des user-agent classiques\n"
		."Exemple : php requete.php  \"http://site.com/index.php?id=42\" -P \"data:1;var:2\" -C \"phpessid:md5;session:1234\"\n\n\n"
		."Usage WEB : http://.../requete.php?[URL][OPTIONS][VARIABLE_GET]...\n"
		."Options :\n"
		." url$code                : URL\n"
		." referer$code            : referer\n"
		." user-agent$code         : user-agent\n"
		."Exemple : http://.../requete.php?url$code=http://google.fr&referer$code=http://google.fr&user-agent$code=firefox&get-data=42&id=12\n\n\n"
		."Usage Brute-force CLI : php requete.php [URL] --brute-force-get [DICO] [VARIABLE_GET]  [OPTIONS] [VALEURS]...\n"
		."Options :\n"
		." --brute-force-get  : brute force une variable GET\n"
		." --brute-force-post : brute force une variable POST\n"
		."Exemple : php requete.php  \"http://site.com/index.php?id=42\" --brute-force-get \"dico.txt\" \"id\" -C \"page:42;user:admin\"\n";
		exit;
	}
	$test++;
}

$test = 0;
while ($test <= $argc){
	if (isset($argv[$test]) AND ($argv[$test] == "-L" OR $argv[$test] == "-l" OR $argv[$test] == "--list"))
	{
		echo "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; FSL 7.0.6.01001)
Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; FSL 7.0.7.01001)
Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; FSL 7.0.5.01003)
Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0
Mozilla/5.0 (X11; U; Linux x86_64; de; rv:1.9.2.8) Gecko/20100723 Ubuntu/10.04 (lucid) Firefox/3.6.8
Mozilla/5.0 (Windows NT 5.1; rv:13.0) Gecko/20100101 Firefox/13.0.1
Mozilla/5.0 (Windows NT 6.1; WOW64; rv:11.0) Gecko/20100101 Firefox/11.0
Mozilla/5.0 (X11; U; Linux x86_64; de; rv:1.9.2.8) Gecko/20100723 Ubuntu/10.04 (lucid) Firefox/3.6.8
Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.0.3705)
Mozilla/5.0 (Windows NT 5.1; rv:13.0) Gecko/20100101 Firefox/13.0.1
Mozilla/5.0 (Windows NT 6.1; WOW64; rv:13.0) Gecko/20100101 Firefox/13.0.1
Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)
Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729)
Opera/9.80 (Windows NT 5.1; U; en) Presto/2.10.289 Version/12.01
Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)
Mozilla/5.0 (Windows NT 5.1; rv:5.0.1) Gecko/20100101 Firefox/5.0.1
Mozilla/5.0 (Windows NT 6.1; rv:5.0) Gecko/20100101 Firefox/5.02
Mozilla/5.0 (Windows NT 6.0) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.112 Safari/535.1
Mozilla/4.0 (compatible; MSIE 6.0; MSIE 5.5; Windows NT 5.0) Opera 7.02 Bork-edition [en]\n";
		exit;
	}
	$test++;
}

// Requete depuis un fichier
$test = 0;
while ($test <= $argc){
	if (isset($argv[$test]) AND ($argv[$test] == "-F" OR $argv[$test] == "-f" OR $argv[$test] == "--file") AND $test + 1 <= $argc)
	{
		$file = $argv[$test + 1];
		if (!is_file($file)){
			echo "Fichier invalide ($file)\n";
			exit;
		}
		$requete = file($file);
		$link = "/";
		$header = "";
		foreach ($requete as $num => $line){
			if (preg_match("#HTTP#", $line) OR preg_match("#Host: #", $line)){
				if (preg_match("#HTTP#", $line)){
					$tmp = explode(" ",$line);
					$link = $tmp[1];
				}
				if (preg_match("#Host: #", $line)){
					$tmp = str_replace('
', '', explode(" ",$line));
					$url = "http://".$tmp[1].$link;
				}
			}elseif(!preg_match("#Accept-Encoding: #", $line)){ // Non prise en compte de l'encodage
				$header .= $line;
			}
		}
		$content = file_get_contents(
		    $url,
		    FALSE,
		    stream_context_create(
			array(
			    'http' => array(
				'method' => 'POST',
				'header' => $header
			    )
			)
		    )
		);
		echo $content;
		exit;
	}
	$test++;
}

function verif_url($url){
	if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
		echo "invalid URL\n";
		exit;
	}
	return ($url);
}

function get_cookies($arr, $argv, $argc){
	$return = "";
	$test = 0;
	if (!isset($argv[0])){
		foreach ($arr["_COOKIE"] as $key => $value){
			if ($test != 0){
				$return .= ";";
			}else{
				$return = "cookie:";
			}
			$test++;
			$return .= "$key=$value";
		}
	}else{
		while ($test <= $argc){
			if (isset($argv[$test]) AND ($argv[$test] == "-C" OR $argv[$test] == "-c" OR $argv[$test] == "--cookie" OR $argv[$test] == "--cookies") AND $test + 1 <= $argc)
			{
				$return = "cookie:".$argv[$test + 1];
				$test = 10000;
			}
			$test++;
		}
	}
	return ($return."\r\n");
}

function get_get($arr, $url){
	$return = $url;
	$test = 0;
	if (!isset($argv[0])){
		foreach ($arr["_GET"] as $key => $value){
			if ($key != "url".$code AND $key != "referer".$code AND $key != "user-agent".$code){
				if ($test != 0){
					$return .= "&";
				}else{
					$return .= "?";
				}
				$test++;
				$return .= "$key=$value";
			}
		}
	}
	return ($return);
}

function get_post($argv, $argc){
	$return = "";
	$test = 0;
	while ($test <= $argc){
		if (isset($argv[$test]) AND ($argv[$test] == "-P" OR $argv[$test] == "-p" OR $argv[$test] == "--post") AND $test + 1 <= $argc)
		{
			$return = $argv[$test + 1];
			$test = 10000;
		}
		$test++;
	}
	return ($return);
}

function get_useragent($argv, $argc){
	$return = "";
	$test = 0;
	while ($test <= $argc){
		if (isset($argv[$test]) AND ($argv[$test] == "-U" OR $argv[$test] == "-u" OR $argv[$test] == "--user-agent") AND $test + 1 <= $argc)
		{
			$return = $argv[$test + 1];
			$test = 10000;
		}
		$test++;
	}
	if ($return == ""){
		$return = "User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0";
	}
	return ($return."\r\n");
}

function get_referer($argv, $argc){
	$return = "";
	$test = 0;
	while ($test <= $argc){
		if (isset($argv[$test]) AND ($argv[$test] == "-R" OR $argv[$test] == "-r" OR $argv[$test] == "--referer") AND $test + 1 <= $argc)
		{
			$return = $argv[$test + 1]."\r\n";
			$test = 10000;
		}
		$test++;
	}
	return ($return);
}


// Récuperation des variables
if (!isset($argv[0])){
	$arr = get_defined_vars();
	if (isset($_SERVER["REMOTE_ADDR"])){
		$ip=$_SERVER["REMOTE_ADDR"];
	}
	$log = "";
	foreach ($arr["_SERVER"] as $key1 => $value1) {$log .= "SERVER:$key1=$value1\n";}
	foreach ($arr["_POST"]   as $key2 => $value2) {$log .=   "POST:$key2=$value2\n";}
	foreach ($arr["_GET"]    as $key3 => $value3) {$log .=    "GET:$key3=$value3\n";}
	foreach ($arr["_COOKIE"] as $key4 => $value4) {$log .= "COOKIE:$key4=$value4\n";}
}


if ((isset($_GET["url".$code]) AND $_GET["url".$code] != "") OR (isset($argv[0]) AND $argv[0] != ""))
{
	if (isset($argv[0]) AND $argv[0] != ""){
		if (isset($argv[1]) AND $argv[1] != ""){
			// CLI
			$url       = verif_url($argv[1]);
			$cookie    = get_cookies("", $argv, $argc);
			$post      = get_post($argv, $argc);
			$useragent = get_useragent($argv, $argc);
			$referer   = get_referer($argv, $argc);
		}else{
			echo "Argument manquant (url)\n";
			exit;
		}

	}else{
		// PROXY
		$url     = verif_url($_GET["url".$code]);
		$cookie  = get_cookies($arr, "", "");
		$url     = get_get($arr, $url);
		$post 	 = http_build_query($arr["_POST"]);
		if (isset($_GET["referer".$code]) AND $_GET["referer".$code] != ""){
			$referer = $_GET["referer".$code]."\r\n";
		}else{
			$referer = "";
		}
		if (isset($_GET["user-agent".$code]) AND $_GET["user-agent".$code] != ""){
			$useragent = $_GET["user-agent".$code]."\r\n";
		}else{
			$useragent = "User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0\r\n";
		}
	}
}else{
	// DUMP
	echo $log;
	exit;
}


$content = file_get_contents(
    $url,
    FALSE,
    stream_context_create(
        array(
            'http' => array(
                'method' => 'POST',
		'header' => "Content-type: application/x-www-form-urlencoded".$eof
				.$useragent
				."Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8".$eof
				."Accept-Language: en-US,en;q=0.5".$eof
				//."Accept-Encoding: gzip, deflate".$eof
				."Referer: ".$url.$eof
				."Content-Length: ".strlen($data).$eof
				.$cookie
			    ,
                'content' => $post
            )
        )
    )
);
echo $content;

?>
