<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

class Webhook extends Controller
{
	/**
	* @var LINEBot
	*/
	private $bot;
	/**
	* @var Request
	*/
	private $request;
	/**
	* @var Response
	*/
	private $response;
	
	public function __construct(Request $request, Response $response) {
		$this->request = $request;
		$this->response = $response;

		$httpClient = new CurlHTTPClient('7BsGfbfyE/7uq9NX+mFjXndUEnF2p9le1F2srRWQRgh8MDMIUtKsiYZ7K4v2GKX0Twvi9Z8ipzdck1n2MXtX1BIrKoaZOmNtlB3HcRCKaZk6Fe4a3AuG5n/QHGrpyWoxvWVj6WEpuRqguN+DZaFq4wdB04t89/1O/w1cDnyilFU=');
		$this->bot  = new LINEBot($httpClient, ['channelSecret' => '1dfbde6acc6a7dcfdcedc082b63c0de7']);

		$this->handleEvents();
	}

	private function handleEvents()
	{
		$data = $this->request->all();

		if(is_array($data['events']))
		{
			foreach ($data['events'] as $event)
			{
				if($event['type'] == 'message')
				{
					if(method_exists($this, $event['message']['type'].'Message')){
						$this->{ $event['message']['type'].'Message' }($event);
					}
				}
				else
				{
					if(method_exists($this, $event['type'].'Callback'))
					{
						$this->{ $event['type'].'Callback' }($event);
					}
				}
			}
		}

		$this->response->setContent("No events found!");
		$this->response->setStatusCode(200);
		return $this->response;
	}

	private function followCallback($event)
	{
		$res = $this->bot->getProfile($event['source']['userId']);
		if ($res->isSucceeded())
		{
			$profile = $res->getJSONDecodedBody();

			$message1 = "";
			$message1 .= "Hallo, " . $profile['displayName'] . "!\n";
			$message1 .= "Terima kasih telah menambahkan kami kedalam pertemanan Anda.\n";
			$message1 .= "\n";
			$message1 .= "Anda dapat mengirimkan pesan \"HELP\" untuk melihat list kata kunci yang dapat digunakan.";
			$textMessageBuilder1 = new TextMessageBuilder($message1);
			
			$hex = "100020";
			$bin = hex2bin(str_repeat('0', 8-strlen($hex)) . $hex);
			$emoji = mb_convert_encoding($bin, 'UTF-8', 'UTF-32BE');

			$message2 = "";
			$message2 .= "Selalu jaga kesehatan! ".$emoji."\n";
			$message2 .= "#Covid19 #physicalDistancing #diRumahAja #jagaKebersihan";
			$textMessageBuilder2 = new TextMessageBuilder($message2);

			$stickerMessageBuilder = new StickerMessageBuilder(11538, 51626496);

			$multiMessageBuilder = new MultiMessageBuilder();
			$multiMessageBuilder->add($textMessageBuilder1);
			$multiMessageBuilder->add($textMessageBuilder2);
			$multiMessageBuilder->add($stickerMessageBuilder);

			$this->bot->replyMessage($event['replyToken'], $multiMessageBuilder);
		}
	}

	private function joinCallback($event)
	{
		$message1 = "";
		$message1 .= "Hi!\n";
		$message1 .= "Terima kasih telah mengundang kami.\n";
		$message1 .= "\n";
		$message1 .= "Anda dapat mengirimkan pesan \"HELP\" untuk melihat list kata kunci yang dapat digunakan.";
		$textMessageBuilder1 = new TextMessageBuilder($message1);
		
		$hex = "100020";
		$bin = hex2bin(str_repeat('0', 8-strlen($hex)) . $hex);
		$emoji = mb_convert_encoding($bin, 'UTF-8', 'UTF-32BE');

		$message2 = "";
		$message2 .= "Selalu jaga kesehatan! ".$emoji."\n";
		$message2 .= "#Covid19 #physicalDistancing #diRumahAja #jagaKebersihan";
		$textMessageBuilder2 = new TextMessageBuilder($message2);

		$stickerMessageBuilder = new StickerMessageBuilder(11538, 51626496);

		$multiMessageBuilder = new MultiMessageBuilder();
		$multiMessageBuilder->add($textMessageBuilder1);
		$multiMessageBuilder->add($textMessageBuilder2);
		$multiMessageBuilder->add($stickerMessageBuilder);

		$this->bot->replyMessage($event['replyToken'], $multiMessageBuilder);
	}

