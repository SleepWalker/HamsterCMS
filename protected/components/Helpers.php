<?php
class Helpers
{
  /*
  * Укорачивает строку с HTML до задоной длины $maxLength
  */
  /*public static function truncHtml($maxLength, $html)
  {
      return self::truncate($html, $maxLength);
  }
  
  //+ Jonas Raoni Soares Silva
  //@ http://jsfromhell.com
  // http://www.dzone.com/snippets/truncate-text-preserving-html
	public static function truncate($text, $length, $suffix = '…', $isHTML = true)
  { 
    $i = 0; 
    $tags = array(); 
    if($isHTML)
    { 
      preg_match_all('/<[^>]+>([^<]*)/u', $text, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER); 
      foreach($matches as $o)
      { 
        if($o[0][1] - $i >= $length) break; 
        $t = strtok($o[0][0], " \t\n\r\0\x0B>");
        $t = mb_substr($t,1,mb_strlen($t, 'UTF-8'), 'UTF-8'); echo $t.'||';
        if($t[0] != '/') $tags[] = $t; 
        elseif(end($tags) == mb_substr($t,1,mb_strlen($t, 'UTF-8'), 'UTF-8')) array_pop($tags); 
        $i += $o[1][1] - $o[0][1]; 
      } 
    } print_r($tags);
    $output = mb_substr($text, 0, $length = min(mb_strlen($text, 'UTF-8'), $length + $i), 'UTF-8') . (count($tags = array_reverse($tags)) ? '' : ''); 
    if (mb_strlen($text, 'UTF-8') > $length) 
    { 
      $output = mb_substr($output,-4,4, 'UTF-8')=='' ? $output=mb_substr($output,0,(mb_strlen($output, 'UTF-8')-4), 'UTF-8').$suffix.'' : $output.=$suffix; 
    } 
    return $output; 
  }*/
}

/*
  function truncHtml($maxLength, $html, $isUtf8=true)
  {
      $printedLength = 0;
      $position = 0;
      $tags = array();
  
      // For UTF-8, we need to count multibyte sequences as one character.
      $re = $isUtf8
          ? '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;|[\x80-\xFF][\x80-\xBF]*}'
          : '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}';
  
      while ($printedLength < $maxLength && preg_match($re, $html, $match, PREG_OFFSET_CAPTURE, $position))
      {
          list($tag, $tagPosition) = $match[0];
  
          // Print text leading up to the tag.
          $str = substr($html, $position, $tagPosition - $position);
          if ($printedLength + strlen($str) > $maxLength)
          {
              print(substr($str, 0, $maxLength - $printedLength));
              $printedLength = $maxLength;
              break;
          }
  
          print($str);
          $printedLength += strlen($str);
          if ($printedLength >= $maxLength) break;
  
          if ($tag[0] == '&' || ord($tag) >= 0x80)
          {
              // Pass the entity or UTF-8 multibyte sequence through unchanged.
              print($tag);
              $printedLength++;
          }
          else
          {
              // Handle the tag.
              $tagName = $match[1][0];
              if ($tag[1] == '/')
              {
                  // This is a closing tag.
  
                  $openingTag = array_pop($tags);
                  //assert($openingTag == $tagName);
                  if($openingTag != $tagName); // check that tags are properly nested.
                    Yii::log('Inproperly nested tags
                    $openingTag: ' . $openingTag . '; $tagName: ' . $tagName . $html, 'info', 'truncHtml');
  
                  print($tag);
              }
              else if ($tag[strlen($tag) - 2] == '/')
              {
                  // Self-closing tag.
                  print($tag);
              }
              else
              {
                  // Opening tag.
                  print($tag);
                  $tags[] = $tagName;
              }
          }
  
          // Continue after the tag.
          $position = $tagPosition + strlen($tag);
      }
  
      // Print any remaining text.
      if ($printedLength < $maxLength && $position < strlen($html))
          print(substr($html, $position, $maxLength - $printedLength));
  
      // when truncated string is shorter, then $html. Then echo three dots    
      if (strlen($html) > $maxLength)
        echo '<span class="truncTrippleDot"> ...</span>';
        
      // Close any open tags.
      while (!empty($tags))
          printf('</%s>', array_pop($tags));
  }
*/