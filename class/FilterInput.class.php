<?php declare(strict_types=1);
class FilterInput extends Filter {
  protected function getTemplate():string {
    $submit = $this-> auto_submit? " class=\"select-auto-submit\"" : "";	
    return 
      "\t\t<label for=\"inp_%1\$s\">%2\$s:</label>\n" .
      "\t\t<input type=\"text\" id=\"inp_%1\$s\"$submit name=\"%1\$s\" value=\"%3\$s\">\n";

  }
}