	private function sendAboutCorona($replyToken)
	{
		$message = "";
		$message .= "Coronavirus adalah keluarga besar virus yang dapat menyebabkan penyakit pada hewan atau manusia. Pada manusia, beberapa coronavirus diketahui menyebabkan infeksi pernafasan mulai dari flu biasa hingga penyakit yang lebih parah seperti Middle East Respiratory Syndrome (MERS) dan Severe Acute Respiratory Syndrome (SARS). Virus corona yang paling baru ditemukan menyebabkan penyakit coronavirus COVID-19.\n";
		$message .= "\n";
		$message .= "Coronaviruses are a large family of viruses which may cause illness in animals or humans.  In humans, several coronaviruses are known to cause respiratory infections ranging from the common cold to more severe diseases such as Middle East Respiratory Syndrome (MERS) and Severe Acute Respiratory Syndrome (SARS). The most recently discovered coronavirus causes coronavirus disease COVID-19.";
		
		$textMessageBuilder = new TextMessageBuilder($message);
		$this->bot->replyMessage($replyToken, $textMessageBuilder);
	}

	private function sendAboutCovid19($replyToken)
	{
		$message = "";
		$message .= "COVID-19 adalah penyakit menular yang disebabkan oleh coronavirus yang paling baru ditemukan. Virus dan penyakit baru ini tidak diketahui sebelum wabah dimulai di Wuhan, Cina, pada bulan Desember 2019.\n";
		$message .= "\n";
		$message .= "COVID-19 is the infectious disease caused by the most recently discovered coronavirus. This new virus and disease were unknown before the outbreak began in Wuhan, China, in December 2019.";

		$textMessageBuilder = new TextMessageBuilder($message);
		$this->bot->replyMessage($replyToken, $textMessageBuilder);
	}

	private function sendNews($replyToken)
	{
		$endpoint = 'https://www.who.int/rss-feeds/news-english.xml';
		$xml = simplexml_load_file($endpoint);
		$news = $xml->channel;

		$link = array(
			strval($news->item[0]->link),
			strval($news->item[1]->link),
			strval($news->item[2]->link),
			strval($news->item[3]->link),
			strval($news->item[4]->link)
		);
		$title = array(
			substr(trim($news->item[0]->title, " "), 0, 35)."...",
			substr(trim($news->item[1]->title, " "), 0, 35)."...",
			substr(trim($news->item[2]->title, " "), 0, 35)."...",
			substr(trim($news->item[3]->title, " "), 0, 35)."...",
			substr(trim($news->item[4]->title, " "), 0, 35)."..."
		);
		$pubDate = array(
			strval($news->item[0]->pubDate),
			strval($news->item[1]->pubDate),
			strval($news->item[2]->pubDate),
			strval($news->item[3]->pubDate),
			strval($news->item[4]->pubDate)
		);

		$imgURL = "https://storage.trubus.id/storage/app/public/posts/t20200301/big_d3bca9f9421b0ff826de1bf46a07e335f04807b9.jpg";
		$carouselTemplateBuilder = new CarouselTemplateBuilder(
			[
				new CarouselColumnTemplateBuilder(
					$title[0], $pubDate[0], $imgURL, [new UriTemplateActionBuilder('View Link', $link[0]),]
				),
				new CarouselColumnTemplateBuilder(
					$title[1], $pubDate[1], $imgURL, [new UriTemplateActionBuilder('View Link', $link[1]),]
				),
				new CarouselColumnTemplateBuilder(
					$title[2], $pubDate[2], $imgURL, [new UriTemplateActionBuilder('View Link', $link[2]),]
				),
				new CarouselColumnTemplateBuilder(
					$title[3], $pubDate[3], $imgURL, [new UriTemplateActionBuilder('View Link', $link[3]),]
				),
				new CarouselColumnTemplateBuilder(
					$title[4], $pubDate[4], $imgURL, [new UriTemplateActionBuilder('View Link', $link[4]),]
				),
			]
		);

		$templateMessageBuilder = new TemplateMessageBuilder('COVID-19 News', $carouselTemplateBuilder);
		$this->bot->replyMessage($replyToken, $templateMessageBuilder);
	}

