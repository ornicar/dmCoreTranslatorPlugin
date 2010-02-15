<?php

class dmCoreTranslatorHandler extends dmConfigurable
{
  protected
  $serviceContainer;
  
  public function __construct(dmBaseServiceContainer $serviceContainer, array $options)
  {
    $this->serviceContainer = $serviceContainer;
    
    $this->initialize($options);
  }
  
  public function getDefaultOptions()
  {
    return array_merge(parent::getDefaultOptions(), array(
      'enabled'               => false,
      'save_new_translations' => true
    ));
  }
  
  protected function initialize(array $options)
  {
    $this->configure($options);
  }
  
  public function connect()
  {
    if ($this->getOption('enabled'))
    {
      $this->serviceContainer
      ->getService('dispatcher')
      ->connect('dm.i18n.not_found', array($this, 'listenToI18nNotFoundEvent'));
    }
  }
  
  public function listenToI18nNotFoundEvent(sfEvent $e)
  {
    $translated = $this->serviceContainer->getService('automatic_translator')->translate(
      $e['source'],
      $e['args']
    );
    
    if ($translated)
    {
      if ($this->getOption('save_new_translations'))
      {
        $this->saveNewTranslation($e['source'], $e['args'], $translated);
      }
      
      $e->setReturnValue($translated);
      
      return true;
    }
    
    return false;
  }
  
  protected function saveNewTranslation($string, array $args, $translated)
  {
    $this->serviceContainer->getService('translation_storage')->save($string, $translated);
  }
}