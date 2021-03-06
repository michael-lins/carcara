<?php
/**
 * Carcara (http://carcara.candango.org)
 *
 * @link      http://github.com/candango/carcara
 * @copyright Copyright (c) 2018 Flavio Garcia
 * @license   https://www.apache.org/licenses/LICENSE-2.0  Apache-2.0
 */

namespace Candango\Carcara
{
    class File
    {
        /**
         * Delete the path and all children if path is a directory
         *
         * @param string $path Path to be deleted
         */
        public static function delete($path)
        {
            if (is_dir($path)) {
                $it = new \RecursiveDirectoryIterator($path,
                    \FilesystemIterator::SKIP_DOTS);
                foreach (new \RecursiveIteratorIterator($it, 1) as $child) {
                    $pName = "" . $child;
                    if ($child->isDir() && !$child->isLink() &&
                        file_exists($child)) {
                        rmdir($pName);
                    } else {
                        unlink($pName);
                    }
                }
                rmdir($path);
            } else {
                unlink($path);
            }
        }

        /**
         * Write a string in a given file
         *
         * @param string $file The file name
         * @param string $string The string to be writed
         * @throws \Exception
         */
        public static function write($file, $string)
        {
            if (!$fp = @fopen($file ,"w")) {
                throw new \Exception(sprintf("Error opening the file %s to ".
                    "write.", $file));
            }

            if (!flock($fp,LOCK_EX)) {
                throw new \Exception(sprintf("Error locking the file %s.",
                    $file));
            }

            if (!fwrite($fp, $string)) {
                throw new \Exception(sprintf("Error writing the file %s.",
                    $file));
            }

            flock($fp,LOCK_UN);
            fclose($fp);
        }

        /**
         * Reads the content of given file
         *
         * @param string $file The file name
         * @return string The file content
         * @throws \Exception
         */
        public static function read($file)
        {
            if (@!$fp = fopen($file ,"r")) {
                throw new \Exception(sprintf("Error opening the file %s to ".
                    "read.", $file));
            }

            if (!flock( $fp, LOCK_SH)) {
                throw new Exception(sprintf("Error locking the file %s.",
                    $file));
            }

            $fileCode = fread($fp, filesize($file));

            flock($fp,LOCK_UN);
            fclose($fp);

            return $fileCode;
        }
    }
}
