<?php declare(strict_types=1);
namespace Link;
require '../inc/settings.inc.php';
require '../inc/utils.inc.php';

/**
 * api has the following endpoints:
 * Request methods allowed: GET, POST, PUT, DELETE, OPTIONS
 * 
 * GET: /api/index.php/<endpoint>[/<id>]
 * - returns a list of all entries in the database or a single object
 * 
 * GET: /api/index.php/<endpoint>?<query>
 * - query is a key=value pair, e.g. ?Name=foo or ?Name=foo&Age=42 or ?Name=foo*
 * - returns a list of all entries in the database or a single object
 *
 * POST: /api/index.php/<endpoint>[/<id>]
 * - creates a new entry in the database
 * 
 * PUT: /api/index.php/<endpoint>
 * - updates an existing entry in the database
 * 
 * DELETE: /api/index.php/<endpoint>[/<id>]
 * - deletes an entry from the database
 * 
 * Payload: JSON
 * Response: JSON array or JSON object or JSON object or error message
 */


/**
 * these entities are allowed, all others get a notFoundResponse
 */
$allowed = ['Code'];	

$requestMethod = $_SERVER["REQUEST_METHOD"];

/**
 * get the endpoint from the request
 * e.g. /api/index.php/<endpoint>[/<id>] or /api/index.php/<endpoint>?<query>
 * $uri[0] is always empty, $uri[1] is the endpoint
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
/**
 * $uri[2] is the id, if it present
 */
$uri = explode( '/', $path );
// remove empty, api, index
array_shift($uri);
array_shift($uri);
array_shift($uri);

if( !isEntityValid($uri[0]) ) {
  sendResponse(notFoundResponse());
  exit();
}
/* early response to GETs */
if( "GET" === $requestMethod ) {
	sendResponse( doGet( $uri ) );
	exit();
}
/**

 * set the query string if query is present
 */
if( $query ) {
  $uri[1] = parseParameters($query);
}
unset($path, $query);

// prepend the namespace
$uri[0] = __NAMESPACE__ . '\\' . $uri[0];

require '../inc/session.inc.php';
if( !array_key_exists('user_id', $_SESSION) ) {
  sendResponse(unprocessableEntityResponse());
}
switch($requestMethod) {
  case 'GET':     $response = doGet($uri);     break;
  case 'POST':    $response = doCreate($uri);  break;
  case 'PUT':     $response = doUpdate($uri);  break;
  case 'DELETE':  $response = doDelete($uri);  break;
  case 'OPTIONS': $response = okResponse();   break;
  default:        $response = notFoundResponse(); break;
}

sendResponse($response);

/**
 * CHeck if a the request contains a valid entity name
 *
 * @param  array $uri
 * @return void
 */
function isEntityValid( ?string $entity ) {
  global $allowed;
  return $entity and in_array( $entity, $allowed );
}

/**
 * If a parameter isther check if numeric
 *
 * @param  array $uri
 * @return void
 */
function parseParameters( ?string $param ) {
  global $uri;
  if( !isset($uri[1]) )  {
    $result = [];
    foreach( explode('&', $param) as $param ) {
      $param = explode('=', $param);
      $result[$param[0]] = str_replace('*','%',$param[1]); // use the like operator
    }
    return $result;
  }
  else {
    return $uri[2];
  }
}

  
/**
 * Send a prepared response
 *
 * @param  array $response - containing [0]=> respose code, [1]=> response body
 * @return void
 */
function sendResponse(array $response): void
{
  header($response['status_code_header']);
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
  header("Access-Control-Max-Age: 3600");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

  if ($response['body']) {
    exit( $response['body'] );
  }
}

/**
 * Handle a GET request, if {id} is provided attempt to retrieve one, otherwise all.
 *
 * @param  mixed $uri
 * @return array
 */
function doGet(array $uri): array
{
  if( isset($uri[1]) and !is_array($uri[1]) ) {
    if( $obj = new $uri[0]($uri[1]) and $obj-> isRecord() ) {
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($obj-> getArrayCopy() );
      return $response;
    } else {
      return notFoundResponse();
    }
  }

  // no key provided, return all or selection
  // paging would be nice here

  $result = [];

  if( isset($uri[1]) and is_array($uri[1]) ) {
    $where = [];
    foreach( $uri[1] as $key => $value ) {
      $where[$key] = urldecode( $value );
    }
    foreach( ($uri[0])::findAll($where) as $o ) {
      $result[] = $o-> getArrayCopy();
    }

  } else {
    foreach( ($uri[0])::findAll() as $id=>$obj )
      $result[] = $obj-> getArrayCopy();
  }

  if( count($result)===0 ) {
    return notFoundResponse();
  }
  $response['status_code_header'] = 'HTTP/1.1 200 OK';
  $response['body'] = json_encode($result);

  return $response;
}

/**
 * Handle a POST request create a record
 *
 * @param  mixed $uri
 * @return array
 */
function doCreate(array $uri): array
{
  $response = [];
  $input = json_decode(file_get_contents('php://input'), true);
  $obj = $uri[0]::createFromArray($input);
  if( $obj-> freeze() ) {
    $response['status_code_header'] = 'HTTP/1.1 201 Created';
    $response['body'] = json_encode( [ 'id'=> $obj-> getKeyValue(), 'result'=> 'created' ] );
  } else {
    $response['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
  }
  return $response;
}

/**
 * Handle PUT request, update a record for {id}
 *
 * @param  mixed $uri
 * @return array
 */
function doUpdate(array $uri): array
{
  $response = [];

  if( !isset($uri[1]) ) {
    return unprocessableEntityResponse();
  }

  $obj = new $uri[0]($uri[1]);
  if( $obj-> isRecord()) {
    $input = json_decode(file_get_contents('php://input'), true);
    $obj-> setFromArray( $input );

    if( $result = $obj-> freeze() ) {
      $response['status_code_header'] = 'HTTP/1.1 200 Updated';
      $response['body'] = json_encode( ['id'=> $obj-> getKeyValue(), 'result'=> $result ] );

    } else {
      $response['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
    }
    return $response;
  }
  return notFoundResponse();
}
/**
 * Handle DELETE request, delete a record for {id}
 *
 * @param  mixed $uri
 * @return array
 */
function doDelete(array $uri): array
{
  $response = [];
  if( !isset($uri[1]) ) {
    return notFoundResponse();
  }
  $obj = new $uri[0]($uri[1]);
  if( !$obj-> isRecord() ) {
    return notFoundResponse();
  }
  $response['status_code_header'] = 'HTTP/1.1 200 DELETED';
  $response['body'] = json_encode( [ 'id'=> (int)$uri[1], 'result'=> $obj->delete() ] );
  return $response;

  return notFoundResponse();
}


/**
 * create 200 Response
 *
 * @return array
 */
function okResponse(): array
{

  $response['status_code_header'] = 'HTTP/1.1 200 OK';
  $response['body'] = null;

  return $response;
}
/**
 * Create a 404 response
 *
 * @return array
 */
function notFoundResponse(): array
{

  $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
  $response['body'] = null;

  return $response;
}

/**
 * Create a 422 response
 *
 * @return array
 */
function unprocessableEntityResponse(): array
{
  $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
  $response['body'] = json_encode([ 'error' => 'Invalid input' ]);
  
  return $response;
}
