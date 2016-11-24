<?php
/**
 * @author    Adam Bukowski <abukowski@telaxus.com>
 * @version   1.0
 * @copyright Copyright &copy; 2015, Telaxus LLC
 * @license   MIT
 * @package   epesi-base
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class FileCache
{
    private $file;
    private $data = array();

    function __construct($file)
    {
        $this->file = $file;
        $this->init();
    }

    protected function init()
    {
        $data = false;
        if (file_exists($this->file)) {
            $content = file_get_contents($this->file);
            $content = substr($content, 8);
            $data = @unserialize($content);
            if (!$data) {
                @unlink($this->file);
            }
        } else {
            $cache_dir = dirname($this->file);
            if (!file_exists($cache_dir)) {
                mkdir($cache_dir, 0777, true);
            }
        }
        $this->data = is_array($data) ? $data : array();
    }

    protected function save()
    {
        $data = "<?php //";
        $data .= serialize($this->data);
        file_put_contents($this->file, $data, LOCK_EX);
    }

    public function get($name, $default = null)
    {
        $ret = isset($this->data[$name]) ? $this->data[$name] : $default;
        return $ret;
    }

    public function set($name, $value)
    {
        $this->data[$name] = $value;
        $this->save();
    }

    public function clear($name = null)
    {
        if(function_exists('xcache_clear_cache') && function_exists('xcache_count')) {
            $count = xcache_count(XC_TYPE_PHP);
            for($cache_id=0; $cache_id<$count; $cache_id++)
                xcache_clear_cache(XC_TYPE_PHP,$cache_id);
        }

        if ($name === null) {
            $this->data = array();
        } else {
            unset($this->data[$name]);
        }
        $this->save();
    }

}

class Cache
{
    protected static $cache_object;

    public static function init($cache_object)
    {
        self::$cache_object = $cache_object;
    }

    public static function get($name, $default = null)
    {
        return self::$cache_object->get($name, $default);
    }

    public static function set($name, $value)
    {
        return self::$cache_object->set($name, $value);
    }

    public static function clear($name = null)
    {
        return self::$cache_object->clear($name);
    }

}

Cache::init(new FileCache(EPESI_LOCAL_DIR . '/' . DATA_DIR . '/cache/common_cache.php'));
