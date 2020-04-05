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
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
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

	private function sendNews($replyToken)
	{
		$endpoint = 'https://www.who.int/rss-feeds/news-english.xml';

		$message = 'Send News from '.$endpoint.' ...';

		$textMessageBuilder = new TextMessageBuilder($message);
		$this->bot->replyMessage($replyToken, $textMessageBuilder);
	}

	private function sendStatistic($replyToken, $countryCode='IDN')
	{
		if($countryCode == 'world')
		{
			$endpoint = 'https://corona.lmao.ninja/all';

			$message = 'Send World Report from '.$endpoint.' ...';

			$textMessageBuilder = new TextMessageBuilder($message);
			$this->bot->replyMessage($replyToken, $textMessageBuilder);
		}
		else
		{
			$endpoint = 'https://corona.lmao.ninja/countries/'.$countryCode;

			$message = 'Send Report from '.$endpoint.' ...';

			$textMessageBuilder = new TextMessageBuilder($message);
			$this->bot->replyMessage($replyToken, $textMessageBuilder);
		}
	}

	private function sendCheck($replyToken)
	{
		$imgURL = 'https://asset.kompas.com/data/photo/special-page/infographic/62003201110455.jpeg';

		$imgMessageBuilder = new ImageMessageBuilder($imgURL, $imgURL);
		$this->bot->replyMessage($replyToken, $imgMessageBuilder);
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
		$message .= "- news\n";
		$message .= "   Untuk menampilkan berita terkini dari WHO.\n";
		$message .= "- report world\n";
		$message .= "   Untuk menampilkan rangkuman laporan dari data seluruh dunia.\n";
		$message .= "- report [country_code]\n";
		$message .= "   Untuk menampilkan rangkuman laporan dari kode negara yang dimasukkan, misal \"report IDN\".\n";
		$message .= "- steps check\n";
		$message .= "   Untuk menampilkan informasi siapa saja yang perlu melakukan pemeriksaan ke rumah sakit terkait COVID-19.\n";
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

	private function textMessage($event)
	{
		$userMessage = $event['message']['text'];
		$userMessage = strtolower($userMessage);

		$words = explode(' ', trim($userMessage));

		switch ($words[0]) {
			case 'hallo':
			case 'hello':
				$res = $this->bot->getProfile($event['source']['userId']);
				if ($res->isSucceeded())
				{
					$profile = $res->getJSONDecodedBody();

					$message = "Hi, ".$profile['displayName']."!";
					$this->interactiveTalk($event['replyToken'], $message);
				}
				break;
			case 'hi':
			case 'hai':
			case 'hei':
				$res = $this->bot->getProfile($event['source']['userId']);
				if ($res->isSucceeded())
				{
					$profile = $res->getJSONDecodedBody();

					$message = "Hallo, ".$profile['displayName']."!";
					$this->interactiveTalk($event['replyToken'], $message);
				}
				break;

			case 'news':
				$this->sendNews($event['replyToken']);
				break;
			case 'report':
				$count = count($words);

				if($count === 1 || $words[1] == 'world')
				{
					$this->sendStatistic($event['replyToken'], 'world');
				}
				else
				{
					$this->sendStatistic($event['replyToken'], $words[1]);
				}
				break;
			case 'steps':
				if($words[1] == 'check')
				{
					$this->sendCheck($event['replyToken']);
				}
				else if($words[1] == 'clean' || $words[1] == 'health')
				{
					$this->sendHealth($event['replyToken']);
				}
				else
				{
					$hex = "100010";
					$bin = hex2bin(str_repeat('0', 8-strlen($hex)) . $hex);
					$emoji = mb_convert_encoding($bin, 'UTF-8', 'UTF-32BE');

					$message = "Parameter yang dimasukkan tidak sesuai ".$emoji."\n";
					$message .= "Suggestion:\n";
					$message .= "- steps check: Untuk menampilkan informasi siapa saja yang perlu melakukan pemeriksaan ke rumah sakit terkait COVID-19.\n";
					$message .= "- steps health: Untuk menampilkan informasi bagaimana menjaga kesehatan dan kebersihan agar terhindar dari COVID-19.\n";
					$message .= "\n";
					$message .= "Kirim pesan \"HELP\" untuk menampilkan kata kunci lainnya yang tersedia.";

					$textMessageBuilder = new TextMessageBuilder($message);
					$this->bot->replyMessage($event['replyToken'], $textMessageBuilder);
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
				$hex = "100010";
				$bin = hex2bin(str_repeat('0', 8-strlen($hex)) . $hex);
				$emoji = mb_convert_encoding($bin, 'UTF-8', 'UTF-32BE');

				$message = "Kata kunci tidak ditemukan ".$emoji."\n";
				$message .= "Kirim pesan \"HELP\" untuk menampilkan kata kunci yang tersedia.";

				$textMessageBuilder = new TextMessageBuilder($message);
				$this->bot->replyMessage($event['replyToken'], $textMessageBuilder);
				break;
		}
	}
}