	private function sendStatistic($replyToken, $countryCode)
	{
		$json = <<<JSON
		{
		  "type": "bubble",
		  "hero": {
		    "type": "image",
		    "url": "https://raw.githubusercontent.com/NovelCOVID/API/master/assets/flags/id.png",
		    "size": "full",
		    "aspectRatio": "20:13",
		    "aspectMode": "cover",
		    "action": {
		      "type": "uri",
		      "uri": "http://linecorp.com/"
		    }
		  },
		  "body": {
		    "type": "box",
		    "layout": "vertical",
		    "contents": [
		      {
		        "type": "text",
		        "text": "COUNTRY_NAME",
		        "weight": "bold",
		        "size": "xl"
		      },
		      {
		        "type": "box",
		        "layout": "baseline",
		        "margin": "md",
		        "contents": [
		          {
		            "type": "text",
		            "text": "Last updated:",
		            "size": "sm",
		            "color": "#999999",
		            "margin": "xs",
		            "flex": 0,
		            "style": "italic"
		          },
		          {
		            "type": "text",
		            "text": "April 5, 2020",
		            "size": "sm",
		            "color": "#999999",
		            "margin": "xs",
		            "flex": 0,
		            "style": "italic"
		          }
		        ]
		      },
		      {
		        "type": "box",
		        "layout": "vertical",
		        "margin": "lg",
		        "spacing": "sm",
		        "contents": [
		          {
		            "type": "box",
		            "layout": "baseline",
		            "spacing": "sm",
		            "contents": [
		              {
		                "type": "text",
		                "text": "Total Case",
		                "color": "#aaaaaa",
		                "size": "sm",
		                "flex": 2,
		                "wrap": true
		              },
		              {
		                "type": "text",
		                "text": "2092",
		                "wrap": true,
		                "color": "#666666",
		                "size": "sm",
		                "flex": 4,
		                "weight": "bold"
		              }
		            ]
		          },
		          {
		            "type": "box",
		            "layout": "baseline",
		            "spacing": "sm",
		            "contents": [
		              {
		                "type": "text",
		                "text": "Active Case",
		                "color": "#aaaaaa",
		                "size": "sm",
		                "flex": 2,
		                "wrap": true
		              },
		              {
		                "type": "text",
		                "text": "1751",
		                "wrap": true,
		                "color": "#666666",
		                "size": "sm",
		                "flex": 4,
		                "weight": "bold"
		              }
		            ]
		          },
		          {
		            "type": "box",
		            "layout": "baseline",
		            "spacing": "sm",
		            "contents": [
		              {
		                "type": "text",
		                "text": "Recovered",
		                "color": "#aaaaaa",
		                "size": "sm",
		                "flex": 2,
		                "wrap": true
		              },
		              {
		                "type": "text",
		                "text": "150",
		                "wrap": true,
		                "color": "#666666",
		                "size": "sm",
		                "flex": 4,
		                "weight": "bold"
		              }
		            ]
		          },
		          {
		            "type": "box",
		            "layout": "baseline",
		            "spacing": "sm",
		            "contents": [
		              {
		                "type": "text",
		                "text": "Death",
		                "color": "#aaaaaa",
		                "size": "sm",
		                "flex": 2,
		                "wrap": true
		              },
		              {
		                "type": "text",
		                "text": "191",
		                "wrap": true,
		                "color": "#666666",
		                "size": "sm",
		                "flex": 4,
		                "weight": "bold"
		              }
		            ]
		          }
		        ]
		      }
		    ]
		  }
		}
		JSON;

		if($countryCode == 'world')
		{
			$endpoint = 'https://corona.lmao.ninja/all';

			$buttonTemplateBuilder = new ButtonTemplateBuilder(
				"World Report",
				"Last updated: April 5, 2020",
				"https://upload.wikimedia.org/wikipedia/commons/e/ef/International_Flag_of_Planet_Earth.svg",
				[
					new MessageTemplateActionBuilder('See Detail Number', 'report world detail'),
				]
			);

			$templateMessageBuilder = new TemplateMessageBuilder('Country Report', $buttonTemplateBuilder);
			$this->bot->replyMessage($replyToken, $templateMessageBuilder);
		}
		else
		{
			$endpoint = 'https://corona.lmao.ninja/countries/'.$countryCode;

			$buttonTemplateBuilder = new ButtonTemplateBuilder(
				"Indonesia",
				"Last updated: April 5, 2020",
				"https://raw.githubusercontent.com/NovelCOVID/API/master/assets/flags/id.png",
				[
					new MessageTemplateActionBuilder('See Detail Number', 'report '.$countryCode.' detail'),
				]
			);

			$templateMessageBuilder = new TemplateMessageBuilder('Country Report', $buttonTemplateBuilder);
			$this->bot->replyMessage($replyToken, $templateMessageBuilder);

			// $httpClient->post(
			// 	LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply',
			// 	[
			// 		'replyToken' => $replyToken,
			// 		'message' => [
			// 			[
			// 				'type' => 'flex',
			// 				'altText' => 'Country Report',
			// 				'contents' => json_decode($json)
			// 			]
			// 		],
			// 	]
			// );
		}
	}

