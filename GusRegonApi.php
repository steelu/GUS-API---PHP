<?php

namespace App\Services;

use JsonException;
use function curl_setopt;
use function json_decode;
use function simplexml_load_string;
use function strlen;
use const CURLOPT_HTTPHEADER;
use const JSON_THROW_ON_ERROR;

class GusRegonApi
{
    //adresy produkcyjne
    protected $loginUrl = 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/UslugaBIRzewnPubl.svc/ajaxEndpoint/Zaloguj';
    protected $searchDataUrl = 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/UslugaBIRzewnPubl.svc/ajaxEndpoint/DaneSzukajPodmioty';

    private $key = null;

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }
    private $session = null;

    /**
     * @param string $key
     * @throws JsonException
     */


    protected function makeCurl($field, $url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $field);
        curl_setopt($curl, CURLOPT_ENCODING, 'UTF-8');

        $head[] = 'Accept: application/json';
        $head[] = 'Content-Type: application/json';
        $head[] = 'Content-Length: ' . strlen($field);
        $head[] = 'sid:' . $this->session;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $head);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.114 Safari/537.36');
        curl_setopt($curl, CURLOPT_HEADER, false);
        $result = curl_exec($curl);
        curl_close($curl);
        if ($this->session === null) {
            return json_decode($result, false, 512, JSON_THROW_ON_ERROR)->d;
        } else {
            return simplexml_load_string(json_decode($result, false, 512, JSON_THROW_ON_ERROR)->d);
        }
    }

    protected function login()
    {
        $login = json_encode(["pKluczUzytkownika" => $this->key]);
        $result = $this->makeCurl($login, $this->loginUrl);
        return $result;
    }

    public function checkNip($nip)
    {

        if ($this->session === null) {
            $this->session = $this->login();
        }


        $searchData = json_encode([
            'jestWojPowGmnMiej' => true,
            'pParametryWyszukiwania' => [
                'AdsSymbolGminy' => null,
                'AdsSymbolMiejscowosci' => null,
                'AdsSymbolPowiatu' => null,
                'AdsSymbolUlicy' => null,
                'AdsSymbolWojewodztwa' => null,
                'Dzialalnosci' => null,
                'FormaPrawna' => null,
                'Krs' => null,
                'Krsy' => null,
                'NazwaPodmiotu' => null,
                'Nip' => $nip,
                'Nipy' => null,
                'NumerwRejestrzeLubEwidencji' => null,
                'OrganRejestrowy' => null,
                'PrzewazajacePKD' => false,
                'Regon' => null,
                'Regony14zn' => null,
                'Regony9zn' => null,
                'RodzajRejestru' => null,
            ]
        ], JSON_THROW_ON_ERROR);

        return $this->makeCurl($searchData, $this->searchDataUrl);
    }


}
