<?php

declare(strict_types=1);

/*
 * This file is part of the box project.
 *
 * (c) Kevin Herrera <kevin@herrera.io>
 *     Théo Fidry <theo.fidry@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace KevinGH\Box\Console\Command;

use Fidry\Console\Command\Command;
use Fidry\Console\Command\Configuration;
use Fidry\Console\ExitCode;
use Fidry\Console\Input\IO;
use KevinGH\Box\Console\PharInfoRenderer;
use KevinGH\Box\Phar\DiffMode;
use KevinGH\Box\Phar\PharDiff;
use KevinGH\Box\Phar\PharInfo;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;
use function array_map;
use function count;
use function explode;
use function implode;
use function is_string;
use function sprintf;
use const PHP_EOL;

/**
 * @private
 */
final class Diff implements Command
{
    private const FIRST_PHAR_ARG = 'pharA';
    private const SECOND_PHAR_ARG = 'pharB';

    private const LIST_FILES_DIFF_OPTION = 'list-diff';
    private const GIT_DIFF_OPTION = 'git-diff';
    private const GNU_DIFF_OPTION = 'gnu-diff';
    private const DIFF_OPTION = 'diff';
    private const CHECK_OPTION = 'check';

    private const DEFAULT_CHECKSUM_ALGO = 'sha384';

    public function getConfiguration(): Configuration
    {
        return new Configuration(
            'diff',
            '🕵  Displays the differences between all of the files in two PHARs',
            '',
            [
                new InputArgument(
                    self::FIRST_PHAR_ARG,
                    InputArgument::REQUIRED,
                    'The first PHAR',
                ),
                new InputArgument(
                    self::SECOND_PHAR_ARG,
                    InputArgument::REQUIRED,
                    'The second PHAR',
                ),
            ],
            [
                new InputOption(
                    self::GNU_DIFF_OPTION,
                    null,
                    InputOption::VALUE_NONE,
                    '(deprecated) Displays a GNU diff',
                ),
                new InputOption(
                    self::GIT_DIFF_OPTION,
                    null,
                    InputOption::VALUE_NONE,
                    '(deprecated) Displays a Git diff',
                ),
                new InputOption(
                    self::LIST_FILES_DIFF_OPTION,
                    null,
                    InputOption::VALUE_NONE,
                    '(deprecated) Displays a list of file names diff (default)',
                ),
                new InputOption(
                    self::DIFF_OPTION,
                    null,
                    InputOption::VALUE_REQUIRED,
                    sprintf(
                        'Displays a diff of the files. Available options are: "%s"',
                        implode(
                            '", "',
                            DiffMode::values(),
                        ),
                    ),
                    DiffMode::LIST->value,
                ),
                new InputOption(
                    self::CHECK_OPTION,
                    'c',
                    InputOption::VALUE_OPTIONAL,
                    'Verify the authenticity of the contents between the two PHARs with the given hash function',
                    self::DEFAULT_CHECKSUM_ALGO,
                ),
            ],
        );
    }

    public function execute(IO $io): int
    {
        $diff = new PharDiff(...self::getPaths($io));
        $diffMode = self::getDiffMode($io);

        $io->comment('<info>Comparing the two archives...</info>');

        $pharInfoA = $diff->getPharInfoA();
        $pharInfoB = $diff->getPharInfoB();

        if ($pharInfoA->equals($pharInfoB)) {
            $io->success('The two archives are identical.');

            return ExitCode::SUCCESS;
        }

        $this->compareArchives($diff, $io);
        $this->compareContents($diff, $diffMode, $io);

        return ExitCode::FAILURE;
    }

    /**
     * @return array{non-empty-string, non-empty-string}
     */
    private static function getPaths(IO $io): array
    {
        $paths = [
            $io->getArgument(self::FIRST_PHAR_ARG)->asNonEmptyString(),
            $io->getArgument(self::SECOND_PHAR_ARG)->asNonEmptyString(),
        ];

        Assert::allFile($paths);

        return array_map(
            static fn (string $path) => Path::canonicalize($path),
            $paths,
        );
    }

    private function compareArchives(PharDiff $diff, IO $io): void
    {
        $pharInfoA = $diff->getPharInfoA();
        $pharInfoB = $diff->getPharInfoB();

        self::renderArchive(
            $diff->getPharInfoA()->getFileName(),
            $pharInfoA,
            $io,
        );

        $io->newLine();

        self::renderArchive(
            $diff->getPharInfoB()->getFileName(),
            $pharInfoB,
            $io,
        );
    }

