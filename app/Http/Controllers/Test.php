<?php
namespace App\Http\Controllers;

class Test extends Controller
{
	public function testXML()
	{
		$endpoint = 'https://www.who.int/rss-feeds/news-english.xml';
		$xml = simplexml_load_file($endpoint);

		$news = $xml->channel;
		echo $news->item[0]->link;
		// $totalItem = count($xml->channel->item);
		// for ($i=0; $i<5; $i++) {
		// 	$item = $xml->channel->item[$i];
		// 	echo "NO: ".$i."<br>";
		// 	echo "Title: ".$item->title."<br>";

		// 	$idx = 200;
		// 	if(strlen(trim($item->description, " ")) > $idx) {
		// 		echo "Description: ".substr(trim($item->description, " "), 0, $idx)."...<br>";
		// 	}
		// 	else {
		// 		echo "Description: ".trim($item->description, " ")."<br>";
		// 	}
			
		// 	echo "Published: ".$item->pubDate."<br>";
		// 	echo "Link: ".$item->link."<br>";
		// 	echo "<br>";
		// }
		// var_dump($xml);
	}
}
