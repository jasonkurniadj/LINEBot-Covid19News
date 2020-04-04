<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Log\Logger;
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
	/**
	* @var Logger
	*/
	private $logger;
	

	public function __construct(
		Request $request,
		Response $response,
		Logger $logger
	) {
		$this->request = $request;
		$this->response = $response;
		$this->logger = $logger;

		// create bot object
		$httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
		$this->bot  = new LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);
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
			$message .= "Silakan kirim pesan \"MULAI\" untuk memulai kuis Tebak Kode.";
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
		$this->bot->replyMessage($replyToken $textMessageBuilder);
	}

	private function sendStatistic($replyToken, $countryCode='IDN')
	{
		// Get Statistic
		if($countryCode == 'world')
		{
			$endpoint = 'https://corona.lmao.ninja/all';

			$message = 'Send World Report from '.$endpoint.' ...';

			$textMessageBuilder = new TextMessageBuilder($message);
			$this->bot->replyMessage($replyToken $textMessageBuilder);
		}
		else
		{
			$endpoint = 'https://corona.lmao.ninja/countries/'.$countryCode;

			$message = 'Send Report from '.$endpoint.' ...';

			$textMessageBuilder = new TextMessageBuilder($message);
			$this->bot->replyMessage($replyToken $textMessageBuilder);
		}
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
		else
		{
			$message = "";
			$message .= "";

			$textMessageBuilder = new TextMessageBuilder($message);
			$this->bot->replyMessage($event['replyToken'], $textMessageBuilder);
		}
	}
}
