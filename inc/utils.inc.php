<?php
/**
 * Autoloader
 */
spl_autoload_register( function( string $class_name ) {
	$class_name = str_replace('\\', '/', $class_name);
	foreach(['class', 'int', 'trait'] as $extension ) {
		$filename = $_SERVER["DOCUMENT_ROOT"] . sprintf( '/class/%s.%s.php', $class_name, $extension );
		if( file_exists($filename) ) {
			require_once $filename;
			return;
		}
	}
});

/**
 * Are we in debug mode?
 */
define('DEBUG', strpos( $_SERVER['SERVER_NAME'], 'localhost')!==false);

/**
 * check_params
 *
 * @param  array $params this params should be in the $_REQUEST
 * @return void
 */
function check_request_params( array $params )
{
	$params_check = array_intersect(array_keys($_REQUEST),$params);
	if(count($params_check) !== count($params)) {
		header("HTTP/1.1 400 Bad Request");
		die();
	}
}

/**
 * Wrap a text in a tag
 * @param string $tag Tag to wrap arount text
 * @param string $text Text to wrap tag around
 * @param string $class optional class or classes
 * @param string $id optional id
 */
function wrap_tag( $tag, $text, $class = null, $id = null ) {
	return "<$tag" .
		// if class is set include a class="" section
		( $class ? " class=\"$class\"" : '' ) .

		// if id is set include a id="" section
		( $id    ? " id=\"$id\"" : '' ) .

		">$text</$tag>";
}

/**
 * Create option entry setting the selected value
 *
 * @param string $text Text to display
 * @param string $value the value attribute of the option
 * @param string $var The variable holding $value to test the selection
 * @return string
 */
function option_tag( string $text, mixed $value, string $var ): string {
	return '<option ' . 
		// set attribute selected when $var has value $value
		( ( $var==$value )? 'selected ': '' ) . 
		'value="' . $value . '">' . $text . PHP_EOL;
}

/**
 * get a POST value if set, otherwise default
 *
 * @param string $key form or  
 * @param string $default
 * @return string
 */
function get_POST( $key, $default = '' ) {
	return ( array_key_exists( $key, $_POST ) && '' !== $_POST[$key] )
		? htmlspecialchars( $_POST[$key] )
		: $default;
}
/**
 * Method kebabToPascal
 */
function kebabToPascal( string $str ): string {
	return str_replace( ' ', '', ucwords( str_replace( '-', ' ', $str ) ) );
}

/**
* convert snake_case to PascalCase
*/
function snakeToPascal( string $str ): string {
	return str_replace (' ', '', ucwords( str_replace( '_', ' ', $str ) ) );
}

/**
* convert snake_case to camelCase
*/
function snakeToCamel( string $str ): string {
	return lcfirst( snakeToPascal( $str ) );
}

/**
* convert kebab-case to camelCase
*/
function kebabToCamel( string $str): string {
	return lcfirst( kebabToPascal( $str ) );
}

/**
 * convert bin to url friendly base64
 */
function base64url_encode( $data ){
  return rtrim( strtr( base64_encode( $data ), '+/', '-_'), '=');
}
/**
 * convert url friendly base64 to bin
 */
function base64url_decode( $data ){
  return base64_decode( strtr( $data, '-_', '+/') . str_repeat('=', 3 - ( 3 + strlen( $data )) % 4 ));
}