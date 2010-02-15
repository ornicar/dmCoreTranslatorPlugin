<?php

class dmNoTranslator extends dmBaseTranslator
{
  
  public function translate($string, array $args = array())
  {
    return $string;
  }
  
}