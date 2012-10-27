<?php
namespace WebFW\Core;

class Request
{
	protected $values = array();
	protected static $instance;

	protected function __construct()
	{
		$this->values = &$_REQUEST;
	}

	public static function getInstance()
	{
		if (!static::$instance)
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	public function __isset($key)
	{
		return array_key_exists($key, $this->values);
	}

	public function __get($key)
	{
		return isset($this->values[$key]) ? $this->values[$key] : null;
	}

	public function __set($key, $value = null)
	{

		if (is_null($value))
		{
			if (isset($this->values[$key]))
			{
				unset($this->values[$key]);
			}
		}
		else
		{
			$this->values[$key] = $value;
		}
	}

	public function getValue($name)
	{
		return $this->__get($name);
	}

	public function setValue($name, $value)
	{
		$this->__set($name, $value);
	}

	/**
	 * filtrira grupu keyeva iz requesta po prefixu
	 *
	 * @param string $p_prefix naziv grupe
	 * @param string $prefix_in_result
	 * @return array
	 */
	public function getFilteredValues($prefix, $prefixInResult = false)
	{

		$result = array();
		foreach ($this->values as $key => $val) {
			if (substr($key, 0, fw3k2_string::strlen($prefix)) == $prefix) {
				$nkey = $key;
				if (!$prefixInResult) {
					$nkey = substr($key, -(fw3k2_string::strlen($key) - fw3k2_string::strlen($prefix)));
				}
				$result[$nkey] = $val;
			}
		}

		return $result;
	}

	public function getValues()
	{
		return $this->values;
	}

	public function removeValue($key)
	{
		if (key_exists($key, $this->values)) {
			unset($this->values[$key]);
		}
	}

	/**
	 * Vraca xhtml enkodirani url slozen od svih stavki postojeceg GET ili POST requesta
	 * ali sa novim (eventualno promijenjenim vrijednostima) i svim naknadno dodanim
	 *
	 */
// 	public function get_current_url( $p_xhtml = true , $p_arr_override = null , $p_include_server = true ) {
//
// 		$ctl = $this->get_value( 'ctl' );
// 		$action = $this->get_value( 'action' );
//
// 		$arr_params = array();
//
// 		foreach( $this->values as $key => $value )
// 		{
// 			if( $key != 'ctl' && $key != 'action' && $key != 'PHPSESSID' && $key != '__URL' )
// 			{
// 				if( is_array( $p_arr_override ) && isset( $p_arr_override[ $key ] ) )
// 				{
// 					$arr_params[ $key ] = $p_arr_override[ $key ];
// 				}
// 				else
// 				{
// 					$arr_params[ $key ] = $value;
// 				}
// 			}
// 		}
//
// 		if( is_array( $p_arr_override ) )
// 		{
// 			foreach( $p_arr_override as $key => $value )
// 			{
// 				if( ! is_null( $value ) )
// 				{
// 					if( ! isset( $arr_params[ $key ] ) )
// 					{
// 						$arr_params[ $key ] = $value;
// 					}
// 				}
// 				else
// 				{
// 					unset( $arr_params[ $key ] );
// 				}
// 			}
// 		}
//
// 		$_server = '';
//
// 		if( $p_include_server )
// 		{
// 			$_server = fw3k2_server::instance()->get_current_protocol() . '://' . $_SERVER[ 'SERVER_NAME' ];
// 		}
//
// 		return $_server . fw3k2_url::seo( $ctl , $action , $arr_params , false , '' , $p_xhtml );
// 	}

// 	public static function get_current_url_full( $p_include_server = true )
// 	{
// 		$_server = '';
// 		if( $p_include_server )
// 		{
// 			$_server = fw3k2_server::instance()->get_current_protocol() . '://' . $_SERVER[ 'SERVER_NAME' ];
// 		}
//
// 		return $_server . $_SERVER[ 'REQUEST_URI' ];
// 	}


	/**
	 * returns url parameter prepared for usage as _return_url
	 *
	 **/
// 	public static function get_current_url_as_return_url_param( $p_xhtml = true )
// 	{
// 		$amp = $p_xhtml ? '&amp;' : '&';
// 		return $amp . '_return_url=' . urlencode( self::get_current_url_full() );
// 	}

}
