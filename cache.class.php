<?php
    /**
     * File Caching Class
     * Simple class to cache the output and save it to the file.
     * @author Michael Arenzon <michael@arenzon.me>
     * @version 1.0
     *
     * Usage:
     * Use only the static method Cache::Start_cache() with the parametars: $time = Time in seconds, False for unlimited time, $path - where to save the cache, $serial - Save as serialized or as is
     */
    
    define('ROOT_DIR',realpath(dirname(__FILE__)));
    
    class Cache
    {
        public $cache_file; // The cache file
        public $time; // Time now (time() func)
        public $path; // Path -- see start_cache func
        public $serial; // Serialize -- see start_cache func
        
        public function __construct() 
        {
            /* Setting the variables cache_file & time */
            $this->cache_file = $_SERVER['PHP_SELF'] . (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '') . (!empty($_COOKIE) ? json_encode($_COOKIE) : '');
            $this->cache_file = explode('/',$this->cache_file);
            $this->cache_file = md5($this->cache_file[count($this->cache_file) - 1]).'.c';
            $this->time = time();
        }
        
        public static function instance()
        {
            return new Cache;
        }
        
        public static function Start_cache($time = false, $path = '', $serial = true)
        {
            /* $time = min to expire, $path = folder to save cache (in folder cache), $serial = use serialize */
            $obj = Cache::instance();
            $rpath = ROOT_DIR .'/cache/' .$path .$obj->cache_file;

            if($data = $obj->Load_cache($path,$time)) // if cache exists
            {
                echo $data;
                exit;
            }
            else
            {
                /* Insert the data to the variables */
                $obj->path = $path;
                $obj->serial = $serial;
                /* Starting ob_start function and callback to the buffer function that save's the output */
                ob_start(array(&$obj,'gb'));
                return false;
            }
        }
        
        public function Save_cache($value, $path = '', $serial = true)
        {
            if($serial) $value = serialize($value); // if serial is true it will serialize the data
            if(!empty($path)) // if there is path it will create the dirs
            {
                $folders = explode('/', $path);
                //if($folders[count($folders)-1] == '') unset($folders[count($folders)-1]);
                $fpath = ROOT_DIR .'/cache/';
                $path = ROOT_DIR .'/cache/' . $path .$this->cache_file;
                
                foreach($folders as $folder)
                {
                    if(trim($folder) != '')
                    {
                        $fpath .= $folder .'/';
                        if(!is_dir($fpath))
                        {
                            if(!mkdir($fpath, 0777)) echo 'could not make dir.';
                            if(!chmod($fpath, 0777)) echo 'could not chmod.';
                            if(!file_put_contents($fpath . '.htaccess', "Order Deny,Allow\nDeny from all\nAllow from 127.0.0.1" )) trigger_error('Could not write htaccess file.');
                        }
                    }
                }
            }
            
            if(empty($path)) $path = ROOT_DIR .'/cache/' . $path .$this->cache_file;
            /* Saving the cache */
            $f = fopen($path, 'w+');
            if (flock($f, LOCK_EX))
            {
                fwrite($f, $value);
                flock($f, LOCK_UN);
            }
            
            fclose($f);
            unset($value);
        }
        
        public function Load_cache($path = '', $time = false, $serial = true)
        {
            $path = ROOT_DIR .'/cache/' .$path .$this->cache_file;

            if($time) // if there's time limit
            {
                $time *= 60;
                if (!file_exists($path)) return false;
                if ((filemtime($path) + $time) < $this->time)
                {
                    if(!unlink($path)) trigger_error('Error deleting');
                    return false;
                }
            }
            
            if (!file_exists($path)) return false; // if cache isn't exists return false
            $data = file_get_contents($path);
            return $serial ? unserialize($data) : $data; // return the data
        }
        
        public function gb($buffer)
        {
            /* Save the cache and return the buffer to the client */
            $this->Save_cache($buffer,$this->path,$this->serial);
            return $buffer;
        }
    }
?>