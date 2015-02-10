<?php

namespace Obullo\Filesystem;

/**
 * Filesystem Class
 * 
 * File/Folder management
 * 
 * @category  Filesystem
 * @package   Filesystem
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/filesystem
 */
class Filesystem
{
	const FILE_PERMISSION = 0777;

	/**
     * Directory seperator
     * 
     * @var constant
     */
	protected $separator = DIRECTORY_SEPARATOR;

	/**
     * Absolute path
     * 
     * @var string
     */
	protected $absPath;

	/**
     * Splitted to the absolute path
     * 
     * @var array
     */
	protected $splittedPath;

	/**
     * Operations to be performed on the file
     * 
     * @var string
     */
	protected $lastSegment;

	/**
     * Full path but without performed on the file
     * 
     * @var string
     */
	protected $withoutEndSegment;

	/**
     * it will a create a new file
     *
     * @param  $text string
     * @param  $shouldBe bool
     * @return bool
     */
	public function write($text = '', $shouldBe = false)
	{
		if ($path = $this->getAbsPath()) {
			if ( ! file_exists($path) && $shouldBe === true) {
				if($this->createTree() && $this->createFile() && $this->writeToFile($text)) {
					return true;
				}
			}

			else if (file_exists($path) && $this->writeToFile($text)) {
				return true;
			}
		}

		return false;
	}

	/**
     * Append the data to the file end
     *
     * @param  $text string
     * @return bool
     */
	public function append($text = '')
	{
		if ($path = $this->getAbsPath()) {
			if (file_exists($path) && is_writable($path) && $this->writeToFile($text, true)) {
				return true;
			}
		}

		return false;
	}

	/**
     * Data processing and return class instance
     *
     * @param  $path string
     * @return object
     */
	public function get($path = false)
	{
		if ($path) {
			$file = $this->replaceSep($path);
			$this->splittedPath = $this->splitPath($file);
			$this->absPath = $this->setAbsPath($this->splittedPath, true);
			$this->lastSegment = end($this->splittedPath);
		}

		return $this;
	}

	/**
     * Gets the file contents
     *
     * @param  $path string
     * @return string|null
     */
	public function read($path = false)
	{
		if( ! filter_var($path, FILTER_VALIDATE_URL)) {
			if ($path !== false) $this->get($path);

			$path = $this->getAbsPath();

			if (filetype($path) != 'dir' && file_exists($path)) {
				return file_get_contents($path);
			}
		}
	}

	/**
     * Delete file and folder
     *
     * @param  $path string
     * @return bool
     */
	public function delete($path = false)
	{
		if ($path !== false) $this->get($path);

		if ($path = $this->getAbsPath()) {
			if (file_exists($path) && filetype($path) != 'dir') {
				return @unlink($path);
			}else{
				return @rmdir($path);
			}
		}

		return false;
	}

	/**
     * File move to target path
     *
     * @param  $target string
     * @return bool
     */
	public function move($target = false)
	{
		$path = $this->getAbsPath();

		if ($target && file_exists($path) && $path <> $target && $this->createTree($target)) {

			$targetPath = $target . $this->separator . $this->lastSegment;
			$resultMove = @rename($path, $targetPath);

			if ($resultMove) {
				$this->get($targetPath);
				return true;
			}
		}

		return false;
	}

	/**
     * Change the file name
     *
     * @param  $name string
     * @return bool
     */
	public function rename($name = false)
	{
		if ($name && $this->getAbsPath()) {
			$newName = implode($this->getWithoutEndSegment(), '/');
			$this->lastSegment = $name;
			$this->move($newName);
		}

		return false;
	}

	/**
     * Target path is a array of separate
     *
     * @param  $string string
     * @return array
     */
	public function splitPath($string = null)
	{
		return preg_split('/\//', $string, -1, PREG_SPLIT_NO_EMPTY);
	}

	/**
     * File replaces the separator
     *
     * @param  $item string
     * @return string
     */
	protected function replaceSep($item = null)
	{
		return preg_replace('/\\\\/', $this->separator, $item);
	}

	/**
     * Sets absolute path
     *
     * @param $splitted array
     * @param $return bool
     * @return string
     */
	protected function setAbsPath(array $splitted, $return = false)
	{
		$this->absPath = implode($splitted, $this->separator);

		if ($return) {
			return $this->getAbsPath();
		}
	}

	/**
     * Get absolute path
     *
     * @return string
     */
	protected function getAbsPath()
	{
		return !is_null($this->absPath) ? $this->absPath : false;
	}

	protected function getLastSegment($path)
	{
		$file = $this->replaceSep($path);
		$splitted = $this->splitPath($file);
		return end($splitted);
	}

	/**
     * Creates all paths
     *
     * @param  $path string
     * @return string
     */
	protected function createTree($path = false)
	{
		$tree = !$path ? $this->getWithoutEndSegment() : $this->splitPath($path);

		$createdTree = '';
		$mkResult = true;

		if (count($tree) > 0) {
			foreach($tree as $segment) {
				$createdTree .= $createdTree == '' ? $segment : $this->separator . $segment;

				if(! file_exists($createdTree)) {
					$mkResult = mkdir($createdTree, self::FILE_PERMISSION, true);
				}
			}
		}

		return $mkResult;
	}

	/**
     * Creates file
     *
     * @return string
     */
	protected function createFile()
	{
		$root = implode($this->getWithoutEndSegment(), $this->separator);

		if(empty($root)) {
			return touch($this->lastSegment);
		}

		else if(file_exists($root) && is_writable($root)) {
			return touch($this->getAbsPath());
		}

		return false;
	}

	/**
     * File writes data
     *
     * @param  $text string
     * @param  $append bool
     * @return bool
     */
	protected function writeToFile($text, $append = false)
	{
		if ($append) {
			return file_put_contents($this->getAbsPath(), $text, FILE_APPEND | LOCK_EX);
		}

		return file_put_contents($this->getAbsPath(), $text, LOCK_EX);
	}

	/**
     * Gets full path without last segment
     *
     * @return string
     */
	protected function getWithoutEndSegment()
	{
		$splitted = $this->splittedPath;
		array_splice($splitted, count($this->splittedPath) - 1);

		return $splitted;
	}
}