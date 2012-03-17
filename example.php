<?php
    require_once "cache.class.php";
    
    /**
     * File Caching Class Example
     * How to use:
     * Use the static method: Start_cache with the time (in seconds), or leave it empty for unlimited time. Other parametars are: $path - where to save the cache, $serial - Save as serialized or as is
     */
    
    Cache::Start_cache(300);
    
    /**
     * We have started cache for 300 seconds - 5 minutes.
     * The content will be serialized because of parametar @serial is set to true. The caching path is: cache/$path.
     * If $path isn't set, so it will be only cache/
     */
    
    
    echo "Hello Stranger.<br/>This file is cached or gonna be cached. To check it, check the time on your computer clock.<br/>This file has been cached on: " . date("H:i:s") . " for 5 minutes!";
?> 