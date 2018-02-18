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
use \setasign\fpdi\Fpdi as FPDI;

class CrawlerPCIController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
	 
	protected $arrLinks;
	protected $arrSubLinks;

    public function getLinks(Request $request)
    {
		try {

			ini_set('max_connection_time', 360000000);
			ini_set("memory_limit","2200M");

			$client = new Client([
						RequestOptions::ALLOW_REDIRECTS => true,
						RequestOptions::COOKIES         => true,
					]);

			$url = base64_decode($request->input('base_url'));

			dump($url);
	
			$response = $client->request('GET', (string) $url, ['ssl.certificate_authority' => true]);

			$domCrawler = new DomCrawler($response->getBody()->getContents());

			$details = $domCrawler->filterXpath("//ul[@class='alf']");

			for($i = 0; $i < $details->getNode(0)->childNodes->length; $i++){
				if  (
						$details->getNode(0)->childNodes[$i]->nodeName == 'li' &&
						$details->getNode(0)->childNodes[$i]->nodeValue != ':: Mais Acessadas ::'
					){
					//dump($details->getNode(0)->childNodes[$i]->childNodes[0]->getAttribute('href'));

					$arrLinks[] = $details->getNode(0)->childNodes[$i]->childNodes[0]->getAttribute('href');

					$responseIndex   = 	$client->request('GET',
											(string) $details->getNode(0)->childNodes[$i]->childNodes[0]->getAttribute('href'), 
											['ssl.certificate_authority' => true]
										);

					$domCrawlerIndex = new DomCrawler($responseIndex->getBody()->getContents());

					$pagesIndex      = $domCrawlerIndex->filterXpath("//span[@id='prova_pagina']");

					if($pagesIndex->count() > 0){
						$numPages = (int)explode('de',explode('página',$pagesIndex->getNode(0)->nodeValue)[1])[1];

						//dump($details->getNode(0)->childNodes[$i]->childNodes[0]->getAttribute('href') . ' -> ' . $numPages);
						
						for($page = 1; $page < $numPages; $page++){
							//dump($page+1);
							$arrLinks[] = $details->getNode(0)->childNodes[$i]->childNodes[0]->getAttribute('href') . '/' . ($page+1);
						}
					}

				}
			} //For de todas as letras do índice

			//dd($arrLinks);

			for($l = 0; $l < count($arrLinks); $l++){
				//dump($arrLinks[$l]);

				$responseSubIndex = $client->request('GET', (string) $arrLinks[$l], ['ssl.certificate_authority' => true]);

				$domCrawlerSubIndex = new DomCrawler($responseSubIndex->getBody()->getContents());

				$linksSubIndex = $domCrawlerSubIndex->filterXpath("//td[@class='ca']");

				//dd($linksSubIndex);
				foreach($linksSubIndex as $link){
					$arrSubLinks[] = $link->childNodes[0]->getAttribute('href');

					//dump($link->childNodes[0]->getAttribute('href'));
					$this->crawlIt($link->childNodes[0]->getAttribute('href'));
				}
				dd($arrSubLinks);

			}

			dump(count($arrSubLinks));

			//$this->crawlIt('https://www.pciconcursos.com.br/provas/download/a-c-d-do-programa-de-saude-bucal-prefeitura-barauna-rn-concsel-2009');

		}
		catch(Exception $e){
			print(json_encode($listUrl));
		}
	}

    public function crawlIt($url = null)
    {
		try {

			$client = new Client([
						RequestOptions::ALLOW_REDIRECTS => true,
						RequestOptions::COOKIES         => true,
					]);

			$url = ($url == null ? base64_decode('aHR0cHM6Ly93d3cucGNpY29uY3Vyc29zLmNvbS5ici9wcm92YXMvZG93bmxvYWQvYS1jLWQtZG8tcHJvZ3JhbWEtZGUtc2F1ZGUtYnVjYWwtcHJlZmVpdHVyYS1iYXJhdW5hLXJuLWNvbmNzZWwtMjAwOQ==')  : $url);
	
			$response = $client->request('GET', (string) $url, ['ssl.certificate_authority' => true]);

			$domCrawler = new DomCrawler($response->getBody()->getContents());
			//pega as informações do corpo do artigo
			$body = $domCrawler->filterXpath('//h2');

			$details = $domCrawler->filterXpath("//ul[@class='linkd']");

			dump($body->getNode(0)->childNodes[0]->nodeValue);

			for($i = 0; $i < $details->getNode(0)->childNodes->length; $i++){
				dump($details->getNode(0)->childNodes[$i]->nodeValue);
			}


			$details = $domCrawler->filterXpath("//ul[@class='pdf']");

			dump($body->getNode(0)->childNodes[0]->nodeValue);

			for($i = 0; $i < $details->getNode(0)->childNodes->length; $i++){
				dump($details->getNode(0)->childNodes[$i]->childNodes[0]->getAttribute('href'));

				$responsePDF = $client->request('GET', (string) $details->getNode(0)->childNodes[$i]->childNodes[0]->getAttribute('href'), ['ssl.certificate_authority' => true]);
			
				\Storage::disk('local')->put($details->getNode(0)->childNodes[$i]->nodeValue, $responsePDF->getBody()->getContents());

				dump($details->getNode(0)->childNodes[$i]->nodeValue);
				$file = glob('../storage/app/' . $details->getNode(0)->childNodes[$i]->nodeValue);

				$fpdf = new \setasign\Fpdi\Fpdi();

				


				// add a page
				
				// set the source file
				$pagecount = $fpdf->setSourceFile('../storage/app/' . $details->getNode(0)->childNodes[$i]->nodeValue);

				for($pgPdf = 1; $pgPdf <= $pagecount; $pgPdf++){
					$fpdf->AddPage();
					// import page 1
					$tplIdx = $fpdf->importPage($pgPdf);
					// use the imported page and place it at position 10,10 with a width of 100 mm
					$fpdf->useTemplate($tplIdx, 10, 13, 190, 295);

					// now write some text above the imported page
					$fpdf->SetFont('Helvetica', 'B', 16);
					$fpdf->SetTextColor(255, 0, 0);
					
					$fpdf->Write(0, '                                    EDITAL CONCURSOS BRASIL');

					//Go to 1.5 cm from bottom
					//$fpdf->SetY(-15);
					//Select Arial italic 8
					//$fpdf->SetFont('Arial','I',8);
					//Print centered cell with a text in it
					//$fpdf->Cell(0, 10, "Edital Concursos Brasil", 0, 0, 'C');
				}


				$fpdf->setCompression(true);
				$fpdf->Output('../storage/app/' . $details->getNode(0)->childNodes[$i]->nodeValue, "F");

				\Zipper::make('../storage/app/' . str_replace('.pdf', '', $details->getNode(0)->childNodes[$i]->nodeValue) . '.zip')->add($file)->close();
			}
		}
		catch(Exception $e){
			print(json_encode($listUrl));
		}
	}

}
