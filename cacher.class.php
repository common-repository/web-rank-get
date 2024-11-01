<?php
class Cacher
{
   var $cachedir = "";
   var $suffix;

   function Cacher($suffix='')
   {
      $this->suffix = $suffix;
   }
   
   function fetch($url, $useragent, $cacheTime=86600)
   {     
      // Determine cache file name
      $cacheFile = $this->cachedir . md5($url) . $this->suffix . '.cache' ;
      $refresh = true;
      if(@file_exists($cacheFile))
      {
         $refresh = (time() - $cacheTime > @filemtime($cacheFile)) ;
      }
      @clearstatcache();
      
     // Cache file if needed
     if($refresh) 
     {
         try
         {
            $tries = 0;
            $errors = 0;
	    $error = "";
            $contents = false;
            while($tries<3)
            {
               $tries++;
               if(!$contents)
               {
		  $opts = array(
			  'http'=>array(
  			  'method'=>"GET",
    			  'header'=>"Accept-language: en\r\n" .
    			  	    "User-Agent: ".$useragent."\r\n")
		  );
		  $context = stream_context_create($opts);
                  $contents = @file_get_contents($url, false, $context);
               }
               if(!$contents)
               {
                  $error .= " GET_FAIL ";
               }
               else
               {
                  $result = @file_put_contents($cacheFile, $contents);
                  if(!$result)
                  {
                     $error .= " PUT_FAIL ";
                  }
                  else
                  {
                     return $cacheFile;   
                  }
               }
               // TODO:  Is this necessary?  Is there a better way?
               usleep(10000);
            }
            error_log("\n" . date('r') . " - Failed to cache: {$url}", 3, 'error_log');
            error_log("\n" . date('r') . " - Failure reasons: {$error}", 3, 'error_log');
            return false;
         }
         catch(Exception $e)
         {
            error_log("\n" . date('r') . " - {$e}", 3, 'error_log');
            error_log("\n" . date('r') . " - Cacher error: {$error}", 3, 'error_log');
            return false;
         }
         
     }
     return $cacheFile;
   } 
   
   function fetchContents($url, $useragent, $cacheTime=86600)
   {
      $file = $this->fetch($url, $useragent, $cacheTime);
      if(!$file) return false;
      return file_get_contents($file);   
   }
   
   function clear($url)
   {
      // Determine cache file name
      $cacheFile = $this->cachedir . md5($url) . $this->suffix . '.cache' ;
      if(@file_exists($cacheFile))
      {
         @unlink($cacheFile);
      }
   }
}

?>
