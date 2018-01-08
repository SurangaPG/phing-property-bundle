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
  protected $propertyDir = '';

  /**
   * @param $propertyDir
   */
  public function setPropertyDir($propertyDir) {
    $this->propertyDir = $propertyDir;
  }

  /**
   * @TODO This is a dirty hack to bypass the verbosity of the command.
   *
   * @var FileParserFactoryInterface
   */
  protected $shadowedFileParserFactory;

  /**
   * PropertyDirLoadTask constructor.
   * @param \FileParserFactoryInterface|NULL $fileParserFactory
   */
  public function __construct(\FileParserFactoryInterface $fileParserFactory = NULL) {
    $this->shadowedFileParserFactory = $fileParserFactory != null ? $fileParserFactory : new FileParserFactory();
    parent::__construct($this->shadowedFileParserFactory);
  }

  /**
   * The main entry point method.
   */
  public function main() {

    $files = glob($this->propertyDir . '/*.yml');
    $this->log(sprintf("Auto-loading properties from %s files.", count($files)) , Project::MSG_INFO);

    foreach ($files as $file) {
      $prefix = str_replace('.yml', '', basename($file));
      $this->setPrefix($prefix);
      // Now load the new fully generated file in as properties for the project.
      $file = new PhingFile($file);
      $this->loadFile($file);
    }
  }

  /**
   * load properties from a file.
   * @param PhingFile $file
   * @throws BuildException
   */
  protected function loadFile(PhingFile $file)
  {
    $fileParser = $this->shadowedFileParserFactory->createParser($file->getFileExtension());
    $props = new Properties(null, $fileParser);
    $this->log("Loading " . $file->getAbsolutePath(), Project::MSG_VERBOSE);
    try { // try to load file
      if ($file->exists()) {
        $props->load($file);
        $this->addProperties($props);
      } else {
        $this->log(
          "Unable to find property file: " . $file->getAbsolutePath() . "... skipped",
          Project::MSG_WARN
        );
      }
    } catch (IOException $ioe) {
      throw new BuildException("Could not load properties from file.", $ioe);
    }
  }
}
