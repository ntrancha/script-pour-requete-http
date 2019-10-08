<?php
# **************************************************************************** #
#                                                                              #
#                                                         :::      ::::::::    #
#    requete_v2.php                                     :+:      :+:    :+:    #
#                                                     +:+ +:+         +:+      #
#    By: ntrancha <ntrancha@student.42.fr>          +#+  +:+       +#+         #
#                                                 +#+#+#+#+#+   +#+            #
#    Created: 2019/07/10 10:51:33 by ntrancha          #+#    #+#              #
#    Updated: 2019/08/10 18:21:02 by ntrancha         ###   ########.fr        #
#                                                                              #
# **************************************************************************** #

function is_cli(){

	// Vérification de l'invocation	en CLI
	if (defined('STDIN'))							{return true;}
	if (php_sapi_name() === 'cli')					        {return true;}
	if (array_key_exists('SHELL', $_ENV))				        {return true;}
	if (!array_key_exists('REQUEST_METHOD', $_SERVER))		        {return true;}
	if (empty($_SERVER['REMOTE_ADDR']) and count($_SERVER['argv']) > 0)	{return true;} 
	return false;
}

function get_param_web($var){

	// Récupération des variables
	$get	= $var["_GET"];
	$list	= array("link", "method", "get", "post", "cookie", "referer", "user-agent");
	$list	= array_fill_keys($list, "");

	if (isset($var["_SERVER"]["HTTP_USER_AGENT"])){
		$list["user-agent"] = $var["_SERVER"]["HTTP_USER_AGENT"];
	}
	if (isset($var["_SERVER"]["HTTP_REFERER"])){
		$list["referer"] = $var["_SERVER"]["HTTP_REFERER"];
	}

	$list["cookie"] = http_build_query($var["_COOKIE"], "", ";");
	$list["post"]   = http_build_query($var["_POST"], "", ";");
	$list["method"] = $var["_SERVER"]["REQUEST_METHOD"];

	foreach ($get as $key => $value){
		$test = 0;
		foreach ($list as $k => $v){
			if ($k == $key){
				$list[$key] = $value;
				unset($get[$key]);
			}
		}
	}
	$list["get"] = http_build_query($get); 
	return ($list);
}

function get_param_cli($argc, $argv){

	// Liste des options possibles en CLI
	$list = array(  //Option    		Paramètres	Contenu
			"link"		        => 1,  		//	url
			"file"		        => 1,   	//	path
			"output"	        => 1,   	//	path
			"method"        	=> 1,   	//	data
			"post"	        	=> 1,   	//	data
			"get"       		=> 1,   	//	data
			"cookie"	        => 1,   	//	data
			"referer"	        => 1,   	//	referer
			"user-agent"	    	=> 1,   	//	user-agent
			"brute-force"	    	=> 3,   	//	path  methode  id
			"display"	        => 0,   	//	/
			"exit"	        	=> 0,   	//	/
			"help"	        	=> 0    	//	/
		);
	
	// Création de la liste des options utilisées avec leurs paramètres
	$options = array_fill_keys($list, "");

	// Récupération des paramètres
	$count = 1;
	while ($count <= $argc){
		if (isset($argv[$count])){
			$param   = strtolower($argv[$count]);
			if (substr($param, 0, 1) == "-"){

				$mode = 0;
				if (substr($param, 0, 2) == "--"){
					$param_f = substr($param, 2, strlen($param) - 2);
					$param	 = substr($param, 2, 1);
					$mode    = 2;
				}elseif (substr($param, 0, 1) == "-" AND strlen($param) == 2){
					$param   = substr($param, 1, 1);
					$mode    = 1;
				}

				$param_valide = 0;
				foreach ($list as $valeur => $num){
					if ($mode == 1 AND $param   == substr($valeur, 0, 1)
						OR $mode == 2 AND $param_f == $valeur){

						// Paramètre valide
						$param_valide++;
						$content   = array();
						$content_n = 1;
						while ($content_n <= $num){
							if (isset($argv[$count + $content_n])){
								array_push($content, $argv[$count + $content_n]);
							}
							$content_n++;
							$count++;
						}

						if (is_array($options[$valeur])){
							echo "Erreur: Paramètre déjà utilisé (".$argv[$count].")\n";
							exit;
						}else{
							$options[$valeur] = $content;
						}
					}
				}

				if ($param_valide == 0){
					// Paramètre invalide
					echo "Erreur: Paramètre non reconnu (".$argv[$count].")\n";
					exit;
				}
			}else{
				echo "Erreur: Paramètre non reconnu (".$argv[$count].")\n";
				exit;
			}
		}
		$count++;
	}
	return ($options);
}

function verif_url($url){
	if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
		echo "Erreur: invalid URL ($url)\n";
		exit;
	}
	return ($url);
}

function requete_context($method, $header, $content){

	$context	= array();
	$list_method	= array("GET", "POST", "PUT", "HEAD", "DELETE", "PATCH", "OPTIONS");

		// METHOD //
	if ($method == ""){
		echo "Erreur: method invalide\n";
		exit;
	}
	if (!array_key_exists($list_mehtod, $method)){
		echo "Avertissement: method \"$method\" inconnu\n";
	}
	array_push($context['method'], $method);

		// HEADER //
	if ($header == ""){
		echo "Erreur: Header invalide\n";
		exit;
	}
	array_push($context['header'], $header);

		// CONTENT //
	if ($method == "POST" AND $content == ""){
		echo "Erreur: Content manquant\n";
		exit;
	}
	if ($content != ""){
		array_push($context['content'], $content);
	}
	return ($context);
}


function send_requete($url, $context){
	if (verif_url($url) == "" OR !is_array($content) OR empty($content)){
		echo "Erreur: Context invalide\n";
		exit;
	}
	return (file_get_contents($url, FALSE, stream_context_create(array('http' => array($context)))));
}

//$options = get_param_cli($argc, $argv);
var_dump(get_param_web(get_defined_vars()));


?>
