<?php
class Securite
{
	// Donn�es entrantes
	static function bdd($string)
	{
	// On regarde si le type de string est un nombre entier (int)
	if(ctype_digit($string))
		{
			$string = intval($string);
		}
	// Pour tous les autres types
	else
		{
			$string = trim($string);
			$string = mysql_real_escape_string($string);
		}
	return $string;
	}
	// Donn�es sortantes
	static function html($string)
	{
	/*
		// Liste des caract�res sp�ciaux et de leurs valeurs
	    $html_entities = array ( 
		// Caract�res tr�s sp�cifique
		"�" =>  "&iexcl;",     #inverted exclamation mark
		"�" =>  "&cent;",     #cent
		"�" =>  "&pound;",     #pound
		"�" =>  "&curren;",     #currency
		"�" =>  "&yen;",     #yen
		"�" =>  "&brvbar;",     #broken vertical bar
		"�" =>  "&sect;",     #section
		"�" =>  "&uml;",     #spacing diaeresis
		"�" =>  "&copy;",     #copyright
		"�" =>  "&ordf;",     #feminine ordinal indicator
		"�" =>  "&laquo;",     #angle quotation mark (left)
		"�" =>  "&not;",     #negation
		"�" =>  "&reg;",     #registered trademark
		"�" =>  "&macr;",     #spacing macron
		"�" =>  "&deg;",     #degree
		"�" =>  "&plusmn;",     #plus-or-minus 
		"�" =>  "&sup2;",     #superscript 2
		"�" =>  "&sup3;",     #superscript 3
		"�" =>  "&acute;",     #spacing acute
		"�" =>  "&micro;",     #micro
		"�" =>  "&para;",     #paragraph
		"�" =>  "&middot;",     #middle dot
		"�" =>  "&cedil;",     #spacing cedilla
		"�" =>  "&sup1;",     #superscript 1
		"�" =>  "&ordm;",     #masculine ordinal indicator
		"�" =>  "&raquo;",     #angle quotation mark (right)
		"�" =>  "&frac14;",     #fraction 1/4
		"�" =>  "&frac12;",     #fraction 1/2
		"�" =>  "&frac34;",     #fraction 3/4
		"�" =>  "&iquest;",     #inverted question mark
		"�" =>  "&times;",     #multiplication
		"�" =>  "&divide;",     #division
		// Caracteres
		"�" =>  "&Agrave;",     #capital a, grave accent
		"�" =>  "&Aacute;",     #capital a, acute accent
		"�" =>  "&Acirc;",     #capital a, circumflex accent
		"�" =>  "&Atilde;",     #capital a, tilde
		"�" =>  "&Auml;",     #capital a, umlaut mark
		"�" =>  "&Aring;",     #capital a, ring
		"�" =>  "&AElig;",     #capital ae
		"�" =>  "&Ccedil;",     #capital c, cedilla
		"�" =>  "&Egrave;",     #capital e, grave accent
		"�" =>  "&Eacute;",     #capital e, acute accent
		"�" =>  "&Ecirc;",     #capital e, circumflex accent
		"�" =>  "&Euml;",     #capital e, umlaut mark
		"�" =>  "&Igrave;",     #capital i, grave accent
		"�" =>  "&Iacute;",     #capital i, acute accent
		"�" =>  "&Icirc;",     #capital i, circumflex accent
		"�" =>  "&Iuml;",     #capital i, umlaut mark
		"�" =>  "&ETH;",     #capital eth, Icelandic
		"�" =>  "&Ntilde;",     #capital n, tilde
		"�" =>  "&Ograve;",     #capital o, grave accent
		"�" =>  "&Oacute;",     #capital o, acute accent
		"�" =>  "&Ocirc;",     #capital o, circumflex accent
		"�" =>  "&Otilde;",     #capital o, tilde
		"�" =>  "&Ouml;",     #capital o, umlaut mark
		"�" =>  "&Oslash;",     #capital o, slash
		"�" =>  "&Ugrave;",     #capital u, grave accent
		"�" =>  "&Uacute;",     #capital u, acute accent
		"�" =>  "&Ucirc;",     #capital u, circumflex accent
		"�" =>  "&Uuml;",     #capital u, umlaut mark
		"�" =>  "&Yacute;",     #capital y, acute accent
		"�" =>  "&THORN;",     #capital THORN, Icelandic
		"�" =>  "&Yuml;",     #latin capital letter Y
		
		"�" =>  "&szlig;",     #small sharp s, German

		"�" =>  "&agrave;",     #small a, grave accent
		"�" =>  "&aacute;",     #small a, acute accent
		"�" =>  "&acirc;",     #small a, circumflex accent
		"�" =>  "&atilde;",     #small a, tilde
		"�" =>  "&auml;",     #small a, umlaut mark
		"�" =>  "&aring;",     #small a, ring
		"�" =>  "&aelig;",     #small ae
		"�" =>  "&ccedil;",     #small c, cedilla
		"�" =>  "&egrave;",     #small e, grave accent
		"�" =>  "&eacute;",     #small e, acute accent
		"�" =>  "&ecirc;",     #small e, circumflex accent
		"�" =>  "&euml;",     #small e, umlaut mark
		"�" =>  "&igrave;",     #small i, grave accent
		"�" =>  "&iacute;",     #small i, acute accent
		"�" =>  "&icirc;",     #small i, circumflex accent
		"�" =>  "&iuml;",     #small i, umlaut mark
		"�" =>  "&eth;",     #small eth, Icelandic
		"�" =>  "&ntilde;",     #small n, tilde
		"�" =>  "&ograve;",     #small o, grave accent
		"�" =>  "&oacute;",     #small o, acute accent
		"�" =>  "&ocirc;",     #small o, circumflex accent
		"�" =>  "&otilde;",     #small o, tilde
		"�" =>  "&ouml;",     #small o, umlaut mark
		"�" =>  "&oslash;",     #small o, slash
		"�" =>  "&ugrave;",     #small u, grave accent
		"�" =>  "&uacute;",     #small u, acute accent
		"�" =>  "&ucirc;",     #small u, circumflex accent
		"�" =>  "&uuml;",     #small u, umlaut mark
		"�" =>  "&yacute;",     #small y, acute accent
		"�" =>  "&thorn;",     #small thorn, Icelandic
		"�" =>  "&yuml;",     #small y, umlaut mark
    );
	// Convertis les caract�res
	foreach ($html_entities as $key => $value)
	{
	$string = str_replace($key, $value, $string);
	}*/
		// S�curisation des variable HTML
		$string = htmlentities($string);
		return $string;
	}
	// D�prot�ge le code
	static function unhtml($string)
	{
	return html_entity_decode($string);
	}
	// Cryptage irr�versible
	static function Hcrypt ($str){
	return str_rot13(base64_encode(md5(magicword.$str)));
	}
	// Fonctions de cryptage simple
	static function crypt ($str){
	return str_rot13(base64_encode($str));
	}
	static function decrypt ($str){
	return base64_decode(str_rot13($str));
	}	

