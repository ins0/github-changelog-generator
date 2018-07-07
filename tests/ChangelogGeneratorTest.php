<?php declare(strict_types=1);

namespace ins0\GitHub;

class ChangelogGeneratorTest extends TestSuite
{
    /**
     * @expectedException        Exception
     * @expectedExceptionMessage No releases found for this repository
     */
    public function testThrowsExceptionIfNoReleasesAreFound()
    {
        $mockRepository = new MockRepository();
        $changelogGenerator = new ChangelogGenerator($mockRepository);

        $changelog = $changelogGenerator->generate();
    }

    public function testThatAReleaseWithNoIssuesGeneratesAnEmptyChangelog()
    {
        $mockRepository = new MockRepository();
        $mockRepository->releases = $this->loadFixtureData('releases');
        $changelogGenerator = new ChangelogGenerator($mockRepository);

        $this->assertEquals(
            $this->loadFile('output/release-with-no-sections.md'),
            $changelogGenerator->generate()
        );
    }

    public function testIssuesAreOnlyPinnedToReleaseTags()
    {
        $mockRepository = $this->getMockRepositoryWithIssues($this->loadFixtureData('issues'));
        $changelogGenerator = new ChangelogGenerator($mockRepository);

        $this->assertEquals(
            $this->loadFile('output/release-with-all-sections.md'),
            $changelogGenerator->generate()
        );
    }

    public function testGeneratesAChangedSectionUnderRelease()
    {
        // Only test issues 1 and 2 (tagged with 'enhancement')
        $issues = $this->loadFixtureData('issues');
        $mockRepository = $this->getMockRepositoryWithIssues([$issues[3], $issues[4]]);
        $changelogGenerator = new ChangelogGenerator($mockRepository);

        $this->assertEquals(
            $this->loadFile('output/release-with-changed-only-section.md'),
            $changelogGenerator->generate()
        );
    }

    public function testGeneratesAnAddedSectionUnderRelease()
    {
        // Only test issue 3 (tagged with 'feature')
        $mockRepository = $this->getMockRepositoryWithIssues([$this->loadFixtureData('issues')[2]]);
        $changelogGenerator = new ChangelogGenerator($mockRepository);

        $this->assertEquals(
            $this->loadFile('output/release-with-added-only-section.md'),
            $changelogGenerator->generate()
        );
    }

    public function testGeneratesAPullRequestsSectionUnderRelease()
    {
        // Only test issue 4 (no tags, marked as a 'pull request')
        $mockRepository = $this->getMockRepositoryWithIssues([$this->loadFixtureData('issues')[1]]);
        $changelogGenerator = new ChangelogGenerator($mockRepository);

        $this->assertEquals(
            $this->loadFile('output/release-with-pull-requests-only-section.md'),
            $changelogGenerator->generate()
        );
    }

    public function testGeneratesAFixedSectionUnderRelease()
    {
        // Only test issue 5 (tagged with 'bug')
        $mockRepository = $this->getMockRepositoryWithIssues([$this->loadFixtureData('issues')[0]]);
        $changelogGenerator = new ChangelogGenerator($mockRepository);

        $this->assertEquals(
            $this->loadFile('output/release-with-fixed-only-section.md'),
            $changelogGenerator->generate()
        );
    }

    public function testCanChooseCustomLabelForChangedSection()
    {
        $issues = $this->loadFixtureData('issues-with-custom-labels');
        $issueMappings = [ChangelogGenerator::LABEL_TYPE_CHANGED => ['CustomEnhancementLabel']];
        $mockRepository = $this->getMockRepositoryWithIssues([$issues[2], $issues[3]]);
        $changelogGenerator = new ChangelogGenerator($mockRepository, $issueMappings);

        $this->assertEquals(
            $this->loadFile('output/release-with-changed-only-section.md'),
            $changelogGenerator->generate()
        );
    }

    public function testCanChooseCustomLabelForAddedSection()
    {
        $issues = $this->loadFixtureData('issues-with-custom-labels');
        $issueMappings = [ChangelogGenerator::LABEL_TYPE_ADDED => ['CustomFeatureLabel']];
        $mockRepository = $this->getMockRepositoryWithIssues([$issues[1]]);
        $changelogGenerator = new ChangelogGenerator($mockRepository, $issueMappings);

        $this->assertEquals(
            $this->loadFile('output/release-with-added-only-section.md'),
            $changelogGenerator->generate()
        );
    }

    public function testCanChooseCustomLabelForFixedSection()
    {
        $issues = $this->loadFixtureData('issues-with-custom-labels');
        $issueMappings = [ChangelogGenerator::LABEL_TYPE_FIXED => ['CustomBugLabel']];
        $mockRepository = $this->getMockRepositoryWithIssues([$issues[0]]);
        $changelogGenerator = new ChangelogGenerator($mockRepository, $issueMappings);

        $this->assertEquals(
            $this->loadFile('output/release-with-fixed-only-section.md'),
            $changelogGenerator->generate()
        );
    }

    public function testCanOverrideTypeHeader()
    {
        $issues = $this->loadFixtureData('issues');
        $typeHeaders = [ChangelogGenerator::LABEL_TYPE_FIXED => '### I fixed it!'];
        $mockRepository = $this->getMockRepositoryWithIssues([$issues[0], $issues[3]]);
        $changelogGenerator = new ChangelogGenerator($mockRepository, [], $typeHeaders);

        $this->assertEquals(
            $this->loadFile('output/release-with-overriden-section-header.md'),
            $changelogGenerator->generate()
        );
    }

    public function testCanChooseCustomTypeHeader()
    {
        $issues = $this->loadFixtureData('issues-with-custom-labels');
        $issueMappings = ['custom' => ['CustomBugLabel']];
        $typeHeaders = ['custom' => '### Custom Header'];
        $mockRepository = $this->getMockRepositoryWithIssues([$issues[0]]);
        $changelogGenerator = new ChangelogGenerator($mockRepository, $issueMappings, $typeHeaders);

        $this->assertEquals(
            $this->loadFile('output/release-with-custom-sections.md'),
            $changelogGenerator->generate()
        );
    }

    private function getMockRepositoryWithIssues(array $issues)
    {
        $mockRepository = new MockRepository();
        $mockRepository->releases = $this->loadFixtureData('releases');
        $mockRepository->issues = $issues;
        $mockRepository->issueEvents = [
            1 => $this->loadFixtureData('issue1-events'),
            2 => $this->loadFixtureData('issue2-events'),
            3 => $this->loadFixtureData('issue3-events'),
            4 => $this->loadFixtureData('issue4-events'),
            5 => $this->loadFixtureData('issue5-events')
        ];

        return $mockRepository;
    }

    private function loadFixtureData($fixture)
    {
        return json_decode($this->loadFile("fixtures/{$fixture}.json"));
    }

    private function loadFile($file)
    {
        return file_get_contents(__DIR__ . "/{$file}");
    }
}
