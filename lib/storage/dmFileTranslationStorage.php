<?php

class dmFileTranslationStorage extends dmBaseTranslationStorage
{
  protected
  $filesystem,
  $file;
  
  protected function initialize(array $options)
  {
    parent::initialize($options);
    
    $this->filesystem = $this->serviceContainer->getService('filesystem');

    if(!$this->filesystem->mkdir(dmProject::rootify('data/dm/i18n')))
    {
      throw new dmException(dmProject::rootify('data/dm/i18n').' is not writable');
    }
  }
  
  public function getFile()
  {
    return dmProject::rootify(sprintf('data/dm/i18n/en_%s.yml', $this->getCulture()));
  }
  
  public function save($string, $translated)
  {
    $translations = $this->getTranslations();
    
    if (!isset($translations[$string]))
    {
      $this->saveTranslation($string, $translated);
    }
  }
  
  protected function getTranslations()
  {
    if (!file_exists($this->getFile()))
    {
      if (!$this->filesystem->mkdir(dirname($this->getFile())))
      {
        throw new dmException('Can not mkdir '.dirname($this->getFile()));
      }
      if (!$this->filesystem->touch($this->getFile()))
      {
        throw new dmException('Can not touch '.$this->getFile());
      }
    }
    
    return (array) sfYaml::load(file_get_contents($this->getFile()));
  }
  
  protected function saveTranslation($string, $translated)
  {
    $string     = sprintf('"%s"', str_replace('"', '\\"', $string));
    $translated = sprintf('"%s"', str_replace('"', '\\"', $translated));
    $line       = sprintf("\n#%s: %s", $string, $translated);
      
    if($fp = fopen($this->getFile(), 'a'))
    {
      fwrite($fp, $line);
      fclose($fp);
    }
    else
    {
      throw new dmException(sprintf('Can not save translation in %s', $this->getFile()));
    }
  }
}