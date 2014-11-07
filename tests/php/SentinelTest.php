<?php

class SentinelTest extends TestCase {

    public static function setUpBeforeClass() {
        Sentinel::destroy();
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
        Sentinel::destroy();
    }

    public function test_CRUD()
    {
        // Auth is not enabled
        $this->assertFalse( Sentinel::isAuthSet() );

        // Enable auth
        $this->assertTrue( Sentinel::create() );

        // Auth is enabled
        $this->assertTrue( Sentinel::isAuthSet() );

        // Create an admin
        $this->assertTrue( Sentinel::setAdmin( 'potsky' , 'thesky' ) );
        $potsky = Sentinel::getUser( 'potsky' );
        $this->assertInternalType( 'array' , $potsky );

        // Create a user
        $this->assertTrue( Sentinel::setUser( 'user' , 'thelooser' ) );
        $user   = Sentinel::getUser( 'user' );
        $this->assertInternalType( 'array' , $user );

        // Create a 2nd user
        $this->assertTrue( Sentinel::setUser( 'user2' , 'thelooser' ) );

        // Save on disk
        $this->assertTrue( Sentinel::save() );

        // Get an unknown user
        $this->assertNull( Sentinel::getUser( 'johndoe' ) );

        // Get all users
        $this->assertCount( 3 , Sentinel::getUsers() );
        $this->assertEquals( 3 , Sentinel::getUsersCount() );

        // Delete an existing user
        $this->assertInternalType( 'array' , Sentinel::deleteUser( 'user2' ) );

        // Delete an non existing user
        $this->assertFalse( Sentinel::deleteUser( 'dontexist' ) );

        // Reload file from disk
        $this->assertTrue( Sentinel::reload() );

        // Previous user was not deleted because not saved on disk !
        $this->assertCount( 3 , Sentinel::getUsers() );

        // Delete an existing user again but save now !
        $this->assertInternalType( 'array' , Sentinel::deleteUser( 'user2' ) );
        $this->assertTrue( Sentinel::save() );

        // Previous user was deleted !
        $this->assertCount( 2 , Sentinel::getUsers() );

    }

    public function test_SignInOut() {

        // Sign in with user admin
        $this->assertFalse( Sentinel::signIn( 'potsky' , 'cacaprout' ) );
        $loggedin = Sentinel::signIn( 'potsky' , 'thesky' );
        $this->assertInternalType( 'array' , $loggedin );

        // Verify user
        $this->assertEquals( 'potsky' , Sentinel::getCurrentUsername() );

        // Verify admin
        $this->assertTrue( Sentinel::isAdmin() );
        $this->assertTrue( Sentinel::userHasRole('admin') );
        $this->assertTrue( Sentinel::userHasRole('this_role_does_not_exists_but_admin_can_do') );

        // Logout
        $this->assertEquals( 'potsky' , Sentinel::signOut() );

        // Verify no user
        $this->assertNull( Sentinel::getCurrentUsername() );

        // Logout again for nothing
        $this->assertNull( Sentinel::signOut() );

        // Get last login informations
        $potsky = Sentinel::getUser('potsky');
        $this->assertEquals( 1 , $potsky['logincount'] );
        $this->assertInternalType( 'array' , $potsky['lastlogin'] );
    }

    public function test_Anonymous() {

        $this->assertFalse( Sentinel::isLogAnonymous( 'rick' ) );
        $this->assertFalse( Sentinel::isAnonymousEnabled() );

        $this->assertTrue( Sentinel::setLogAnonymous( 'rick' , true ) );
        $this->assertTrue( Sentinel::save() );

        $this->assertTrue( Sentinel::isLogAnonymous( 'rick' ) );
        $this->assertTrue( Sentinel::isAnonymousEnabled() );

        $this->assertTrue( Sentinel::setLogAnonymous( 'rick' , false ) );
        $this->assertTrue( Sentinel::save() );

        $this->assertFalse( Sentinel::isLogAnonymous( 'rick' ) );
        $this->assertFalse( Sentinel::isAnonymousEnabled() );
    }

}
