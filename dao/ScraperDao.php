<?php
namespace F3\Dao;

use DOMDocument;
use DOMXPath;

/**
 * DAO class for screen scraping a website.
 *
 * @author bbischoff
 */
class ScraperDao {
	
	public function __construct() {
	}

	public function parsePost($url) {
		// call to get the contents of the post
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$html = curl_exec($ch);
		curl_close($ch);
		
		// parse the html contents to a DOM object
		$doc = new DOMDocument();
		libxml_use_internal_errors(true);
		$doc->loadHTML($html);
		$xpath = new DOMXPath($doc);
		
		// query to get the workout date
		$dateNode = $xpath->query("//ul/li/strong[text()='When:']")->item(0);
		$dateStr = trim($dateNode->nextSibling->nodeValue);
		
		// query to get the Q
		$qNode = $xpath->query("//ul/li/strong[text()='QIC:']")->item(0);
		$qStr = trim($qNode->nextSibling->nodeValue);
		
		// query to get the PAX
		$paxNode = $xpath->query("//ul/li/strong[text()='The PAX:']")->item(0);
		$paxStr = trim($paxNode->nextSibling->nodeValue);
		$split = preg_split("/,|\band\b/", $paxStr);
		// trim values and remove empty values from the array
		$paxArray = array_filter(array_map('trim', $split));
		
		// query to get the tags
		$tags = $xpath->query('//div[@class="categories"]/a[@rel="tag"]/text()');
		$tagsArray = array();
		foreach($tags as $tagNode){
			$tagsArray[] = $tagNode->nodeValue;
		}
		
		// create an object to return;
		return (object) array('q' => $qStr, 'pax' => $paxArray, 'tags' => $tagsArray);
	}
}
?>