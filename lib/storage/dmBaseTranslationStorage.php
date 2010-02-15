<?php

abstract class dmBaseTranslationStorage extends dmConfigurable
{
  protected
  $serviceContainer;
  
  public function __construct(dmBaseServiceContainer $serviceContainer, array $options)
  {
    $this->serviceContainer = $serviceContainer;
    
    $this->initialize($options);
  }
  
  protected function initialize(array $options)
  {
    $this->configure($options);
  }
  
  abstract public function save($string, $translated);
  
  
  public function getDefaultOptions()
  {
    return array_merge(parent::getDefaultOptions(), array(
      'culture' => $this->serviceContainer->getParameter('user.culture')
    ));
  }
  
  public function getCulture()
  {
    return $this->getOption('culture');
  }
  
  public function setCulture($culture)
  {
    $this->setOption('culture', $culture);
  }
}