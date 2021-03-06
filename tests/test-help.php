<?php

use WP_CLI\Utils;

require_once dirname( __DIR__ ) . '/php/class-wp-cli.php';
require_once dirname( __DIR__ ) . '/php/class-wp-cli-command.php';
require_once dirname( __DIR__ ) . '/php/commands/help.php';

class HelpTest extends PHPUnit_Framework_TestCase {

	public function testPassThroughPagerProcDisabled() {
		$err_msg = 'Warning: check_proc_available() failed in pass_through_pager().';

		$cmd = 'php -ddisable_functions=proc_open php/boot-fs.php help --debug 2>&1';
		$output = array();
		exec( $cmd, $output );
		$output = trim( implode( "\n", $output ) );
		$this->assertTrue( false !== strpos( $output, $err_msg ) );

		$cmd = 'php -ddisable_functions=proc_close php/boot-fs.php help --debug 2>&1';
		$output = array();
		exec( $cmd, $output );
		$output = trim( implode( "\n", $output ) );
		$this->assertTrue( false !== strpos( $output, $err_msg ) );
	}

	public function test_parse_reference_links() {
		$test_class = new ReflectionClass( 'Help_Command' );
		$method = $test_class->getMethod( 'parse_reference_links' );
		$method->setAccessible( true );

		$desc = 'This is a [reference link](https://wordpress.org/). It should be displayed very nice!';
		$result = $method->invokeArgs( null, array( $desc ) );

		$expected =<<<EOL
This is a [reference link][1]. It should be displayed very nice!

---
[1] https://wordpress.org/
EOL;
		$this->assertSame( $expected, $result );

		$desc = 'This is a [reference link](https://wordpress.org/) and [second link](http://wp-cli.org/). It should be displayed very nice!';
		$result = $method->invokeArgs( null, array( $desc ) );

		$expected =<<<EOL
This is a [reference link][1] and [second link][2]. It should be displayed very nice!

---
[1] https://wordpress.org/
[2] http://wp-cli.org/
EOL;
		$this->assertSame( $expected, $result );

		$desc =<<<EOL
This is a [reference link](https://wordpress.org/) and [second link](http://wp-cli.org/).
It should be displayed very nice!
EOL;
		$result = $method->invokeArgs( null, array( $desc ) );

		$expected =<<<EOL
This is a [reference link][1] and [second link][2].
It should be displayed very nice!

---
[1] https://wordpress.org/
[2] http://wp-cli.org/
EOL;

		$this->assertSame( $expected, $result );

		$desc =<<<EOL
This is a [reference link](https://wordpress.org/) and [second link](http://wp-cli.org/).
It should be displayed very nice!

## Example

It doesn't expect to be link here like [reference link](https://wordpress.org/).
EOL;
		$result = $method->invokeArgs( null, array( $desc ) );

		$expected =<<<EOL
This is a [reference link][1] and [second link][2].
It should be displayed very nice!

---
[1] https://wordpress.org/
[2] http://wp-cli.org/

## Example

It doesn't expect to be link here like [reference link](https://wordpress.org/).
EOL;

		$this->assertSame( $expected, $result );

		$desc =<<<EOL
## Example

It doesn't expect to be link here like [reference link](https://wordpress.org/).
EOL;
		$result = $method->invokeArgs( null, array( $desc ) );

		$expected =<<<EOL
## Example

It doesn't expect to be link here like [reference link](https://wordpress.org/).
EOL;

		$this->assertSame( $expected, $result );

		$desc =<<<EOL
This is a long description.
It doesn't have any link.

## Example

It doesn't expect to be link here like [reference link](https://wordpress.org/).
EOL;
		$result = $method->invokeArgs( null, array( $desc ) );

		$expected =<<<EOL
This is a long description.
It doesn't have any link.

## Example

It doesn't expect to be link here like [reference link](https://wordpress.org/).
EOL;

		$this->assertSame( $expected, $result );
	}
}
