<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Spatie\Crawler\Exceptions\InvalidBaseUrl;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use \DOMDocument;
use App\Dump;

class CrawlerController2 extends Controller
{
    /**
     * Get the list of links to be crawled. Includes the translation mechanism
     * @param Request
     * @return void
     */
	 
	protected $arrResultado;

    public function getLinks(Request $request)
    {
		try {

			$translator = new \Dedicated\GoogleTranslate\Translator;


			ini_set('max_connection_time', 360000000);
			ini_set("memory_limit","2200M");
			
			$baseUrl = base64_decode($request->input('base_url'));
			$queryString = '';
			$url = $baseUrl . $queryString;
			$urls;
			$client = new Client([
						RequestOptions::ALLOW_REDIRECTS => true,
						RequestOptions::COOKIES         => true,
					]);
	
			$response = $client->request('GET', (string) $url, ['ssl.certificate_authority' => true]);
			$domCrawler = new DomCrawler($response->getBody()->getContents());

			$body = $domCrawler->filterXpath('//div[@class="mw-parser-output"]/*');


			$html = '';
			foreach($body as $paragraph){
				if($paragraph->tagName != 'table' && $paragraph->tagName != 'div' && $paragraph->tagName != 'sup'){

					if($paragraph->tagName == 'h3' || $paragraph->tagName == 'h2'){
						for($ni =0; $ni < $paragraph->childNodes->length; $ni++){

							if( $paragraph->childNodes[$ni]->nodeValue !== '[edit]'){
								$html .= '<' . $paragraph->tagName . '>' . $paragraph->childNodes[$ni]->nodeValue . '</' . $paragraph->tagName . '>';
							}
						}
					} else {
						$html .= $paragraph->ownerDocument->saveHTML($paragraph);
					}
				}
			}
			
			/*Removing stuff we don't want... */
			$sup = $domCrawler->filterXpath('//sup');
			foreach($sup as $s){
				$html = str_replace($s->ownerDocument->saveHTML($s), '', $html);
			}
			
			$domCrawlerEdt = new DomCrawler($html);
			$edt = $domCrawler->filterXpath('//h2/span[@class="mw-editsection"]/span/a');
			foreach($edt as $e){
				//dump($e->ownerDocument->saveHTML($e));
				$html = str_replace($e->ownerDocument->saveHTML($e), '', $html);
			}

			$edt = $domCrawler->filterXpath('//h2/span[@class="mw-editsection"]/a');
			foreach($edt as $e){
				//dump($e->ownerDocument->saveHTML($e));
				$html = str_replace($e->ownerDocument->saveHTML($e), '', $html);
			}


			$pgs = $domCrawler->filterXpath('//div[@class="mw-parser-output"]/p/a');
			$listUrl = array();
			$i = 0;

			foreach($pgs as $pg){
				dump($pg);
				$i++;
			}

			$html = str_replace('<span class="mw-editsection-bracket">[</span>', '', $html);
			$html = str_replace('<span class="mw-editsection-bracket">]</span>', '', $html);
			$html = str_replace('<span> </span>', '', $html);
			$html = str_replace('<span></span>', '', $html);

			$html = str_replace('href=', '=!1!', $html);
			$html = str_replace('title=', '=!2!', $html);
			$html = str_replace('/wiki/', '!|!', $html);


			$prhases = explode('.', $html);

			$resultHtml = '';
			/* Beginning translation... */
			foreach($prhases as $ph){
				$prhases2 = explode("\n", $ph);
				foreach($prhases2 as $ph2){
					$resultHtml .= 	$translator->setSourceLang('en')
						                     ->setTargetLang('it')
						                     ->translate($ph2);
				}
				$resultHtml .= '.';
			}

			$html = str_replace('=!1!', 'href=', $resultHtml);
			$html = str_replace('=!2!', 'title=', $html);
			$html = str_replace('!|!', '/artigos/', $html);
			$html = str_replace(' ,', ',', $html);

			print($html);
		}
		catch(Exception $e){
			print(json_encode($listUrl));
		}
	}

}
