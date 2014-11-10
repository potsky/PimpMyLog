<?php

class ParserTest extends TestCase {

	public function test_Read_Last_Lines()
    {
        $this->assertCount( 1 , LogParser::getLinesFromBottom( PHPMOCKUP . '/apache2.4_error.log' ) );
        $this->assertCount( 3 , LogParser::getLinesFromBottom( PHPMOCKUP . '/apache2.4_error.log' , 3 ) );
    }

}
