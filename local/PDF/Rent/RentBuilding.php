<?
namespace PDF\Rent;

use \PDF\PdfExport;

final class RentBuilding extends PdfExport {

  use \PDF\Helpers\PdfHelper;

  protected $filePrefix = 'rent';

  protected $templatePath = __DIR__;

  private const RENT_TYPE = [

     '0' => 'Открытая аренда',
     '1' => 'Закрытая аренда'

  ];

  private const INPUTTYPE = [

    "96"  =>  "общий",
    "95"  =>  "отдельный"

  ];

  private const CURRENCY =  [

    "144" => 'Руб.',
    "145" => 'Usd',
    "146" => 'Eur'

  ];

  protected function buildMacros(int $doc_id) : array {

    $sort = ["ID" => "DESC"];

    $filter = ["CHECK_PERMISSIONS" => "N", "ID" => $doc_id];

    $select = ["UF_CRM_1540384807664","UF_CRM_1540202889","UF_CRM_1540202817","UF_CRM_1540202908","UF_CRM_1540202900",
              "UF_CRM_1540471471728","UF_CRM_1548410231729","UF_CRM_1540203111","UF_CRM_1543406565","OPPORTUNITY",
              "UF_CRM_1540384944","UF_CRM_1540554743072","UF_CRM_1541056049","UF_CRM_1540384963","UF_CRM_1540371585",
              "UF_CRM_1540371261836","UF_CRM_1540385060","UF_CRM_1540385112","UF_CRM_1540385262",'UF_CRM_1540385040',
              "UF_CRM_1540203015","UF_CRM_1540456473"];

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
      '#METRO_TIME#' =>  $this->enumValue((int)$arResult['UF_CRM_1540203015'],'UF_CRM_1540203015'),
      '#PRICE#'      => $arResult['OPPORTUNITY'],
      '#SQUARE#'     => $arResult['UF_CRM_1540384944'],
      '#PRICE_1YEAR#' => $arResult['UF_CRM_1540554743072'],
      '#INDEXS#'     => $arResult['UF_CRM_1541056049'],
      '#FLOOR#'      => $arResult['UF_CRM_1540384963'],
      '#FLOORS#'     => $arResult['UF_CRM_1540371585'],
      '#BUILDING#'   => $this->enumValue((int)$arResult['UF_CRM_1540371261836'],'UF_CRM_1540371261836'),
      '#CEILING#'    => $arResult['UF_CRM_1540385060'],
      '#ELECTRIC#'   => $arResult['UF_CRM_1540385112'],
      '#OVERHOUL#'   => $this->enumValue((int)$arResult['UF_CRM_1540385262'],'UF_CRM_1540385262'),
      '#INPUT#'      => self::INPUTTYPE[$arResult["UF_CRM_1540385040"]],
      '#CURRENCY#'   => self::CURRENCY[$arResult['UF_CRM_1540456473']]

    ];

    return  $arFields;

  }

  private function getLocationMap(int $file_id) : string {

    $file = \CFile::GetFileArray($file_id);
  
    return sprintf("%s", $file['SRC']);
 
  }

  private function getAddress(array $row) : string {

    if($row['UF_CRM_1540202889'] == self::STREET_TYPE) {

        return sprintf("Россия, %s, %s %s",$row['UF_CRM_1540202817'], $row['UF_CRM_1540202900'], $row['UF_CRM_1540202908']);

    }

    return sprintf("Россия, %s, %s-й %s %s",$row['UF_CRM_1540202817'], $row['UF_CRM_1540202908'], $this->enumValue((int)$row['UF_CRM_1540202889'],'UF_CRM_1540202889'), $row['UF_CRM_1540202900']);

  }

}