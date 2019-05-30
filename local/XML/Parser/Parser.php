<?php

namespace XML\Parser;

abstract class Parser {

  use ParserHelper;

  protected $path;

  protected $errors = [];

  private $xpath = '/offers/offer[%d]/following-sibling::offer';

  private $isXpathMode = false;

  private function __construct(?int $offset) {

    if(!is_null($offset) && is_int($offset)) {

      $this->isXpathMode = true;
      $this->xpath = sprintf($this->xpath, $offset);

    }

  }

  public function instance(?string $xpath = '') : Parser {

     return new static($xpath);

  }

  public function setPath(string $path) : void {

     if(!file_exists($_SERVER['DOCUMENT_ROOT'].$path)) {

        throw new \Error("Файл $path не существует."); 
     }

     $this->path = $_SERVER['DOCUMENT_ROOT'].$path;

  }

  private function getPath() : string {

    return basename($this->path);

  }

  public function load() : array {

    $runStart = new \DateTime();

    $data = $this->execute($this->loadXML());

    if(\SAVE_MODE == 'Y') {
    
      foreach($data as $item) {

        if(!$this->exists($item['ORIGIN_ID'])) {

          $id = $this->save($item);

          if($id) {

             $item['ID'] = $id;

             $this->fireEvent($item);

           }
         } else {

             $this->errors[] = sprintf("offer internal-id=%d, has exists.",$item['ORIGIN_ID']);

         }
       }
    }

    $runEnd = new \DateTime();

    $runTime = $runStart->diff($runEnd, true);

    return ['status' => 200, 'offers' => count($data), 'file' => $this->getPath(), 'xpath' => $this->xpath, 'errors' => $this->errors, 'time' => sprintf("%s min. : %s sec.", $runTime->i, $runTime->s),'memory' => round( memory_get_usage() / 1024 / 1024, 2)." МБ." ];

  }

  private function loadXML() : \DOMElement {

    if($dom = \DOMDocument::load($this->path)) {

      if($this->isXpathMode) {

         $xdom = new \DOMXpath($dom);

         unset($dom);

         $childs = $xdom->query($this->xpath); 

         $virtualDom = new \DomDocument("1.0","utf-8");

         $offers = $virtualDom->createElement('offers');

         foreach($childs as $k=>$node) {
          
            $offers->appendChild($virtualDom->importNode($node, true));

         }

         $root = $virtualDom->appendChild($offers);

         return $virtualDom->documentElement;

      }

      return $dom->documentElement;

    }

    throw new \Error('error load xml \DOMDocument');

  }

  protected function save(array $entity) {

    $deal = new \CCrmDeal(false);

    if(!$ID = $deal->Add($entity)) {

        $this->errors[] = ['internal_id' => $entity['ORIGIN_ID'] , 'error' =>  $deal->LAST_ERROR ];

        return false;

    }

    return $ID;

  }

  protected function exists(int $id) : bool {

    $entity = \CCrmDeal::GetList(['ORIGIN_ID' => 'DESC'], ['ORIGIN_ID' => $id, 'CHECK_PERMISSIONS' => 'Y'], ['ORIGIN_ID'])->Fetch();

    return $entity['ORIGIN_ID'] ? true : false;

  }


  protected function fireEvent(array &$event) : bool {

    return true;

  }

  abstract protected function execute(\DOMElement $document) : array;
}

