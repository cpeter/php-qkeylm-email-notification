<?php
/**
 * An extended CURL class.
 *
 * CURLer is focused on combining several CURL-functions into a handy class.
 *
 * @package other
 * @subpackage curler
 * @version RC.0.1-12/06/2007
 */
class CURLer
{
  /**
   * Contains the CURL-session.
   * @var object
   */
  private $session;

  /**
   * Contains the content gotten through curl_exec().
   * @var string
   */
  private $content;

  /**
   * Contains the options to be set.
   * @var array
   */
  public $options;

  /**
   * Contains the information gotten through curl_getinfo().
   * @var array
   */
  private $info;

  /**
   * Contains the last executed url.
   * @var string
   */
  private $lastUrl;

  /**
   * Constructor.
   * @return void
   */
  function __construct()
  {
    $this->session = null;
    $this->content = null;
    $this->options = array();
    $this->info = array();
    $this->url = null;

    $this->setOption( CURLOPT_RETURNTRANSFER, true );
  }

  /**
   * Destructor.
   * @return void
   */
  function __destruct()
  {
    unset( $this->session );
    unset( $this->content );
    unset( $this->options );
    unset( $this->info );
  }

  /**
   * Opens and closes a given url and stores the content.
   * @param string $url
   * @param array $params
   * @return void
   */
  public function execute( $url, $params = array(), $encode = true )
  {

    // Reset lastUrl.
    $this->lastUrl = null;

    // Parse the parameters.
    $url = $this->parseUrl( $url, $params, $encode );
    
    // Try to open the url and set the options.
    $this->open( $url );
    $this->initialize();

    // Set content info.
    $this->content = curl_exec( $this->session );
    $this->info = curl_getinfo( $this->session );

    // Close the url.
    $this->close();

    // Set lastUrl.
    $this->lastUrl = $url;

  }

  /**
   * Opens and closes a given url and do this through the POST-method.
   * @param string $url
   * @param array $params
   * @return void
   */
  public function executePost( $url, $params = array(), $encode = true )
  {
      
    // Parse the params.
    $query = $this->parseParams( $params, $encode );

    // Set the options.
    $this->setOption( CURLOPT_POST, 1 );
    $this->setOption( CURLOPT_POSTFIELDS, $query );

    // Execute the url.
    $this->execute( $url );

    // Unset the options.
    $this->unsetOption( CURLOPT_POST );
    $this->unsetOption( CURLOPT_POSTFIELDS );
  }

  /**
   * Opens a connection to a given url.
   * @param string $url
   * @return void
   */
  private function open( $url )
  {
    if( ( $this->session = curl_init( $url ) ) === false )
    {
      throw new Error( 'Could not open the url [' . $url . ']. CURL error [' . curl_errno() . ']:' . curl_error,  CURLER_INIT_FAILED );
    }
  }

  /**
   * Closes the current session.
   * @return void
   */
  private function close()
  {
    curl_close( $this->session );
  }

  /**
   * Sets all the options.
   * @return void
   */
  private function initialize()
  {
    if( !empty( $this->options ) )
    {
      foreach( $this->options as $name => $value )
      {
        if( !curl_setopt( $this->session, $name, $value ) )
        {
          throw new Error( 'Option [' . $name . ':' . $value . '] couldn\'t be set.', E_CURLER_INVALID_OPTION );
        }
      }
    }
  }

  /**
   * Sets an option that's used for curl_setopt().
   * @param string $name
   * @param string $value
   */
  public function setOption( $name, $value )
  {
    $this->options[ $name ] = $value;
  }

  /**
   * Unset an option already set.
   * @param string $name
   * @return void
   */
  public function unsetOption( $name )
  {
    if( array_key_exists( $name, $this->options ) )
    {
      unset( $this->options[ $name ] );
    }
  }

  /**
   * Returns the content, if there is any.
   * @return void
   */
  public function getContent()
  {
    return $this->content;
  }

  /**
   * Get's info about a specifiction option from curl_getinfo().
   * @param string $option
   * @return void
   */
  public function getInfo( $option )
  {
    if( !array_key_exists( $option, $this->info ) )
    {
      throw new Error( 'No valid option [' . $option . '] has been given.', E_CURLER_INVALID_INFO_OPTION );
    }

    return $this->info[ $option ];
  }

  /**
   * Parses the paramters into a valid query-URL.
   * @param array $params
   * @return string $query
   */
  private function parseParams( $params = array(), $encode = true )
  {
    $query = '';

    // Parse the parameters, if there are any.
    if( !empty( $params ) )
    {
      foreach( $params as $key => $value )
      {
        if( !empty( $query ) )
        {
          $query .= '&';
        }

        if( $encode )
        {
          $query .= urlencode( $key ) . '=' . urlencode( $value );
        }
        else
        {
          $query .= $key . '=' . $value;
        }
      }
    }

    return $query;
  }

  /**
   * Parses the paramters into a valid query-URL.
   * @param string $url
   * @param array $params
   * @return string $url
   */
  public function parseUrl( $url, $params = array(), $encode = true )
  {
    // Parse the parameters.
    $query = $this->parseParams( $params, $encode );

    // If there isn't a '?' at the end then place it.
    if( substr( $url, -1, 1 ) != '?' && !empty( $query ) )
    {
      $url .= '?' . $query;
    }

    return $url;
  }
}
?>