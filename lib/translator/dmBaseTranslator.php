<?php

abstract class dmBaseTranslator extends dmConfigurable
{
  protected
  $serviceContainer;
  
  public function __construct(dmBaseServiceContainer $serviceContainer, array $options = array())
  {
    $this->serviceContainer = $serviceContainer;
    
    $this->initialize($options);
  }
  
  public function getDefaultOptions()
  {
    return array_merge(parent::getDefaultOptions(), array(
      'culture' => $this->serviceContainer->getParameter('user.culture')
    ));
  }
  
  protected function initialize(array $options)
  {
    $this->configure($options);
  }
  
  abstract public function translate($string, array $args = array());

  public function getCulture()
  {
    return $this->getOption('culture');
  }
  
  public function setCulture($culture)
  {
    return $this->setOption('culture', $culture);
  }
}