<?php

namespace ins0\GitHub;

/**
 * This class has a private method and can't be tested properly...
 */
class RepositoryTest extends TestSuite
{
	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage Invalid format. Required format is: ":username/:repository".
	 */
	public function testThrowsErrorIfRepositoryDetailsNotInCorrectFormat()
	{
		$repository = new Repository('github-changelog-generator');
	}
}
