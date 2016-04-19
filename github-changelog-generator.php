<?php

use ins0\GitHub\Repository;
use ins0\Github\ChangelogGenerator;
use RuntimeException;

/**
 * Provides a backwords compatible wrapper for the new changelog generator.
 *
 * @deprecated This class is deprecated and SHOULD NOT be used.
 * @author Marco Rieger (ins0) (Original Author)
 * @author Nathan Bishop (nbish11) (Contributor and Refactorer)
 * @copyright (c) 2015 Marco Rieger
 * @license MIT
 */
class GithubChangelogGenerator
{
    /**
     * The default file to write to.
     *
     * @var string
     */
    const DEFAULT_FILENAME = 'CHANGELOG.md';

    /**
     * @var string
     */
    const LABEL_TYPE_BUG = 'type_bug';

    /**
     * @var string
     */
    const LABEL_TYPE_FEATURE = 'type_feature';

    /**
     * @var string
     */
    const LABEL_TYPE_PR = 'type_pr';

    /**
     * The OAUTH token.
     *
     * @var string
     */
    private $token;

    /**
     * The file we're writing the changelog to.
     *
     * @var string
     */
    private $fileName;

    /**
     * The sub-heading to issue label mapping.
     *
     * @var array
     */
    private $issueLabelMapping = [
        self::LABEL_TYPE_BUG => [
            'bug',
        ],
        self::LABEL_TYPE_FEATURE => [
            'enhancement',
            'feature',
        ]
    ];

    /**
     * Constructs an instance.
     *
     * @param string $token        Your GitHub OAUTH token. This is not required, but is recommended.
     * @param array  $issueMapping Customize your issue's labels instead of using the default "bug",
     *                             "enhancement" and "feature" labels.
     */
    public function __construct($token = null, $issueMapping = null)
    {
        if ($issueMapping) {
            $this->issueLabelMapping = $issueMapping;
        }

        $this->token = $token;
        $this->fileName = self::DEFAULT_FILENAME;
    }

    /**
     * Create a changelog file for the requested repository.
     *
     * @param string $user       The owner of the repository. Typically this is your GitHub username.
     * @param string $repository Your GitHub repository you would like to generate the changelog file for.
     * @param string $savePath   The path to the file. Defaults to current directory of this file.
     */
    public function createChangelog($user, $repository, $savePath = null)
    {
        $repository = new Repository("{$user}/{$repository}", $this->token);
        $changelogGenerator = new ChangelogGenerator($repository, $this->issueLabelMapping);
        $savePath = !$savePath ? dirname(__FILE__) . '/' . $this->fileName : null;
        $file = fopen($savePath, 'w');

        if (!$file) {
            throw new RuntimeException(sprintf('Could not open file for writing to: %s', $savePath));
        }

        fwrite($file, $changelogGenerator->generate());
        fclose($file);
    }
}
