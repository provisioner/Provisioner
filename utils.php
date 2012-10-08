<?php

/*
 * Recursively merge two arrays, overwriting any keys that match with the second array
 */
function merge_arrays($new_array, $Arr2)
{
  foreach($Arr2 as $key => $Value)
  {
    if(array_key_exists($key, $new_array) && is_array($Value))
      $new_array[$key] = merge_arrays($new_array[$key], $Arr2[$key]);
    else
      $new_array[$key] = $Value;
  }
  return $new_array;
}


function import_settings($filename, &$settings) {
    $tmp = json_decode(file_get_contents($filename), TRUE);
    
    // TO DO: Loop through recursively the entire array and look for any "quantity" specifications. If they exist
    // make copies of the items
    
    $settings = merge_arrays($settings, $tmp);
    
    return TRUE;
}