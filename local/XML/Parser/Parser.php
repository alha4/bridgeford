<?php

namespace XML\Parser;

abstract class Parser {

  use ParserHelper;

  protected $path;

  private $errors = [];

  public function instance() : Parser {

     return new static();

  }

  public function setPath(string $path) : void {

     if(!file_exists($_SERVER['DOCUMENT_ROOT'].$path)) {

        throw new \Error("Файл $path не существует."); 
     }

     $this->path = $_SERVER['DOCUMENT_ROOT'].$path;

  }

  public function load() : array {

    $data = $this->execute($this->loadXML());
    
    foreach($data as $item) {

      if(!$this->exists($item['ORIGIN_ID'])) {

          $this->save($item);

      }
    }

    return ['status' => 200, 'data' => $data, 'errors' => $this->errors ];

  }

  private function loadXML() : \DOMElement {

    if($dom = \DOMDocument::load($this->path)) {

       return $dom->documentElement;

    }

    throw new Error('error load xml document');

  }

  protected function save(array $entity) : bool {

    $deal = new \CCrmDeal(false);

    if(!$ID = $deal->Add($entity)) {

        $this->errors[] = $deal->LAST_ERROR;

        return false;

    }

    return true;

  }

  protected function exists(int $id) : bool {

    $entity = \CCrmDeal::GetList(['ORIGIN_ID' => 'DESC'], ['ORIGIN_ID' => $id, 'CHECK_PERMISSIONS' => 'Y'], ['ORIGIN_ID'])->Fetch();

    return $entity['ORIGIN_ID'] ? true : false;

  }

  abstract protected function execute(\DOMElement $document) : array;
}

