<?
namespace PDF\Rent;

use \PDF\PdfExport;

final class RentBuilding extends PdfExport {

  use \PDF\Helpers\PdfHelper;

  protected $filePrefix = 'rent';

  protected $templatePath = __DIR__;

  private const RENT_TYPE = [

     '0' => ' ',
     '1' => 'закрытая продажа,'

  ];

  private const INPUTTYPE = [

    "96"  =>  "общий",
    "95"  =>  "отдельный"

  ];

  private const CURRENCY =  [

    "144" => 'руб.',
    "145" => 'usd',
    "146" => 'eur'

  ];

  private const CURRENCY_CODE =  [

    "144" => 'RUB',
    "145" => 'USD',
    "146" => 'EUR'

  ];


  protected function buildMacros(int $doc_id) : array {

    $sort = ["ID" => "DESC"];

    $filter = ["CHECK_PERMISSIONS" => "N", "ID" => $doc_id];

    $select = ["UF_CRM_1540384807664","UF_CRM_1540202889","UF_CRM_1540202817","UF_CRM_1540202908","UF_CRM_1540202900",
              "UF_CRM_1540471471728","UF_CRM_1548410231729","UF_CRM_1540203111","UF_CRM_1543406565","OPPORTUNITY",
              "UF_CRM_1540384944","UF_CRM_1540554743072","UF_CRM_1541056049","UF_CRM_1540384963","UF_CRM_1540371585",
              "UF_CRM_1540371261836","UF_CRM_1540385060","UF_CRM_1540385112","UF_CRM_1540385262",'UF_CRM_1540385040',
              "UF_CRM_1540203015","UF_CRM_1540456473","UF_CRM_1540471409","UF_CRM_1540532330","UF_CRM_1540886934"];

    $object = \CCrmDeal::GetList($sort, $filter, $select);

    $arResult = $object->Fetch();

    $category_id = \CCrmDeal::GetCategoryID($doc_id);

    $arFields = [

      '#LOT#'        => $arResult['ID'],
      '#RENT_TYPE#'  => self::RENT_TYPE[$arResult['UF_CRM_1540471471728']],
      '#TYPE#'       => self::OBJECT_TYPE[$category_id],
      '#BUILD_TYPE#' => $this->enumValue((int)$arResult['UF_CRM_1540384807664'], 'UF_CRM_1540384807664'),
      '#ADDRESS#'    => $this->getAddress($arResult),
      '#ADDR#'       => $this->getAddress($arResult),
      '#MAP#'        => $this->getLocationMap((int)$arResult['UF_CRM_1548410231729']),
      '#AREA#'       => $this->enumValue((int)$arResult['UF_CRM_1540203111'],'UF_CRM_1540203111'),
      '#METRO#'      => $this->IblockEnumValue($arResult['UF_CRM_1543406565']),
      '#METRO_TIME#' => $this->enumValue((int)$arResult['UF_CRM_1540203015'],'UF_CRM_1540203015'),
      '#PRICE#'      => $this->getPrice((int)$arResult['OPPORTUNITY'], $arResult['UF_CRM_1540456473']),
      '#SQUARE#'     => $arResult['UF_CRM_1540384944'],
      '#PRICE_1YEAR#'=> $this->getPrice((int)$arResult['UF_CRM_1540554743072'],$arResult['UF_CRM_1540456473']),
      '#INDEXS#'     => $arResult['UF_CRM_1541056049'] ? : 0,
      '#FLOOR#'      => $arResult['UF_CRM_1540384963'],
      '#FLOORS#'     => $arResult['UF_CRM_1540371585'],
      '#BUILDING#'   => $this->enumValue((int)$arResult['UF_CRM_1540371261836'],'UF_CRM_1540371261836'),
      '#CEILING#'    => $arResult['UF_CRM_1540385060'],
      '#CEIL_PREFIX#' => $this->getCeilingPrefix($arResult['UF_CRM_1540385060']), 
      '#ELECTRIC#'   => $arResult['UF_CRM_1540385112'],
      '#OVERHOUL#'   => $this->enumValue((int)$arResult['UF_CRM_1540385262'],'UF_CRM_1540385262'),
      '#INPUT#'      => self::INPUTTYPE[$arResult["UF_CRM_1540385040"]],
      '#CURRENCY#'   => self::CURRENCY[$arResult['UF_CRM_1540456473']],
      '#DESCRIPTION#' => $arResult['UF_CRM_1540471409'],
      '#IMAGES#'      => $this->getImages($arResult['UF_CRM_1540532330']),
      '#BROKER_NAME#' => $this->getBroker($arResult['UF_CRM_1540886934'])['FULL_NAME'],
      '#BROKER_PHONE#'=> $this->getBroker($arResult['UF_CRM_1540886934'])['PHONE'],
      '#BROKER_EMAIL#'=> $this->getBroker($arResult['UF_CRM_1540886934'])['EMAIL']

    ];

    return  $arFields;

  }

  private function getBroker(int $ID) : array {

    $user = \CUser::GetList($sort = 'ID', $order = 'desc', ['ID' => $ID], ['SECECT' => ['NAME','LAST_NAME','EMAIL','PERSONAL_PHONE'] ]);

    $arUser = $user->Fetch();

    return [

       'FULL_NAME' => sprintf("%s %s", $arUser['NAME'], $arUser['LAST_NAME']),
       'EMAIL' => $arUser['EMAIL'],
       'PHONE' => $arUser['PERSONAL_PHONE']

    ];
  }

  private function getPrice(int $price, string $currency) : string {


    return \SaleFormatCurrency($price, self::CURRENCY_CODE[$currency]);

  }

  private function getImages(array $data) : string {

      $html_img = '';

      foreach($data as $k=>$file_id) {


        $html_img.= sprintf("%s<td class='obj_img'><img src='%s' width='310' height='210'>", ($k % 2 == 0  ?  "<tr>" : '')  ,\CFile::GetPath($file_id));


      }

      return $html_img;

  }

  private function getLocationMap(int $file_id) : string {

    $file = \CFile::GetFileArray($file_id);
  
    return sprintf("%s", $file['SRC']);
 
  }

  private function getCeilingPrefix(int $value) : string {

    if($value < 2) {

        return 'р';

    }
    
    return $value % 2 == 0 ? 'ра' : 'ров';

  }

  private function getAddress(array $row) : string {

    if($row['UF_CRM_1540202889'] == self::STREET_TYPE) {

        return sprintf("Россия, %s, %s %s",$row['UF_CRM_1540202817'], $row['UF_CRM_1540202900'], $row['UF_CRM_1540202908']);

    }

    return sprintf("Россия, %s, %s %s %s",$row['UF_CRM_1540202817'], $row['UF_CRM_1540202900'], $this->enumValue((int)$row['UF_CRM_1540202889'],'UF_CRM_1540202889'), $row['UF_CRM_1540202908']);

  }

}