	//Ici on regarde si il existe la variable globale $_SERVEUR 
	static function ipX() {
	// On test si $_SERVER exist
	if (isSet($_SERVER))
		{
		if (isSet($_SERVER["HTTP_X_FORWARDED_FOR"]))
			{
			$ipx = $_SERVER["HTTP_X_FORWARDED_FOR"];
			}
		elseif (isSet($_SERVER["HTTP_CLIENT_IP"]))
			{
			$ipx = $_SERVER["HTTP_CLIENT_IP"];
			}
		else
			{
			$ipx = $_SERVER["REMOTE_ADDR"];
			}
		}
	// Sinon on utilise une ancienne methode
	else
		{
		// getenv � Retourne la valeur d'une variable d'environnement
		if ( getenv( 'HTTP_X_FORWARDED_FOR' ) )
			{
			$ipx = getenv( 'HTTP_X_FORWARDED_FOR' );
			}
		elseif ( getenv( 'HTTP_CLIENT_IP' ) )
			{
			$ipx = getenv( 'HTTP_CLIENT_IP' );
			}
		else
			{
			$ipx = getenv( 'REMOTE_ADDR' );
			}
		}
	return trim($ipx);
	}

	static function isLockIp($ip=NULL)
	{
	$ip=(empty($ip)) ? Securite::ipX() : $ip;

	$oCache = new Cache('iplock');
	$tableIpLock = $oCache->getCache();
	
		if (array_key_exists($ip, $tableIpLock))
		{
		return true;
		}
		else
		{
		return false;
		}
	}
	
	static function toLockIp($ip=NULL)
	{
	$ip=(empty($ip)) ? Securite::ipX() : $ip;

	$oCache = new Cache('iplock');
	$tableIpLock = $oCache->getCache();
	
		if (array_key_exists($ip, $tableIpLock))
		{
		$tableIpLock[$ip]=true;
		$oCache->setCache($tableIpLock);
		return true;
		}
		else
		{
		$tableIpLock[$ip]=true;
		$oCache->setCache($tableIpLock);
		return true;
		}
	}
	
	
	static function referer(){
	$part = array(0=>"scheme","host","port","user","pass","path","query","fragment");
	$result = array_flip($part);
		if(isset($_SERVER['HTTP_REFERER']))
		{
		$parse_url = parse_url($_SERVER['HTTP_REFERER']);
			if(get_magic_quotes_gpc() == 1)
			{
				while(list($key,$val) = each($parse_url))
				{
				$result["$key"] = $val;
				}
			}
			else
			{
				while(list($key,$val) = each($parse_url))
				{
				$result["$key"] = addslashes($val);
				}
			}
		}
	return $result;
	}

	static function isMail($string){
	if (preg_match("/^[a-z0-9._-]+@[a-z0-9.-]{2,}[.][a-z]{2,3}$/", $string)) {
		return true;
	} else {
		return false;
	}
	
	}
}

?>