	private function sendCheck($replyToken)
	{
		$imgURL = 'https://asset.kompas.com/data/photo/special-page/infographic/62003201110455.jpeg';

		$imgMessageBuilder = new ImageMessageBuilder($imgURL, $imgURL);
		$this->bot->replyMessage($replyToken, $imgMessageBuilder);
	}

	private function sendSymptoms($replyToken)
	{
		$message = "";
		$message .= "Gejala COVID-19 yang paling umum adalah demam, kelelahan, dan batuk kering. Beberapa pasien mungkin mengalami sakit dan nyeri, hidung tersumbat, pilek, sakit tenggorokan atau diare. Gejala-gejala ini biasanya ringan dan mulai secara bertahap.\n";
		$message .= "\n";
		$message .= "Beberapa orang menjadi terinfeksi tetapi tidak mengembangkan gejala apa pun dan merasa tidak enak badan. Kebanyakan orang (sekitar 80%) pulih dari penyakit tanpa perlu perawatan khusus. Sekitar 1 dari setiap 6 orang yang mendapatkan COVID-19 sakit parah dan mengalami kesulitan bernapas.\n";
		$message .= "\n";
		$message .= "Orang yang lebih tua, dan mereka yang memiliki masalah medis yang mendasari seperti tekanan darah tinggi, masalah jantung atau diabetes, lebih mungkin untuk mengembangkan penyakit serius. Orang dengan demam, batuk dan kesulitan bernapas harus mencari perhatian medis.";

		$textMessageBuilder = new TextMessageBuilder($message);
		$this->bot->replyMessage($replyToken, $textMessageBuilder);
	}

	private function sendHealth($replyToken)
	{
		$message = "";
		$message .= "Cara menghindari penularan COVID-19:\n";
		$message .= "1. Cuci tangan dengan benar dan menggunakan sabun.\n";
		$message .= "2. Menjaga jarak atau physical distancing.\n";
		$message .= "3. Hindari kontak fisik dengan hewan yang berpotensi menularkan corona virus.\n";
		$message .= "4. Hindari menyentuh area wajah.\n";
		$message .= "5. Melakukan etika batuk dan bersin.\n";
		$message .= "6. Tetap di rumah dan cari bantuan medis jika sakit.\n";
		$message .= "7. Menggunakan masker kain untuk orang sehat, dan masker bedah untuk yang kurang sehat.\n";
		$message .= "8. Bersihkan barang pribadi dan perabotan rumah.\n";
		$message .= "9. Selalu mencuci bahan makanan.\n";
		$message .= "10. Menjaga daya tahan tubuh.\n";
		$message .= "11. Tetap produktif dan beribadah.";

		$textMessageBuilder = new TextMessageBuilder($message);
		$this->bot->replyMessage($replyToken, $textMessageBuilder);
	}

