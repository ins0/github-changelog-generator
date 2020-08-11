<?php declare(strict_types=1);

namespace ins0\GitHub;

use Generator;

/**
 * Mocks responses from GitHub's API. Used for testing purposes only.
 */
final class MockRepository extends Repository
{
    public function __construct(string $repository = '', string $token = null)
    {
        $this->releases = [];
        $this->issues = [];
        $this->issueEvents = [];
    }

    public function getReleases(array $params = []): Generator
    {
        yield from $this->releases;
    }

    public function getIssues(array $params = []): Generator
    {
        yield from $this->issues;
    }

    public function getIssueEvents(int $number): Generator
    {
        yield from $this->issueEvents[$number];
    }
}
