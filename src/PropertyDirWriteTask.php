<?php
/**
 * @file
 */


require_once "phing/Task.php";
include_once 'phing/system/util/Properties.php';
include_once 'phing/system/io/FileParserFactoryInterface.php';
include_once 'phing/system/io/FileParserFactory.php';

use Symfony\Component\Yaml\Yaml;

class PropertyDirWriteTask extends PropertyTask {

  /**
   * Origin folder passed in the buildfile.
   */
  private $originDir = '';

  /**
   * @var string
   */
  private $subLevels = '';

  /**
   * @var string
   */
  private $order = '';

  /**
   * The folder where all the data is written to.
   * @var string
   */
  private $outputDir;

  /**
   * Should a full output be written, .yml files with all the values
   * replaced.
   * @var bool
   */
  private $outputFull = false;

  /**
   * @param $outputFull
   */
  public function setOutputFull($outputFull) {
    $this->outputFull = $outputFull;
  }

  /**
   * @param $originDir
   */
  public function setOriginDir($originDir) {
    $this->originDir = $originDir;
  }

  /**
   * @param $subLevels
   */
  public function setSubLevels($subLevels) {
    $this->subLevels = $subLevels;
  }

  /**
   * @param $order
   */
  public function setOrder($order) {
    $this->order = $order;
  }

  /**
   * @param $setOutputDir
   */
  public function setOutputDir($setOutputDir) {
    $this->outputDir = $setOutputDir;
  }

  /**
   * The main entry point method.
   */
  public function main() {

    $orderedFiles = $this->getSublevelFileList();
    $this->orderFileList($orderedFiles);

    // Write out all the data in a compressed fashion.
    // e.g overwrite the settings of lower levels with that of higher levels.
    $this->loadCalculatedFiles($orderedFiles);

    // Write out full files with all the values replaced for other
    // tools etc to use implementations.
    if ($this->outputFull) {
      $this->writeFullPropertyFiles($orderedFiles);
    }

    // Clean up all the temp files.
    $this->cleanUpTempFiles($orderedFiles);
  }

  /**
   * @param $orderedFiles
   */
  protected function cleanUpTempFiles($orderedFiles) {
    foreach (array_keys($orderedFiles) as $type) {
      unlink($this->generateTempFilename($type));
    }
  }

  /**
   * @param $orderedFiles
   */
  protected function writeFullPropertyFiles($orderedFiles) {
    foreach ($orderedFiles as $type => $levels) {
      $calculatedFileName = $this->generateTempFilename($type);
      $file = new PhingFile($calculatedFileName);

      $contents = '';

      $this->filterChains = [];
      $reader = FileUtils::getChainedReader(new FileReader($file), $this->filterChains, $this->project);
      while (-1 !== ($buffer = $reader->read())) {
        $contents .= $buffer;
      }
      $reader->close();

      $tempConfigName = base64_encode('__' . $type);

      $this->getProject()->setNewProperty($tempConfigName, $contents);
      $calculatedFileName = $this->outputDir .  '/' . $type . '.yml';
      $handle = fopen($calculatedFileName, "w");
      fwrite($handle, $this->getProject()->getProperty($tempConfigName));
      fclose($handle);

      $this->getProject()->setProperty($tempConfigName, null);
    }
  }

  /**
   * Writes out the data of all the calculated files to temporary files with
   * a full complement of data.
   *
   * @param $orderedFiles
   */
  protected function loadCalculatedFiles($orderedFiles) {

    // Import all the values.
    foreach ($orderedFiles as $type => $levels) {
      $this->log(sprintf("Loading properties for %s.yml (found: %s)", $type, implode('>', array_keys($levels))) , Project::MSG_INFO);
      $this->setPrefix($type);

      // Overwrite the data to generate a proper aggregated file.
      $data = [];
      foreach ($levels as $level => $fileName) {
        $extraData = Yaml::parse(file_get_contents($fileName));
        $extraData = isset($extraData) ? $extraData : [];
        $data = array_replace_recursive($data, $extraData);
      }

      // Write out the data to the calculated dir
      $tempFileName = $this->generateTempFilename($type);
      $handle = fopen($tempFileName, "w");
      fwrite($handle, Yaml::dump($data, 5));
      fclose($handle);

      // Now load the new fully generated file in as properties for the project.
      $file = new PhingFile($tempFileName);
      $this->loadFile($file);
    }
  }

  /**
   * @param $type
   * @return string
   */
  protected function generateTempFilename($type) {
    return $this->outputDir . '__' . base64_encode($type) . '.yml';
  }

  /**
   * Generates a list of all the files to load.
   * Based on the correct sub levels and ordered in the correct order.
   *
   * @param $fileList
   */
  protected function orderFileList(&$fileList) {
    $allFiles = $fileList;

    // Order the files so that the passed order is respected.
    $fileList = explode(',', $this->order);
    $fileList = array_fill_keys($fileList, null);

    $fileList = array_merge($fileList, $allFiles);
    $fileList = array_filter($fileList);
  }

  /**
   * @return array
   */
  protected function getSublevelFileList() {

    $fileList = [];

    // Get all the different file locations.
    // This allows for the overwriting of config files on different levels.
    $subLevels = explode(',', $this->subLevels);

    foreach ($subLevels as $subLevel) {
      $files = glob($this->originDir . '/' . $subLevel . '/*.yml');

      foreach ($files as $file) {
        $fileList[str_replace('.yml', '', basename($file))][$subLevel] = $file;
      }
    }

    return $fileList;
  }
}