    private static function getDiffMode(IO $io): DiffMode
    {
        if ($io->getOption(self::GNU_DIFF_OPTION)->asBoolean()) {
            $io->writeln(
                sprintf(
                    '⚠️  <warning>Using the option "%s" is deprecated. Use "--%s=%s" instead.</warning>',
                    self::GNU_DIFF_OPTION,
                    self::DIFF_OPTION,
                    DiffMode::GNU->value,
                ),
            );

            return DiffMode::GNU;
        }

        if ($io->getOption(self::GIT_DIFF_OPTION)->asBoolean()) {
            $io->writeln(
                sprintf(
                    '⚠️  <warning>Using the option "%s" is deprecated. Use "--%s=%s" instead.</warning>',
                    self::GIT_DIFF_OPTION,
                    self::DIFF_OPTION,
                    DiffMode::GIT->value,
                ),
            );

            return DiffMode::GIT;
        }

        if ($io->getOption(self::LIST_FILES_DIFF_OPTION)->asBoolean()) {
            $io->writeln(
                sprintf(
                    '⚠️  <warning>Using the option "%s" is deprecated. Use "--%s=%s" instead.</warning>',
                    self::LIST_FILES_DIFF_OPTION,
                    self::DIFF_OPTION,
                    DiffMode::LIST->value,
                ),
            );

            return DiffMode::LIST;
        }

        return DiffMode::from($io->getOption(self::DIFF_OPTION)->asNonEmptyString());
    }

    private function compareContents(PharDiff $diff, DiffMode $diffMode, IO $io): void
    {
        $io->comment('<info>Comparing the two archives contents...</info>');

        $checkSumAlgorithm = $io->getOption(self::CHECK_OPTION)->asNullableNonEmptyString() ?? self::DEFAULT_CHECKSUM_ALGO;

        if ($io->hasOption('-c') || $io->hasOption('--check')) {
            $diff->listChecksums($checkSumAlgorithm);

            return;
        }

        $diffResult = $diff->diff($diffMode);

        if (null === $diffResult || [[], []] === $diffResult) {
            $io->success('The contents are identical');

            return;
        }

        if (is_string($diffResult)) {
            // Git or GNU diff: we don't have much control on the format
            $io->writeln($diffResult);

            return;
        }

        $io->writeln(sprintf(
            '--- Files present in "%s" but not in "%s"',
            $diff->getPharInfoA()->getFileName(),
            $diff->getPharInfoB()->getFileName(),
        ));
        $io->writeln(sprintf(
            '+++ Files present in "%s" but not in "%s"',
            $diff->getPharInfoB()->getFileName(),
            $diff->getPharInfoA()->getFileName(),
        ));

        $io->newLine();

        self::renderPaths('-', $diff->getPharInfoA(), $diffResult[0], $io);
        self::renderPaths('+', $diff->getPharInfoB(), $diffResult[1], $io);

        $io->error(sprintf(
            '%d file(s) difference',
            count($diffResult[0]) + count($diffResult[1]),
        ));
    }

    /**
     * @param list<non-empty-string> $paths
     */
    private static function renderPaths(string $symbol, PharInfo $pharInfo, array $paths, IO $io): void
    {
        $bufferedOutput = new BufferedOutput(
            $io->getVerbosity(),
            $io->isDecorated(),
            $io->getOutput()->getFormatter(),
        );

        PharInfoRenderer::renderContent(
            $bufferedOutput,
            $pharInfo,
            false,
            false,
        );

        $lines = array_map(
            static fn (string $line) => '' === $line ? '' : $symbol.' '.$line,
            explode(
                PHP_EOL,
                $bufferedOutput->fetch(),
            ),
        );

        $io->writeln($lines);
    }

    private static function renderArchive(string $fileName, PharInfo $pharInfo, IO $io): void
    {
        $io->writeln(
            sprintf(
                '<comment>Archive: </comment><fg=cyan;options=bold>%s</>',
                $fileName,
            ),
        );

        PharInfoRenderer::renderCompression($pharInfo, $io);
        PharInfoRenderer::renderSignature($pharInfo, $io);
        PharInfoRenderer::renderMetadata($pharInfo, $io);
        PharInfoRenderer::renderContentsSummary($pharInfo, $io);
        // TODO: checksum
    }
}
