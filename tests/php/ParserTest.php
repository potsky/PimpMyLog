<?php

class ParserTest extends TestCase {

	public function test_Read_Last_Lines()
    {
        $this->assertCount( 1 , Parser::getLinesFromBottom( PHPMOCKUP . '/apache2.4_error.log' ) );
        $this->assertCount( 3 , Parser::getLinesFromBottom( PHPMOCKUP . '/apache2.4_error.log' , 3 ) );
    }

}
