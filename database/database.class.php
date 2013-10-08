<?php

namespace WebFW\Database;

class Database
{
    protected $classExtension = '.class.php';

    public function getCreateQueries()
    {
        $basePath = \WebFW\Config\BASE_PATH;
        $list = array();
        if (file_exists($basePath) && is_dir($basePath)) {
            $list = $this->getTableList($basePath);
        }
        var_dump($list);die;
    }

    protected function getTableList($directoryPath)
    {
        $list = array();
        $handle = opendir($directoryPath);
	while (($entry = readdir($handle)) !== false) {
            if ($entry === '.' || $entry === '..' || $entry === '.git' || $entry === '__install') {
                continue;
            }
            $entry = $directoryPath . DIRECTORY_SEPARATOR . $entry;
            if ($entry === \WebFW\Config\PUBLIC_PATH) {
                continue;
            }
            if (is_dir($entry)) {
                $list = array_merge($list, $this->getTableList($entry));
                continue;
            }
            if (substr_compare($entry, $this->classExtension, -strlen($this->classExtension), strlen($this->classExtension)) !== 0) {
                continue;
            }
            $entry = $this->getClassNameFromFilename($entry);
            if (!is_subclass_of($entry, '\\WebFW\\Database\\table')) {
                continue;
            }
            $list[] = $entry;
        }
        closedir($handle);
        return $list;
    }

    protected function getClassNameFromFilename($filename)
    {
        $fp = fopen($filename, 'r');
        $class = $namespace = $buffer = '';
        $i = 0;
        if ($fp === false) {
            return null;
        }
        while (!$class) {
            if (feof($fp)) break;

            $buffer .= fread($fp, 512);
            $tokens = @token_get_all($buffer);

            if (strpos($buffer, '{') === false) continue;

            for (;$i<count($tokens);$i++) {
                if ($tokens[$i][0] === T_NAMESPACE) {
                    for ($j=$i+1;$j<count($tokens); $j++) {
                        if ($tokens[$j][0] === T_STRING) {
                            $namespace .= '\\'.$tokens[$j][1];
                        } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }

                if ($tokens[$i][0] === T_CLASS) {
                    for ($j=$i+1;$j<count($tokens);$j++) {
                        if ($tokens[$j] === '{') {
                            $class = $tokens[$i+2][1];
                        }
                    }
                }
            }
        }

        if ($class === '') {
            return null;
        }

        return $namespace . '\\' . $class;
    }
}
