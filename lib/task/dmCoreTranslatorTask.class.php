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
    //$this->checkDuplicated();
    
    $referenceFile = dmOs::join(sfConfig::get('dm_core_dir'), 'data/dm/i18n/en_fr.yml');
    $referenceTranslations = sfYaml::load(file_get_contents($referenceFile));
    
    $translator = $this->get('automatic_translator');
    $translator->setCulture($arguments['culture']);
    
    $storage = $this->get('translation_storage');
    $storage->setCulture($arguments['culture']);

    if(!file_exists($storage->getFile()))
    {
      $coreFile = dmOs::join(sfConfig::get('dm_core_dir'), 'data/dm/i18n/en_'.$arguments['culture'].'.yml');

      if(file_exists($coreFile))
      {
        copy($coreFile, $storage->getFile());
      }
    }

    $existingTranslations = file_exists($storage->getFile())
    ? (array) sfYaml::load(str_replace('#"', '"', file_get_contents($storage->getFile())))
    : array();

    $diff = array_diff_key($referenceTranslations, $existingTranslations);
    $nbDiff = count($diff);

    if(!$nbDiff)
    {
      $this->logBlock($arguments['culture'].' is up to date.', 'INFO_LARGE');
      return;
    }
    else
    {
      $this->logSection('diem translator', 'Generating '.$nbDiff.' missing translations in '.$storage->getFile());
    }

    $it = 1;
    foreach(array_keys($diff) as $source)
    {
      try
      {
        $translated = $translator->translate($source);
      }
      catch(Exception $e)
      {
        $this->logBlock(sprintf('Error while translating "%s": added an empty translation', $source), 'ERROR');
        $translated = '';
      }
      
      $translated = false === $translated ? '' : $translated;
      
      $storage->save($source, $translated);
      
      $this->logSection('diem translator', sprintf('%d/%d %s -> %s', $it++, $nbDiff, $source, $translated));
    }

    $this->logBlock('Please review these '.$nbDiff.' new translations at the end of '.$storage->getFile(), 'INFO_LARGE');
    $this->logBlock('The new translations are commented with a #, remove it to activate them.', 'INFO_LARGE');
  }
  
  protected function checkDuplicated()
  {
    $files = sfFinder::type('file')->name('*.yml')->in(array(
      dmOs::join(sfconfig::get('dm_core_dir'), 'data/dm/i18n'),
      dmProject::rootify('data/dm/i18n')
    ));
    
    foreach($files as $file)
    {
      $this->logSection('diem translator', 'Checking duplicated sources in '.dmProject::unrootify($file));
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
          $this->logBlock(sprintf('Duplicated %s in %s line %d', $source, basename($file), $lineNumber+1), 'COMMENT');
        }
      }
    }
  }
  
  protected function getLineSource($line)
  {
    return substr($line, 0, strpos($line, ':'));
  }
  
}