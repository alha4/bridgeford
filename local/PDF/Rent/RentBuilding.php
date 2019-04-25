<?
namespace PDF\Rent;

use \PDF\PdfExport;

final class RentBuilding extends PdfExport {

  protected $filePrefix = 'rent';

  protected function buildMacros(int $doc_id) : array {

    $select = ["UF_CRM_1540384807664","UF_CRM_1540202889","UF_CRM_1540202817","UF_CRM_1540202908","UF_CRM_1540202900",
              "UF_CRM_1540471471728","UF_CRM_1548410231729","UF_CRM_1540203111","UF_CRM_1543406565","OPPORTUNITY",
              "UF_CRM_1540384944","UF_CRM_1540554743072","UF_CRM_1541056049","UF_CRM_1540384963","UF_CRM_1540371585",
              "UF_CRM_1540371261836","UF_CRM_1540385060","UF_CRM_1540385112","UF_CRM_1540385262",'UF_CRM_1540385040',
              "UF_CRM_1540203015","UF_CRM_1540456473","UF_CRM_1540471409","UF_CRM_1540532330","UF_CRM_1540886934",
              "UF_CRM_1545649289833","UF_CRM_1540532459"];

    $arResult = $this->getData($doc_id, $select);

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
      '#PRICE#'      => $this->getPrice((int)$arResult['UF_CRM_1545649289833'], $arResult['UF_CRM_1540456473']),
      '#SQUARE#'     => $arResult['UF_CRM_1540384944'],
      '#PRICE_1YEAR#'=> $this->getPrice((int)$arResult['UF_CRM_1540554743072'],$arResult['UF_CRM_1540456473']),
      '#INDEXS#'     => $arResult['UF_CRM_1541056049'] ? : 0,
      '#FLOOR#'      => $arResult['UF_CRM_1540384963'],
      '#FLOORS#'     => $arResult['UF_CRM_1540371585'],
      '#BUILDING#'   => $this->enumValue((int)$arResult['UF_CRM_1540371261836'],'UF_CRM_1540371261836'),
      '#CEILING#'    => $arResult['UF_CRM_1540385060'],
      '#CEIL_PREFIX#'=> $this->getCeilingPrefix($arResult['UF_CRM_1540385060']), 
      '#ELECTRIC#'   => $arResult['UF_CRM_1540385112'],
      '#OVERHOUL#'   => $this->enumValue((int)$arResult['UF_CRM_1540385262'],'UF_CRM_1540385262'),
      '#INPUT#'      => self::INPUTTYPE[$arResult["UF_CRM_1540385040"]],
      '#CURRENCY#'   => self::CURRENCY[$arResult['UF_CRM_1540456473']],
      '#DESCRIPTION#' => strip_tags($arResult['UF_CRM_1540471409']),
      '#IMAGES#'      => $this->getImages($arResult['UF_CRM_1540532330']),
      '#EXPLICATION#' => $this->getImages($arResult['UF_CRM_1540532459']),
      '#BROKER_NAME#' => $this->getBroker($arResult['UF_CRM_1540886934'])['FULL_NAME'],
      '#BROKER_PHONE#'=> $this->getBroker($arResult['UF_CRM_1540886934'])['PHONE'],
      '#BROKER_EMAIL#'=> $this->getBroker($arResult['UF_CRM_1540886934'])['EMAIL']

    ];

    if($arFields['#INDEXS#'] == 0 || $arFields['#INDEXS#'] == '0' || !$arFields['#INDEXS#']) {

      $arFields['#INDEXS_DISPLAY#'] = 'none';
      
    }

    return  $arFields;

  }
}