	private function sendContact($replyToken)
	{
		$message = "";
		$message .= "Hotline Covid-19 Kemenkes RI:\n";
		$message .= "119 EXT 9";

		$textMessageBuilder = new TextMessageBuilder($message);
		$this->bot->replyMessage($replyToken, $textMessageBuilder);
	}

	private function sendAbout($replyToken)
	{
		$message = "";
		$message .= "Chatbot ini ditujukan untuk membantu masyarakat mempermudah memperoleh informasi mengenai COVID-19.\n";
		$message .= "\n";
		$message .= "Informasi yang dapat ditampilkan di chatbot ini adalah berita mengenai COVID-19 dari WHO, laporan jumlah kasus COVID-19 di seluruh dunia ataupun negara yang diinginkan dimana bersumber dari referensi yang terpercaya.\n";
		$message .= "\n";
		$message .= "Semoga dengan adanya chatbot ini, masyarakat menjadi semakin teredukasi, terhindar dari hoax, serta dapat mengurangi persebaran COVID-19.\n";
		$message .= "\n";
		$message .= "Silahkan kirim pesan \"HELP\" untuk dapat melihat kata kunci yang dapat digunakan.\n";
		$message .= "\n";
		$message .= "Terima kasih.";

		$textMessageBuilder = new TextMessageBuilder($message);
		$this->bot->replyMessage($replyToken, $textMessageBuilder);
	}

	private function sendHelp($replyToken)
	{
		$message = "";
		$message .= "Berikut list kata kunci yang dapat Anda gunakan:\n";
		$message .= "- corona\n";
		$message .= "   Untuk menampilkan informasi mengenai apa itu corona.\n";
		$message .= "- covid19\n";
		$message .= "   Untuk menampilkan informasi mengenai apa itu COVID-19.\n";
		$message .= "- news\n";
		$message .= "   Untuk menampilkan berita terkini dari WHO.\n";
		$message .= "- report world\n";
		$message .= "   Untuk menampilkan rangkuman laporan dari data seluruh dunia.\n";
		$message .= "- report [country_code]\n";
		$message .= "   Untuk menampilkan rangkuman laporan dari kode negara yang dimasukkan, misal \"report IDN\".\n";
		$message .= "- steps check\n";
		$message .= "   Untuk menampilkan informasi siapa saja yang perlu melakukan pemeriksaan ke rumah sakit terkait COVID-19.\n";
		$message .= "- steps symptoms\n";
		$message .= "   Untuk menampilkan informasi mengenai gejala dari COVID-19.\n";
		$message .= "- steps health\n";
		$message .= "   Untuk menampilkan informasi bagaimana menjaga kesehatan dan kebersihan agar terhindar dari COVID-19.\n";
		$message .= "- about\n";
		$message .= "   Untuk menampilkan informasi mengenai chatbot ini.\n";

		$textMessageBuilder = new TextMessageBuilder($message);
		$this->bot->replyMessage($replyToken, $textMessageBuilder);
	}

	private function interactiveTalk($replyToken, $message)
	{
		$textMessageBuilder = new TextMessageBuilder($message);
		$this->bot->replyMessage($replyToken, $textMessageBuilder);
	}

	private function suggestion($replyToken, $keyword)
	{
		switch ($keyword) {
			case 'steps':
				$hex = "100010";
				$bin = hex2bin(str_repeat('0', 8-strlen($hex)) . $hex);
				$emoji = mb_convert_encoding($bin, 'UTF-8', 'UTF-32BE');

				$message = "Parameter yang dimasukkan tidak sesuai ".$emoji."\n";
				$message .= "Suggestion:\n";
				$message .= "- steps check: Untuk menampilkan informasi siapa saja yang perlu melakukan pemeriksaan ke rumah sakit terkait COVID-19.\n";
				$message .= "- steps symptoms: Untuk menampilkan informasi mengenai gejala dari COVID-19.\n";
				$message .= "- steps health: Untuk menampilkan informasi bagaimana menjaga kesehatan dan kebersihan agar terhindar dari COVID-19.\n";
				$message .= "\n";
				$message .= "Kirim pesan \"HELP\" untuk menampilkan kata kunci lainnya yang tersedia.";

				$textMessageBuilder = new TextMessageBuilder($message);
				$this->bot->replyMessage($replyToken, $textMessageBuilder);
				break;
		}
		
	}

