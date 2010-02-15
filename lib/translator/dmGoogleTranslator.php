<?php

class dmGoogleTranslator extends dmBaseTranslator
{
  public function getDefaultOptions()
  {
    return array_merge(parent::getDefaultOptions(), array(
      'api_url'     => 'http://ajax.googleapis.com/ajax/services/language/translate',
      'api_version' => '1.0'
    ));
  }

  public function translate($string, array $args = array())
  {
    try
    {
      $translated = $this->getTranslation($string);
    }
    catch(dmException $e)
    {
      return false;
    }

    return html_entity_decode($translated);
  }

  public function getTranslation($string)
  {
    $parameters = array(
      'v' => $this->getOption('api_version'),
      'q' => $string,
      'langpair' => 'en|'.$this->getCulture()
    );

    $url  = $this->getOption('api_url') . '?';

    foreach($parameters as $k => $p) {
      $url .= $k . '=' . urlencode($p) . '&';
    }

    if (!@$json = json_decode(file_get_contents($url)))
    {
      throw new dmException("Unable to connect to translation service ".$url);
    }

    switch($json->responseStatus)
    {
      case 200:
        return $this->cleanTranslation($json->responseData->translatedText);
        break;

      default:
        throw new dmException("Unable to perform Translation:".$json->responseStatus);
    }
  }

  protected function cleanTranslation($translated)
  {
    if (false !== strpos($translated, '%'))
    {
      $translated = preg_replace('|% (\d)%|', ' %$1%', $translated);
    }
    
    return $translated;
  }
}