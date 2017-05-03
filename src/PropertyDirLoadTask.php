<?php
/**
 * @file
 */


require_once "phing/Task.php";
include_once 'phing/system/util/Properties.php';
include_once 'phing/system/io/FileParserFactoryInterface.php';
include_once 'phing/system/io/FileParserFactory.php';

use Symfony\Component\Yaml\Yaml;

class PropertyDirLoadTask extends PropertyTask {

  /**
   * Origin folder passed in the buildfile.
   */
  private $propertyDir = '';

  /**
   * @param $propertyDir
   */
  public function setPropertyDir($propertyDir) {
    $this->propertyDir = $propertyDir;
  }

  /**
   * The main entry point method.
   */
  public function main() {

    $files = glob($this->propertyDir . '/*.yml');

    foreach ($files as $file) {

      $prefix = str_replace('.yml', '', basename($file));
      $this->setPrefix($prefix);
      // Now load the new fully generated file in as properties for the project.
      $file = new PhingFile($file);
      $this->loadFile($file);
    }
  }
}
