<?php declare(strict_types=1);
/**
 * @class Filter
 */
abstract class Filter {
	protected $valname;
	protected $label;
	protected $value = '';
	protected $auto_submit;
	abstract protected function getTemplate(): string;

	/**
	 * Generic getter
	 * @param string $property Property name
	 * @return mixed if the property exists, its current value
	 * @throws Exception non existing property
	 */
	public function __get( string $property ) {
		if( property_exists( $this, $property ) )
			return $this-> $property;

		throw new Exception( "no such property $property." );
	}	
	/**
	 * Filter
	 *
	 * @param  string $valname
	 * @param  string $label
	 * @param  bool $auto_submit
	 * @param  string  $default
	 * @return void
	 */
	public function __construct( string $valname, string $label, ?bool $auto_submit=false, ?string $default='' ) {
		$this-> valname = $valname;
		$this-> label = $label;
		$this-> auto_submit = $auto_submit;
		if( !$this-> parseValue() ) {
			$this-> value = $default;
		}
	}
		
	/**
	 * return section for autosubmit
	 *
	 * @return string
	 */
	protected function getAutoSubmit(): string {
		$auto_submit_template = " class=\"select-auto-submit\"";
		return $this-> auto_submit? $auto_submit_template: '';
	}
	
	/**
	 * Get a drop in where clause
	 *
	 * @param  string $fieldName field to compare with
	 * @return string
	 */
	public function getWhere( string $fieldName ): string {
		return "($fieldName='{$this-> value}')";
	}
	/**
	 * @method getHtml generate a html section based on template, value and valname
	 * @return string HTML section
	 */
	public function getHtml():string {
		return sprintf( $this-> getTemplate(),
			$this-> valname,
			$this-> label,
			$this-> value
		);
	}	
	/**
	 * @method initValue interpret value in $_POST
	 */
	private function parseValue() {
		if( array_key_exists( $this-> valname, $_POST ) && $_POST[$this-> valname]!=='') {
			$this->value = $_POST[$this-> valname];
			return true;
		}
		return false;
	}
}