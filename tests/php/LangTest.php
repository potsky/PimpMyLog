<?php

class LangTest extends TestCase {

	public function test_Default_Lang()
    {
    	global $locale_default, $locale_available, $locale_numeraljs;

        $this->assertNotEmpty( $locale_available[ $locale_default ] );
        $this->assertNotEmpty( $locale_numeraljs[ $locale_default ] );
    }

    /**
     * Test if all numeral JS languages are in the PHP lang variables
     *
     * @return  [type]  [description]
     */
	public function test_NumeralJS_Languages_Exists()
    {
    	global $locale_default, $locale_available, $locale_numeraljs;

    	foreach ( $locale_numeraljs as $key => $value ) {
	        $this->assertNotEmpty( $locale_available[ $key ] );
    	}
    }

    /**
     * Test if all PHP languages are in the numeral JS lang variables
     *
     * @return  [type]  [description]
     */
	public function test_PHP_Languages_Exists()
    {
    	global $locale_default, $locale_available, $locale_numeraljs;

    	foreach ( $locale_available as $key => $value ) {
	        $this->assertNotEmpty( $locale_numeraljs[ $key ] );
    	}
    }
}
