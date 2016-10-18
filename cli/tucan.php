<?php
// use Box\Spout\Reader\Wrapper\SimpleXMLElement;
require_once '../../../config.php';

error_reporting ( E_ALL );
ini_set ( 'display_errors', 'On' );
ini_set ( 'html_errors', 1 );
class TUCaN {
	private $BASEURL = "https://www.tucan.tu-darmstadt.de";
	private $REL_URL_START_PAGE = "/scripts/mgrqcgi?APPNAME=CampusNet&PRGNAME=EXTERNALPAGES&ARGUMENTS=-N000000000000001,-N000344,-Awelcome";
	private $URL_SEARCH_PAGE;
	private $examples = [ 
			0 => [
					'id' => 6613,
					'semester' => 'SoSe 2016',
					'shortname' => 'Empirische Wirtschaftsforschung 01-64-0002-vl SoSe 2016',
					'idnumber' => '358774093771103'
			],
			1 => [ 
					'id' => 1896,
					'semester' => 'WiSe 2013/14',
					'shortname' => 'Investition und Finanzierung 01-16-0001-vl WiSe 2013/14',
					'idnumber' => '350025633908610' 
			] 
	];
	function __construct() {
		$this->URL_SEARCH_PAGE = $this->BASEURL . $this->get_link_for_veranstaltungssuche ();
		
		// $veranstaltungsnummer = $this->get_veranstaltungsnummer($this->examples[0]['shortname']);
		
		$this->look_for_veranstaltung ( $this->examples [0] );
	}
	private function look_for_veranstaltung($course) {
		$search = $this->get_web_page ( $this->URL_SEARCH_PAGE );
		
		$matches = array ();
		preg_match_all ( "(<input.+\/>)", $search ['content'], $matches );
		$matches = $matches [0];
		// $this->printer ( $matches );
		$params = array ();
		
		$i = 0;
		foreach ( $matches as $id => $input_html ) {
			if (strpos ( $input_html, 'type="hidden"' ) !== false && $i >= 8) {
				// $input_html = str_replace ( ' /', '', $input_html );
				$input_html = preg_replace ( '!\s+!', ' ', $input_html );
				
				//$this->printer ( htmlentities ( $input_html ) );
				$input = new SimpleXMLElement ( $input_html );
				
				if (( string ) $input ['type'] == 'hidden') {
					$params [] = ( string ) $input ['name'] . "=" .( string ) $input ['value'];
				}
			}
			$i++;
		}
		
		$poststring = implode("&", $params);
		$poststring .= "&course_number=" . $this->get_veranstaltungsnummer($course['shortname']);
		$poststring .= "&submit_search=Suche";
		$poststring .= "&course_catalogue=359975970421241";
		
		$this->printer(explode("&", $poststring));
		
		$url = $this->BASEURL . "/scripts/mgrqcgi";
		
		$search_result = $this->get_web_page($url, $poststring);
		
	
		$this->printer($search_result);
		
		
		
		//for
	}
	private function get_link_for_veranstaltungssuche() {
		$startpage = $this->get_web_page ( $this->BASEURL . $this->REL_URL_START_PAGE );
		
		$pattern = '(<a[ ="_0-9/?&;\-A-Za-z]+>Lehrveranstaltungssuche<\/a>)';
		$matches = array ();
		preg_match ( $pattern, $startpage ['content'], $matches );
		$a = new SimpleXMLElement ( $matches [0] );
		return ( string ) $a ['href'];
	}
	private function get_veranstaltungsnummer($shortname) {
		$matches = array ();
		
		$pattern = "(\d{2}-\d{2}-\d{4}-[A-Za-z]{2})";
		preg_match ( $pattern, $shortname, $matches );
		if (isset ( $matches [0] )) {
			return $matches [0];
		}
	}
	private function get_tucan_url_to_description_page($idnumber) {
		return "https://www.tucan.tu-darmstadt.de/scripts/mgrqcgi?APPNAME=CampusNet&PRGNAME=COURSEDETAILS&ARGUMENTS=-N000000000000001,-N000335,-N0,-N" . $idnumber; // .",-N358774093750104,-N0,-N0,-N3";
	}
	private function get_web_page($url, $poststring = null) {
		$options = array (
				CURLOPT_RETURNTRANSFER => true, // return web page
				CURLOPT_HEADER => true, // don't return headers
				CURLOPT_FOLLOWLOCATION => false, // follow redirects
				CURLOPT_ENCODING => "", // handle all encodings
				CURLOPT_USERAGENT => "spider", // who am i
				CURLOPT_AUTOREFERER => false, // set referer on redirect
				CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
				CURLOPT_TIMEOUT => 5, // timeout on response
				CURLOPT_MAXREDIRS => 0, // stop after 10 redirects
				CURLOPT_SSL_VERIFYPEER => false 
		); // Disabled SSL Cert checks

		$ch = curl_init ( $url );
		if($poststring) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,
					$poststring);
		}
		
		curl_setopt_array ( $ch, $options );
		$content = curl_exec ( $ch );
		$err = curl_errno ( $ch );
		$errmsg = curl_error ( $ch );
		$header = curl_getinfo ( $ch );
		curl_close ( $ch );
		
		$header ['errno'] = $err;
		$header ['errmsg'] = $errmsg;
		$header ['content'] = $content;
		return $header;
	}
	function printer($o) {
		echo "OUTPUT:";
		echo "<pre>" . print_r ( $o, true ) . "</pre>";
	}
}

$t = new TUCaN ();

//curl 'https://www.tucan.tu-darmstadt.de/scripts/mgrqcgi?APPNAME=CampusNet&PRGNAME=EXTERNALPAGES&ARGUMENTS=-N000000000000001,-N000344,-Awelcome' -H 'Pragma: no-cache' -H 'Accept-Encoding: gzip, deflate, sdch, br' -H 'Accept-Language: de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4' -H 'Upgrade-Insecure-Requests: 1' -H 'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8' -H 'Referer: https://www.tucan.tu-darmstadt.de/scripts/mgrqcgi?APPNAME=CampusNet&PRGNAME=STARTPAGE_DISPATCH&ARGUMENTS=-N000000000000001' -H 'Cookie: cnsc=0' -H 'Connection: keep-alive' -H 'Cache-Control: no-cache' --compressed