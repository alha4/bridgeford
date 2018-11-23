<?php
namespace Cian;

use \Bitrix\Main\Web\HttpClient,
    \Cian\CrmObject;

final class CianPriceMonitoring {

  private const CIAN_API_URL = 'https://api.cian.ru/search-offers/v2/search-offers-desktop/';

  private const CIAN_GEOCODER_SEARCH_URL = 'https://www.cian.ru/api/geo/geocoded-for-search';

  private const CIAN_REGION_SEARCH_URL = 'https://www.cian.ru/cian-api/site/v1/search-regions-cities/?text=#CITY#';

  private const CIAN_GEO_SEARCH_URL = 'https://www.cian.ru/api/geo/geocode-cached?request=#REGION#';

  private const CIAN_SUGGEST_URL = 'https://api.cian.ru/geo-suggest/v1/suggest-railways/?query=#Q#&regionId=1&offerType=flat';

  private const SUCCESS = 'ok';

  private const DEFAULT_CITY = 'Москва';

  private const SQUARE_PRECENT = 15;

  private const GEO_DATA_LENGTH = 4;

  private $http_headers = ["Host"    => "api.cian.ru",
                           "Origin"  => "https://www.cian.ru",
                           "Referer" => "https://www.cian.ru/cat.php?deal_type=sale&engine_version=2&offer_type=flat&region=1&room1=1&room2=1",
                           "Content-Type" => "application/json",
                           "User-Agent"   => "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36"
                          ];


  private const CIAN_SEARCH_TYPE = [
      '0' => 'flatrent',
      '1' => 'flatsale',
      '2' => 'flatrent'
  ];

 public static function instance() : CianPriceMonitoring {

   return new static();

 }

 public function run(?int $object_id = 0) : void {

   $objects = CrmObject::getAll( $object_id );

   if($objects) {

     foreach($objects as $object) {

     $geodecode = $this->getGeocodedAdress($object);

     if($geodecode) {
   
        $geodecode['CATEGORY_ID'] = $object['CATEGORY_ID'];

        $request  = $this->buildRequest($geodecode);

        $response = $this->offers($request);

        if($response['offersSerialized']) {
         
           $price = $object['MAIN_ANCHOR'] > 0 ? $object['MAIN_ANCHOR'] : $this->getMinPrice($response);

           $competitors = $this->getOffersList($response['offersSerialized']);

           if(!CrmObject::setCompetitors($object['ID'], $competitors)) {

              //
           }

           if($price > 0) {

             $price_step = $object['PRICE_STEP'];
           
             if(CrmObject::setPrice($object['ID'], $price, $price_step)) {
           
               echo json_encode(['ID' => $object['ID'], 'status' => 'цена обновлена'], JSON_UNESCAPED_UNICODE);


             } else {

               echo json_encode(['произошла ошибка'], JSON_UNESCAPED_UNICODE);

             }
          }
        } else {
  
          echo json_encode(['нет данных',$response],JSON_UNESCAPED_UNICODE);

        }
      }
     }
   }
 }

 /**
  *@param array $data - параметры объекта площадь, город, улица, дом
  *
  *@return array $result - массив ключей запроса для ЦИАН
  *
  */
 private function buildRequest(array $data) : array {

  $square_gte = round($data['SQUARE'] - (($data['SQUARE']) / 100 * self::SQUARE_PRECENT));

  $search_type = self::CIAN_SEARCH_TYPE[$data['CATEGORY_ID']];

  $result = ['jsonQuery' => [
           '_type' => $search_type,
           'engine_version' => [
             'type' => 'term',
             'value' => 2,
           ],
           'room' => [

            'type' => 'term',
            'value' => [1,2]

           ],
           'geo' =>  [
            'type' => 'geo',
            'value' => [
              [
              'type' => 'house',
              'id'  => $data['HOUSE']
              ]
            ]
           ],
           'region' => [

            'type' => 'term',
            'value' => [$data['CITY']],

           ],
           'total_area' => [
              'type' => 'range',
              'value' => ['gte' => $square_gte,
                          'lte' => $data['SQUARE']]
           ]
        ]];

        if($search_type == 'flatrent') {

            $result['jsonQuery']['for_day'] = [

              'type' => 'term',
              'value' => '!1'
  
            ];

        }

   return $result;

 }

