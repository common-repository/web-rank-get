<?php
require_once('cacher.class.php');

class AlexaRank
{
   var $rank;
   var $site;
 
   function AlexaRank($site, $cacheTime=86400)
   {
      // Keep site URI on the instance
      $this->site = $site;
      $this->rank=0;
      
      $encodedSite = urlencode($site);
      $uri = "http://data.alexa.com/data?cli=10&dat=s&url={$encodedSite}";
      
      // Open uri or fail
      $cacher = new Cacher('_alexa');
      $useragent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:6.0.2) Gecko/20100101 Firefox/6.0.2";
      if(isset($_SERVER['HTTP_USER_AGENT'])) {
      	$useragent = $_SERVER['HTTP_USER_AGENT'];
      }
      $raw = $cacher->fetchContents($uri, $useragent, $cacheTime);
      $this->raw = $raw;
      
      // Read output into an XML DOM
      try
      {
         $doc = new SimpleXMLElement($raw);
      }
      catch(Exception $e)
      {
         error_log("\n" . date('r') . " - Bad XML in Alexa output:\n{$raw}", 3, 'error_log');
         $this->rank=0;
         return false;
      }
      
      // Locate all rank elements in DOM
      $rankElement = $doc->xpath('/ALEXA/SD/POPULARITY/@TEXT');
      if(!isset($rankElement) or $rankElement == FALSE) 
      {
         error_log("\n" . date('r') . " - Cant find rank in Alexa output:\n{$raw}", 3, 'error_log');
         $this->rank=0;
         return false;
      }
      
      // Success
      return true;
   }
   
   function getRank()
   {
      return $this->rank;  
   }
   
   function getSite()
   {
      return $this->site;
   }
}

?>
