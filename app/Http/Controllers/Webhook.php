<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
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

		// create bot object
		$httpClient = new CurlHTTPClient('7BsGfbfyE/7uq9NX+mFjXndUEnF2p9le1F2srRWQRgh8MDMIUtKsiYZ7K4v2GKX0Twvi9Z8ipzdck1n2MXtX1BIrKoaZOmNtlB3HcRCKaZk6Fe4a3AuG5n/QHGrpyWoxvWVj6WEpuRqguN+DZaFq4wdB04t89/1O/w1cDnyilFU=');
		$this->bot  = new LINEBot($httpClient, ['channelSecret' => '1dfbde6acc6a7dcfdcedc082b63c0de7']);
	}

	private function handleEvents()
	{
		$data = $this->request->all();

		if(is_array($data['events']))
		{
			foreach ($data['events'] as $event)
			{
				// respond event
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

			// create welcome message
			$message  = "Salam kenal, " . $profile['displayName'] . "!\n";
			$message .= "Silakan kirim pesan \"HELP\" untuk melihat kata kunci yang dapat digunakan.";
			$textMessageBuilder = new TextMessageBuilder($message);

			// create sticker message
			$stickerMessageBuilder = new StickerMessageBuilder(1, 3);

			// merge all message
			$multiMessageBuilder = new MultiMessageBuilder();
			$multiMessageBuilder->add($textMessageBuilder);
			$multiMessageBuilder->add($stickerMessageBuilder);

			// send reply message
			$this->bot->replyMessage($event['replyToken'], $multiMessageBuilder);
		}
	}

	private function sendNews($replyToken)
	{
		// Get news from WHO
		$endpoint = 'https://www.who.int/rss-feeds/news-english.xml';

		$message = 'Send News from '.$endpoint.' ...';

		$textMessageBuilder = new TextMessageBuilder($message);
		$this->bot->replyMessage($replyToken, $textMessageBuilder);
	}

	private function sendStatistic($replyToken, $countryCode='IDN')
	{
		// Get Statistic
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

	private function sendAbout($replyToken)
	{
		$message = "";
		$message .= "Chatbot ini ditujukan untuk membantu masyarakat mempermudah memperoleh informasi mengenai COVID-19.\n";
		$message .= "Informasi yang dapat ditampilkan dari chatbot ini adalah berita mengenai COVID-19 dari WHO, laporan jumlah kasus COVID-19 di seluruh dunia ataupun negara yang diinginkan.\n";
		$message .= "\n";
		$message .= "Semoga dengan adanya chatbot ini, masyarakat menjadi semakin teredukasi, terhindar dari hoax, serta dapat mengurangi persebaran COVID-19.\n";
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
		$message .= "- news                    Untuk menampilkan berita terkini dari WHO.\n";
		$message .= "- report world            Untuk menampilkan rangkuman laporan dari data seluruh dunia.\n";
		$message .= "- report [country_code]   Untuk menampilkan rangkuman laporan dari kode negara yang dimasukkan, misal \"report IDN\".\n";
		$message .= "- about                   Untuk menampilkan informasi mengenai chatbot ini.\n";

		$textMessageBuilder = new TextMessageBuilder($message);
		$this->bot->replyMessage($replyToken, $textMessageBuilder);
	}

	private function textMessage($event)
	{
		$userMessage = $event['message']['text'];
		$userMessage = strtolower($userMessage);

		$words = explode(' ', trim($userMessage));

		if($words[0] == 'news')
		{
			$this->sendQuestion($event['replyToken']);
		}
		else if($words[0] == 'report')
		{
			if($words[1] == 'world')
			{
				$this->sendStatistic($event['replyToken'], 'world');
			}
			else
			{
				$this->sendStatistic($event['replyToken'], $words[1]);
			}
		}
		else if($words[0] == 'about')
		{
			$this->sendAbout($event['replyToken']);
		}
		else if($words[0] == 'help')
		{
			$this->sendHelp($event['replyToken']);
		}
		else
		{
			$message = "Kata kunci tidak ditemukan :(\n";
			$message .= "Kirim pesan \"HELP\" untuk menampilkan kata kunci yang tersedia.";

			$textMessageBuilder = new TextMessageBuilder($message);
			$this->bot->replyMessage($event['replyToken'], $textMessageBuilder);
		}
	}
}