 public function getGeocodedAdress(array $address) : ?array {

  if($address['IS_DECODED'] == 'Y') {

     return  [
      'STREET' => $address['STREET'],
      'HOUSE'  => $address['HOUSE'],
      'CITY'   => $address['CITY'],
      'SQUARE' => $address['SQUARE']
     ];

  }

  $full_address = $this->coordinateAdressString($address);

  $boundedBy = $this->coordinate($full_address);

  if($boundedBy) {

   $data = [
     "Lng" => $boundedBy['lng'],
     "Lat" => $boundedBy['lat'],
     "Kind" => "house",
     "Address" => $this->houseAdressString($address)
   ];

   $geoData = $this->geocoded($data);

   if($geoData) {

    if(count($geoData) >= self::GEO_DATA_LENGTH) {
     
     return  [
      'STREET' => $geoData[1]['id'],
      'HOUSE'  => $geoData[3]['id'],
      'CITY'   => $geoData[0]['id'],
      'SQUARE' => $address['SQUARE']
     ];
    
    }

    return  [
      'STREET' => $geoData[1]['id'],
      'HOUSE'  => $geoData[2]['id'],
      'CITY'   => $geoData[0]['id'],
      'SQUARE' => $address['SQUARE']
     ];

   }

   return false;

  }

  return false;

 }

 private function offers(array $data) : array {
  
  $http_client = $this->httpClient();

  $response = json_decode( $http_client->post(self::CIAN_API_URL, json_encode($data)), 1);

  if($response['status'] == self::SUCCESS) {

     return $response['data'];

  }

  return ['RESPONSE' => $response, 'DATA' =>  json_encode($data) /*,'HEADERS' => $http_client->getHeaders()*/];

 }

 private function coordinate(string $search) : ?array {

  $http_client = $this->httpClient();

  $url = str_replace('#REGION#', urlencode($search), self::CIAN_GEO_SEARCH_URL);

  $response = json_decode( $http_client->get($url), 1);

  if($response['items']) {

     return ['lng' => $response['items'][0]['coordinates'][0], 
             'lat' => $response['items'][0]['coordinates'][1]];

  }

  return null;

 }

 private function geocoded(array $data) : ?array {

  $http_client = $this->httpClient();

  $response = json_decode( $http_client->post(self::CIAN_GEOCODER_SEARCH_URL, json_encode($data)), 1);

  if($response['isParsed'] == 1) {

     return $response['details'];

  }

  return null;

 }

 private function coordinateAdressString(array $address) : string {

  if(strlen($address['CITY']) > 0 && $address['CITY'] != self::DEFAULT_CITY) {

    if($this->isProspekt($address['STREET'])) {
  
      return  "Россия, Москва, {$address['STREET']}, {$address['HOUSE']}";

    }
  
    return "Россия, Москва, {$address['CITY']}, {$address['STREET']} улица, {$address['HOUSE']}";

  }


  if($this->isProspekt($address['STREET'])) {
  
    return  "Россия, Москва, {$address['STREET']}, {$address['HOUSE']}";

  }

  return "Россия, Москва, {$address['STREET']} улица, {$address['HOUSE']}";

 }

 private function houseAdressString(array $address) : string {

  if(strlen($address['CITY']) > 0 && $address['CITY'] != self::DEFAULT_CITY) {

    if($this->isProspekt($address['STREET'])) {
  
      return  "Россия, Москва, {$address['STREET']}, {$address['HOUSE']}";

    }

    return  "Россия, Москва, {$address['CITY']}, {$address['HOUSE']}-я {$address['STREET']} улица";

  }

  if($this->isProspekt($address['STREET'])) {
  
    return  "Россия, Москва, {$address['STREET']}, {$address['HOUSE']}";

  }

  return  "Россия, Москва, {$address['HOUSE']}-я {$address['STREET']} улица";

 }

 private function isProspekt(string $street) : bool {

    return strpos($street, 'проспект') ? : false;

 }

 private function getMinPrice(array $data) : float {

  $prices = array_column($data['offersSerialized'], 'bargainTerms');
  $prices = array_column($prices, 'price');
  $prices = array_unique($prices);
  asort($prices);
 
  return array_shift($prices);

 }

 private function getOffersList(array $data) : array {

    $result = [];

    foreach($data as $item) {

      $result[] = [
         'TITLE' => $item['geo']['userInput'],
         'PRICE' => $item['bargainTerms']['price']
      ];

    }

    return $result;
    
 }

 private function httpClient() {

  $http_client = new HttpClient();

  foreach($this->http_headers as $name=>$value) {

    $http_client->setHeader($name, $value);

  }

  return $http_client;

 }
                          
 private function __construct() {}
 private function __clone(){}
}





