<?php
/**
 * Magento filesystem facade
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Filesystem
{
    const DIRECTORY_SEPARATOR = '/';

    /**
     * @var Magento_Filesystem_AdapterInterface
     */
    protected $_adapter;

    /**
     * @var string
     */
    protected $_workingDirectory;

    /**
     * @var bool
     */
    protected $_isAllowCreateDirs = false;

    /**
     * @var int
     */
    protected $_newDirPermissions = 0777;

    /**
     * Initialize adapter and default working directory.
     *
     * @param Magento_Filesystem_AdapterInterface $adapter
     */
    public function __construct(Magento_Filesystem_AdapterInterface $adapter)
    {
        $this->_adapter = $adapter;
        $this->_workingDirectory = self::getAbsolutePath(self::getPathFromArray(array(__DIR__, '..', '..')));
    }

    /**
     * Sets working directory to restrict operations with filesystem.
     *
     * @param string $dir
     * @return Magento_Filesystem
     * @throws InvalidArgumentException
     */
    public function setWorkingDirectory($dir)
    {
        $dir = self::getAbsolutePath($dir);
        if (!$this->_adapter->isDirectory($dir)) {
            throw new InvalidArgumentException(sprintf('Working directory "%s" does not exists', $dir));
        }
        $this->_workingDirectory = $dir;
        return $this;
    }

    /**
     * Get current working directory.
     *
     * @return string
     */
    public function getWorkingDirectory()
    {
        return $this->_workingDirectory;
    }

    /**
     * Allows to create directories when process operations
     *
     * @param bool $allow
     * @param int $permissions
     * @return Magento_Filesystem
     */
    public function setIsAllowCreateDirectories($allow, $permissions = null)
    {
        $this->_isAllowCreateDirs = (bool)$allow;
        if (null !== $permissions) {
            $this->_newDirPermissions = $permissions;
        }
        return $this;
    }

    /**
     * Checks the file existence.
     *
     * @param string $key
     * @param string|null $workingDirectory
     * @return bool
     */
    public function has($key, $workingDirectory = null)
    {
        return $this->_adapter->exists($this->_getCheckedPath($key, $workingDirectory));
    }

    /**
     * Reads content of the file.
     *
     * @param string $key
     * @param string|null $workingDirectory
     * @return string
     */
    public function read($key, $workingDirectory = null)
    {
        $path = $this->_getCheckedPath($key, $workingDirectory);
        $this->_checkFileExists($path);
        return $this->_adapter->read($path);
    }

    /**
     * Writes content into the file.
     *
     * @param string $key
     * @param string $content
     * @param string|null $workingDirectory
     * @return int The number of bytes that were written.
     */
    public function write($key, $content, $workingDirectory = null)
    {
        $path = $this->_getCheckedPath($key, $workingDirectory);
        $this->ensureDirectoryExists(dirname($path));
        return $this->_adapter->write($path, $content);
    }

    /**
     * Deletes the key.
     *
     * @param string $key
     * @param string|null $workingDirectory
     * @return bool
     */
    public function delete($key, $workingDirectory = null)
    {
        $path = $this->_getCheckedPath($key, $workingDirectory);
        return $this->_adapter->delete($path);
    }

    /**
     * Renames the file.
     *
     * @param string $source
     * @param string $target
     * @param string|null $workingDirectory
     * @param string|null $targetDirectory
     * @return bool
     */
    public function rename($source, $target, $workingDirectory = null, $targetDirectory = null)
    {
        if ($workingDirectory && null === $targetDirectory) {
            $targetDirectory = $workingDirectory;
        }
        $sourcePath = $this->_getCheckedPath($source, $workingDirectory);
        $targetPath = $this->_getCheckedPath($target, $targetDirectory);
        $this->_checkExists($sourcePath);
        $this->ensureDirectoryExists(dirname($targetPath), $this->_newDirPermissions, $targetDirectory);
        return $this->_adapter->rename($sourcePath, $targetPath);
    }

    /**
     * Copy the file.
     *
     * @param string $source
     * @param string $target
     * @param string|null $workingDirectory
     * @param string|null $targetDirectory
     * @return bool
     */
    public function copy($source, $target, $workingDirectory = null, $targetDirectory = null)
    {
        if ($workingDirectory && null === $targetDirectory) {
            $targetDirectory = $workingDirectory;
        }
        $sourcePath = $this->_getCheckedPath($source, $workingDirectory);
        $targetPath = $this->_getCheckedPath($target, $targetDirectory);
        $this->_checkFileExists($sourcePath);
        $this->ensureDirectoryExists(dirname($targetPath), $this->_newDirPermissions, $targetDirectory);
        return $this->_adapter->copy($sourcePath, $targetPath);
    }

    /**
     * Check if key is directory.
     *
     * @param string $key
     * @param string|null $workingDirectory
     * @return bool
     */
    public function isDirectory($key, $workingDirectory = null)
    {
        return $this->_adapter->isDirectory($this->_getCheckedPath($key, $workingDirectory));
    }

    /**
     * Check if key is file.
     *
     * @param string $key
     * @param string|null $workingDirectory
     * @return bool
     */
    public function isFile($key, $workingDirectory = null)
    {
        return $this->_adapter->isFile($this->_getCheckedPath($key, $workingDirectory));
    }

    /**
     * Check if key exists and is writable
     *
     * @param string $key
     * @param string|null $workingDirectory
     * @return bool
     */
    public function isWritable($key, $workingDirectory = null)
    {
        return $this->_adapter->isWritable($this->_getCheckedPath($key, $workingDirectory));
    }

    /**
     * Check if key exists and is readable
     *
     * @param string $key
     * @param string|null $workingDirectory
     * @return bool
     */
    public function isReadable($key, $workingDirectory = null)
    {
        return $this->_adapter->isReadable($this->_getCheckedPath($key, $workingDirectory));
    }

    /**
     * Change permissions of key
     *
     * @param string $key
     * @param int $permissions
     * @param bool $recursively
     * @param string|null $workingDirectory
     */
    public function changePermissions($key, $permissions, $recursively = false, $workingDirectory = null)
    {
        $this->_adapter->changePermissions($this->_getCheckedPath($key, $workingDirectory), $permissions, $recursively);
    }

    /**
     * Gets list of all nested keys
     *
     * @param string $key
     * @param string|null $workingDirectory
     * @return array
     */
    public function getNestedKeys($key, $workingDirectory = null)
    {
        return $this->_adapter->getNestedKeys($this->_getCheckedPath($key, $workingDirectory));
    }

    /**
     * Gets list of all matched keys
     *
     * @param string $baseDirectory
     * @param string $pattern
     * @return array
     */
    public function searchKeys($baseDirectory, $pattern)
    {
        $baseDirectory = $this->_getCheckedPath($baseDirectory);
        $this->_checkPathInWorkingDirectory(
            rtrim($baseDirectory, self::DIRECTORY_SEPARATOR)
            . self::DIRECTORY_SEPARATOR
            . ltrim($pattern, self::DIRECTORY_SEPARATOR)
        );
        return $this->_adapter->searchKeys(
            rtrim($baseDirectory, self::DIRECTORY_SEPARATOR)
            . self::DIRECTORY_SEPARATOR
            . ltrim(self::fixSeparator($pattern), self::DIRECTORY_SEPARATOR)
        );
    }

    /**
     * Creates new directory
     *
     * @param string $key
     * @param int $permissions
     * @param string|null $workingDirectory
     */
    public function createDirectory($key, $permissions = 0777, $workingDirectory = null)
    {
        $path = $this->_getCheckedPath($key, $workingDirectory);
        $parentPath = dirname($path);
        if (!$this->isDirectory($parentPath)) {
            $this->createDirectory($parentPath, $permissions, $workingDirectory);
        }
        $this->_adapter->createDirectory($path, $permissions);
    }

    /**
     * Create directory if it does not exists.
     *
     * @param string $key
     * @param int $permissions
     * @param string|null $workingDirectory
     * @throws Magento_Filesystem_Exception
     */
    public function ensureDirectoryExists($key, $permissions = 0777, $workingDirectory = null)
    {
        if (!$this->isDirectory($key, $workingDirectory)) {
            if ($this->_isAllowCreateDirs) {
                $this->createDirectory($key, $permissions, $workingDirectory);
            } else {
                throw new Magento_Filesystem_Exception("Directory '$key' doesn't exist.");
            }
        }
    }

    /**
     * Sets access and modification time of file.
     *
     * @param string $key
     * @param int|null $fileModificationTime
     * @param string|null $workingDirectory
     */
    public function touch($key, $fileModificationTime = null, $workingDirectory = null)
    {
        $key = $this->_getCheckedPath($key, $workingDirectory);
        $this->ensureDirectoryExists(dirname($key), $this->_newDirPermissions);
        $this->_adapter->touch($key, $fileModificationTime);
    }

    /**
     * Get file modification time.
     *
     * @param string $key
     * @param string|null $workingDirectory
     * @return int
     */
    public function getMTime($key, $workingDirectory = null)
    {
        $key = $this->_getCheckedPath($key, $workingDirectory);
        $this->_checkExists($key);
        return $this->_adapter->getMTime($key);
    }

    /**
     * Get file size.
     *
     * @param string $key
     * @param string|null $workingDirectory
     * @return int
     */
    public function getFileSize($key, $workingDirectory = null)
    {
        $key = $this->_getCheckedPath($key, $workingDirectory);
        $this->_checkFileExists($key);
        return $this->_adapter->getFileSize($key);
    }

    /**
     * Creates stream object
     *
     * @param string $key
     * @param string|null $workingDirectory
     * @return Magento_Filesystem_StreamInterface
     * @throws Magento_Filesystem_Exception
     */
    public function createStream($key, $workingDirectory = null)
    {
        $key = $this->_getCheckedPath($key, $workingDirectory);
        if ($this->_adapter instanceof Magento_Filesystem_Stream_FactoryInterface) {
            return $this->_adapter->createStream($key);
        } else {
            throw new Magento_Filesystem_Exception("Filesystem doesn't support streams.");
        }
    }

    /**
     * Creates stream object and opens it
     *
     * @param string $key
     * @param Magento_Filesystem_Stream_Mode|string $mode
     * @param string|null $workingDirectory
     * @return Magento_Filesystem_StreamInterface
     * @throws InvalidArgumentException
     */
    public function createAndOpenStream($key, $mode, $workingDirectory = null)
    {
        $stream = $this->createStream($key, $workingDirectory);
        if (!$mode instanceof Magento_Filesystem_Stream_Mode && !is_string($mode)) {
            throw new InvalidArgumentException('Wrong mode parameter');
        }
        $stream->open($mode);
        return $stream;
    }

    /**
     * Calculates the md5 hash of a given file
     *
     * @param string $key
     * @param string $workingDirectory
     * @return string
     */
    public function getFileMd5($key, $workingDirectory = null)
    {
        $key = $this->_getCheckedPath($key, $workingDirectory);
        $this->_checkFileExists($key);
        return $this->_adapter->getFileMd5($key);
    }

    /**
     * Check that file exists
     *
     * @param string $path
     * @throws InvalidArgumentException
     */
    protected function _checkFileExists($path)
    {
        if (!$this->_adapter->isFile($path)) {
            throw new InvalidArgumentException(sprintf('"%s" does not exists', $path));
        }
    }

    /**
     * Check that file or directory exists
     *
     * @param string $path
     * @throws InvalidArgumentException
     */
    protected function _checkExists($path)
    {
        if (!$this->_adapter->exists($path)) {
            throw new InvalidArgumentException(sprintf('"%s" does not exists', $path));
        }
    }

    /**
     * Get absolute checked path
     *
     * @param string $key
     * @param string|null $workingDirectory
     * @return string
     */
    protected function _getCheckedPath($key, $workingDirectory = null)
    {
        $this->_checkPathInWorkingDirectory($key, $workingDirectory);
        return self::getAbsolutePath($key);
    }

    /**
     * Asserts path in working directory
     *
     * @param string $key
     * @param string|null $workingDirectory
     * @return string
     * @throws InvalidArgumentException
     */
    protected function _checkPathInWorkingDirectory($key, $workingDirectory = null)
    {
        $workingDirectory = $workingDirectory ? $workingDirectory : $this->_workingDirectory;
        if (!self::isPathInDirectory($key, $workingDirectory)) {
            throw new InvalidArgumentException("Path '$key' is out of working directory '$workingDirectory'");
        }
    }

    /**
     * Get absolute path.
     *
     * @param string $key
     * @return string
     */
    public static function getAbsolutePath($key)
    {
        $path = self::getPathAsArray($key);
        $realPath = array();
        foreach ($path as $dir) {
            if ('.' == $dir) {
                continue;
            }
            if ('..' == $dir) {
                if (count($realPath) > 0) {
                    array_pop($realPath);
                }
                continue;
            }
            $realPath[] = $dir;
        }
        return self::getPathFromArray($realPath);
    }

    /**
     * Get path as string from array.
     *
     * @param array $path
     * @param bool $isAbsolute
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getPathFromArray(array $path, $isAbsolute = true)
    {
        $path = array_map(array('Magento_Filesystem', 'fixSeparator'), $path);
        if (!count($path)) {
            throw new InvalidArgumentException('Path must contain at least one node');
        }
        $isWindows = preg_match('/\w:/', $path[0]);
        $path = implode(self::DIRECTORY_SEPARATOR, $path);
        if ($isAbsolute && !$isWindows) {
            return self::DIRECTORY_SEPARATOR . ltrim($path, self::DIRECTORY_SEPARATOR);
        } else {
            return $path;
        }
    }

    /**
     * Get path as array
     *
     * @param string $path
     * @return array
     */
    public static function getPathAsArray($path)
    {
        $path = self::fixSeparator($path);
        return explode(self::DIRECTORY_SEPARATOR, ltrim($path, self::DIRECTORY_SEPARATOR));
    }

    /**
     * Update directory separator
     *
     * @static
     * @param string $path
     * @return string
     */
    public static function fixSeparator($path)
    {
        return rtrim(str_replace('\\', self::DIRECTORY_SEPARATOR, $path), self::DIRECTORY_SEPARATOR);
    }

    /**
     * Check is path absolute
     *
     * @param string $path
     * @return bool
     */
    public static function isAbsolutePath($path)
    {
        return self::fixSeparator($path) == self::getAbsolutePath($path);
    }

    /**
     * Checks is directory contains path
     *
     * @param string $path
     * @param string $directory
     * @return bool
     */
    public static function isPathInDirectory($path, $directory)
    {
        return 0 === strpos(self::getAbsolutePath($path), self::getAbsolutePath($directory));
    }
}
