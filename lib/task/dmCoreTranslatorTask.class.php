<?php

/**
 * Install Diem
 */
class dmCoreTranslatorTask extends dmContextTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    parent::configure();
    
    $this->addArguments(array(
      new sfCommandArgument('culture', sfCommandArgument::REQUIRED, 'The culture to generate')
    ));
    
    $this->namespace = 'dm';
    $this->name = 'translate-core';
    $this->briefDescription = 'Creates translation yml files for given culture';

    $this->detailedDescription = $this->briefDescription;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->checkDuplicated();
    
    $referenceFile = dmOs::join(sfConfig::get('dm_core_dir'), 'data/dm/i18n/en_fr.yml');
    $referenceTranslations = sfYaml::load(file_get_contents($referenceFile));
    
    $translator = $this->get('automatic_translator');
    $translator->setCulture($arguments['culture']);
    
    $storage = $this->get('translation_storage');
    $storage->setCulture($arguments['culture']);
    
    $this->logSection('diem', 'Creating translations in '.dmProject::unRootify($storage->getFile()));
    
    $existingTranslations = file_exists($storage->getFile())
    ? (array) sfYaml::load(file_get_contents($storage->getFile()))
    : array();
    
    foreach($referenceTranslations as $source => $target)
    {
      if (array_key_exists($source, $existingTranslations))
      {
//        $this->logSection('diem', 'skip '.$source);
        continue;
      }
      
      try
      {
        $translated = $translator->translate($source);
      }
      catch(Exception $e)
      {
        $this->logBlock(sprintf('Error while translating "%s" : added an empty translation', $source), 'ERROR');
        $translated = '';
      }
      
      $translated = false === $translated ? '' : $translated;
      
      $storage->save($source, $translated);
      
      $this->logSection('diem translator', sprintf('%s : %s', $source, $translated));
    }
  }
  
  protected function checkDuplicated()
  {
    $files = sfFinder::type('file')->name('*.yml')->in(array(
      dmOs::join(sfconfig::get('dm_core_dir'), 'data/dm/i18n'),
      dmProject::rootify('data/dm/i18n')
    ));
    
    foreach($files as $file)
    {
      $this->logSection('diem', 'Check duplicated sources in '.dmProject::unrootify($file));
      $lines = (array) file($file);
      
      foreach($lines as $lineNumber => $line)
      {
        $source = $this->getLineSource($line);
        $count = 0;
        foreach($lines as $_line)
        {
          if ($source === $this->getLineSource($_line))
          {
            ++$count;
          }
        }
        
        if ($count > 1)
        {
          $this->log(sprintf('Duplicated %s line %d', $source, $lineNumber+1));
        }
      }
    }
  }
  
  protected function getLineSource($line)
  {
    return substr($line, 0, strpos($line, ':'));
  }
  
}