	private function textMessage($event)
	{
		$userMessage = $event['message']['text'];
		$userMessage = strtolower($userMessage);

		$words = explode(' ', trim($userMessage));
		$totalWords = count($words);

		switch ($words[0]) {
			case 'test':
				$this->interactiveTalk($event['replyToken'], "OK");
				break;
			case 'hallo':
			case 'hello':
				$message = 'Hi!';

				if($event['source']['type'] == 'group' || $event['source']['type'] == 'room')
				{
					if($event['source']['userId'])
					{
						$userId = $event['source']['userId'];
						$getprofile = $this->bot->getProfile($userId);
						$profile = $getprofile->getJSONDecodedBody();

						$message = "Hi, ".$profile['displayName'];
					}
				}
				else
				{
					$res = $this->bot->getProfile($event['source']['userId']);
					if ($res->isSucceeded())
					{
						$profile = $res->getJSONDecodedBody();

						$message = "Hi, ".$profile['displayName']."!";
					}
				}

				$this->interactiveTalk($event['replyToken'], $message);
				break;
			case 'hi':
			case 'hai':
			case 'hei':
				$message = 'Hallo!';

				if($event['source']['type'] == 'group' || $event['source']['type'] == 'room')
				{
					if($event['source']['userId'])
					{
						$userId = $event['source']['userId'];
						$getprofile = $this->bot->getProfile($userId);
						$profile = $getprofile->getJSONDecodedBody();

						$message = "Hallo, ".$profile['displayName'];
					}
				}
				else
				{
					$res = $this->bot->getProfile($event['source']['userId']);
					if ($res->isSucceeded())
					{
						$profile = $res->getJSONDecodedBody();

						$message = "Hallo, ".$profile['displayName']."!";
					}
				}

				$this->interactiveTalk($event['replyToken'], $message);
				break;

			case 'corona':
			case 'coronavirus':
			case 'corona virus':
				$this->sendAboutCorona($event['replyToken']);
				break;
			case 'cov':
			case 'covid':
			case 'cov19':
			case 'covid19':
			case 'cov-19':
			case 'covid-19':
				$this->sendAboutCovid19($event['replyToken']);
				break;
			case 'news':
				$this->sendNews($event['replyToken']);
				break;
			case 'report':
				if($totalWords === 1 || $words[1] == 'world')
				{
					$this->sendStatistic($event['replyToken'], 'world');
				}
				else
				{
					$this->sendStatistic($event['replyToken'], $words[1]);
				}
				break;
			case 'step':
			case 'steps':
				if($totalWords > 1)
				{
					if($words[1] == 'check')
					{
						$this->sendCheck($event['replyToken']);
					}
					else if($words[1] == 'symptoms')
					{
						$this->sendSymptoms($event['replyToken']);
					}
					else if($words[1] == 'clean' || $words[1] == 'health')
					{
						$this->sendHealth($event['replyToken']);
					}
					else
					{
						$this->suggestion($event['replyToken'], 'steps');
					}
				}
				else
				{
					$this->suggestion($event['replyToken'], 'steps');
				}
				break;
			case 'contact':
					$this->sendContact($event['replyToken']);
				break;
			case 'about':
				$this->sendAbout($event['replyToken']);
				break;
			case 'help':
				$this->sendHelp($event['replyToken']);
				break;

			default:
				if($event['source']['type'] != 'group' || $event['source']['type'] != 'room')
				{
					$hex = "100010";
					$bin = hex2bin(str_repeat('0', 8-strlen($hex)) . $hex);
					$emoji = mb_convert_encoding($bin, 'UTF-8', 'UTF-32BE');

					$message = "Kata kunci tidak ditemukan ".$emoji."\n";
					$message .= "Kirim pesan \"HELP\" untuk menampilkan kata kunci yang tersedia.";

					$textMessageBuilder = new TextMessageBuilder($message);
					$this->bot->replyMessage($event['replyToken'], $textMessageBuilder);
				}
				break;
		}
	}
}
