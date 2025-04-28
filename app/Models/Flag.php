<?php

namespace app\Models;

class Flag
{
  public $nome;

  public $bandeiraPng;
  public $dificuldade;
  public function  __construct($nome, $bandeiraPng, $dificuldade){

      $this->nome = $nome;
      $this->bandeiraPng = $bandeiraPng;
      $this->dificuldade = $dificuldade;

  }

    public static function loadAllFlags($jsonPath)
    {
        $jsonData = json_decode(file_get_contents($jsonPath), true);
        $flags = [];

        foreach ($jsonData as $difficulty => $countries) {
            foreach ($countries as $country) {
                $flags[] = new self($country['name'], $country['flag'], $difficulty);
            }
        }

        return $flags;
    }

    public static function getFlagsByDifficulty($jsonPath, $difficulty)
    {
        $jsonData = json_decode(file_get_contents($jsonPath), true);
        $flags = [];

        if (isset($jsonData[$difficulty])) {
            foreach ($jsonData[$difficulty] as $country) {
                $flags[] = new self($country['name'], $country['flag'], $difficulty);
            }
        }

        return $flags;
    }



}
