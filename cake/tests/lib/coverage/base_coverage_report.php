<?php
/**
 * Abstract class for common CoverageReport methods.
 * Provides several template methods for custom output.
 *
 * PHP5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake
 * @since         CakePHP(tm) v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'DEFAULT');

abstract class BaseCoverageReport {

/**
 * coverage data
 *
 * @var string
 */
	protected $_rawCoverage;

/**
 * is the test an app test
 *
 * @var string
 */
	public $appTest = false;

/**
 * is the test a plugin test
 *
 * @var string
 */
	public $pluginTest = false;

/**
 * is the test a group test?
 *
 * @var string
 */
	public $groupTest = false;

/**
 * Array of test case file names.  Used to do basename() matching with
 * files that have coverage to decide which results to show on page load. 
 *
 * @var array
 */
	protected $_testNames = array();

/**
 * Constructor
 *
 * @param array $coverage Array of coverage data from PHPUnit_Test_Result
 * @param CakeBaseReporter $reporter A reporter to use for the coverage report.
 * @return void
 */
	public function __construct($coverage, CakeBaseReporter $reporter) {
		$this->_rawCoverage = $coverage;
		$this->setParams($reporter);
	}

/**
 * Pulls params out of the reporter.
 *
 * @param CakeBaseReporter $reporter Reporter to suck params out of.
 * @return void
 */
	protected function setParams(CakeBaseReporter $reporter) {
		if ($reporter->params['app']) {
			$this->appTest = true;
		}
		if ($reporter->params['group']) {
			$this->groupTest = true;
		}
		if ($reporter->params['plugin']) {
			$this->pluginTest = Inflector::underscore($reporter->params['plugin']);
		}
	}

/**
 * Set the coverage data array
 *
 * @param array $coverage Coverage data to use.
 * @return void
 */
	public function setCoverage($coverage) {
		$this->_rawCoverage = $coverage;
	}

/**
 * Gets the base path that the files we are interested in live in.
 * If appTest ist
 *
 * @return void
 */
	public function getPathFilter() {
		$path = ROOT . DS;
		if ($this->appTest) {
			$path .= APP_DIR . DS;
		} elseif ($this->pluginTest) {
			$path = App::pluginPath($this->pluginTest);
		} else {
			$path = TEST_CAKE_CORE_INCLUDE_PATH;
		}
		return $path;
	}

/**
 * Filters the coverage data by path.  Files not in the provided path will be removed.
 * This method will merge all the various test run reports as well into a single report per file.
 *
 * @param string $path Path to filter files by.
 * @return array Array of coverage data for files that match the given path.
 */
	public function filterCoverageDataByPath($path) {
		$files = array();
		foreach ($this->_rawCoverage as $testRun) {
			foreach ($testRun['files'] as $filename => $fileCoverage) {
				if (strpos($filename, $path) !== 0) {
					continue;
				}
				$dead = isset($testRun['dead'][$filename]) ? $testRun['dead'][$filename] : array();
				$executable = isset($testRun['executable'][$filename]) ? $testRun['executable'][$filename] : array();
		
				if (!isset($files[$filename])) {
					$files[$filename] = array(
						'covered' => array(),
						'dead' => array(),
						'executable' => array()
					);
				}
				$files[$filename]['covered'] += $fileCoverage;
				$files[$filename]['executable'] += $executable;
				$files[$filename]['dead'] += $dead;
			}
			if (isset($testRun['test'])) {
				$testReflection = new ReflectionClass(get_class($testRun['test']));
				list($fileBasename, $x) = explode('.', basename($testReflection->getFileName()), 2);
				$this->_testNames[] = $fileBasename;
			}
		}
		ksort($files);
		return $files;
	}

/**
 * Generates report to display.
 *
 * @return string compiled html report.
 */
	abstract public function report();

/**
 * Generates an coverage 'diff' for $file based on $coverageData.
 *
 * @param string $filename Name of the file having coverage generated
 * @param array $fileLines File data as an array. See file() for how to get one of these.
 * @param array $coverageData Array of coverage data to use to generate HTML diffs with
 * @return string prepared report for a single file.
 */
	abstract public function generateDiff($filename, $fileLines, $coverageData);

}