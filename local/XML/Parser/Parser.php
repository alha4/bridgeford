<?php

namespace XML\Parser;

abstract class Parser {

  use ParserHelper;

  protected $path;

  protected $errors = [];

  public function instance() : Parser {

     return new static();

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
         }
       }
    }

    $runEnd = new \DateTime();

    $runTime = $runStart->diff($runEnd, true);

    return ['status' => 200, 'offers' => count($data), 'file' => $this->getPath(), 'errors' => $this->errors, 'time' => sprintf("%s:%s sec.", $runTime->i, $runTime->s),'memory' => round( memory_get_usage() / 1024 / 1024, 2)." МБ." ];

  }

  private function loadXML() : \DOMElement {

    if($dom = \DOMDocument::load($this->path)) {

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

