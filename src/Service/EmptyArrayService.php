<?php

namespace App\Service;

class EmptyArrayService
{
  public function arrayEmpty($array): bool
  {
    if (!empty($array)) {
      foreach ($array as $key => $valeur) {
        if ($valeur !== null) {
          return false;
        }
      }
    }
    return true;
  }
}
