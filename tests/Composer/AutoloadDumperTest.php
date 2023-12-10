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

namespace KevinGH\Box\Composer;

use Humbug\PhpScoper\Symbol\SymbolsRegistry;
use PhpParser\Node\Name\FullyQualified;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNothing]
final class AutoloadDumperTest extends TestCase
{
    #[DataProvider('autoloadProvider')]
    public function test_it_can_generate_the_autoload(
        SymbolsRegistry $symbolsRegistry,
        array $excludedComposerAutoloadFileHashes,
        string $autoloadContents,
        string $expected,
    ): void {
        $actual = AutoloadDumper::generateAutoloadStatements(
            $symbolsRegistry,
            $excludedComposerAutoloadFileHashes,
            $autoloadContents,
        );

        self::assertSame($expected, $actual);
    }

    public static function autoloadProvider(): iterable
    {
        yield 'no symbols' => [
            new SymbolsRegistry(),
            [],
            <<<'PHP'
                <?php

                // autoload.php @generated by Composer

                if (PHP_VERSION_ID < 50600) {
                    if (!headers_sent()) {
                        header('HTTP/1.1 500 Internal Server Error');
                    }
                    $err = 'Composer 2.3.0 dropped support for autoloading on PHP <5.6 and you are running '.PHP_VERSION.', please upgrade PHP or use Composer 2.2 LTS via "composer self-update --2.2". Aborting.'.PHP_EOL;
                    if (!ini_get('display_errors')) {
                        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
                            fwrite(STDERR, $err);
                        } elseif (!headers_sent()) {
                            echo $err;
                        }
                    }
                    trigger_error(
                        $err,
                        E_USER_ERROR
                    );
                }

                require_once __DIR__ . '/composer/autoload_real.php';

                return ComposerAutoloaderInit2b848b33b146391fe6393b67efd7cd6f::getLoader();

                PHP,
            <<<'PHP'
                <?php

                // autoload.php @generated by Composer

                if (PHP_VERSION_ID < 50600) {
                    if (!headers_sent()) {
                        header('HTTP/1.1 500 Internal Server Error');
                    }
                    $err = 'Composer 2.3.0 dropped support for autoloading on PHP <5.6 and you are running '.PHP_VERSION.', please upgrade PHP or use Composer 2.2 LTS via "composer self-update --2.2". Aborting.'.PHP_EOL;
                    if (!ini_get('display_errors')) {
                        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
                            fwrite(STDERR, $err);
                        } elseif (!headers_sent()) {
                            echo $err;
                        }
                    }
                    trigger_error(
                        $err,
                        E_USER_ERROR
                    );
                }

                require_once __DIR__ . '/composer/autoload_real.php';

                return ComposerAutoloaderInit2b848b33b146391fe6393b67efd7cd6f::getLoader();

                PHP,
        ];

        yield 'global symbols' => [
            self::createRegistry(
                [
                    'bar' => 'Humbug\bar',
                    'foo' => 'Humbug\foo',
                ],
                [],
            ),
            [],
            <<<'PHP'
                <?php

                // @generated by Humbug Box

                // autoload.php @generated by Composer

                if (PHP_VERSION_ID < 50600) {
                    if (!headers_sent()) {
                        header('HTTP/1.1 500 Internal Server Error');
                    }
                    $err = 'Composer 2.3.0 dropped support for autoloading on PHP <5.6 and you are running '.PHP_VERSION.', please upgrade PHP or use Composer 2.2 LTS via "composer self-update --2.2". Aborting.'.PHP_EOL;
                    if (!ini_get('display_errors')) {
                        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
                            fwrite(STDERR, $err);
                        } elseif (!headers_sent()) {
                            echo $err;
                        }
                    }
                    trigger_error(
                        $err,
                        E_USER_ERROR
                    );
                }

                require_once __DIR__ . '/composer/autoload_real.php';

                return ComposerAutoloaderInit2b848b33b146391fe6393b67efd7cd6f::getLoader();

                PHP,
            <<<'PHP'
                <?php

                // @generated by Humbug Box

                $loader = (static function () {
                    // Backup the autoloaded Composer files
                    $existingComposerAutoloadFiles = $GLOBALS['__composer_autoload_files'] ?? [];

                    // @generated by Humbug Box

                    // autoload.php @generated by Composer

                    if (PHP_VERSION_ID < 50600) {
                        if (!headers_sent()) {
                            header('HTTP/1.1 500 Internal Server Error');
                        }
                        $err = 'Composer 2.3.0 dropped support for autoloading on PHP <5.6 and you are running '.PHP_VERSION.', please upgrade PHP or use Composer 2.2 LTS via "composer self-update --2.2". Aborting.'.PHP_EOL;
                        if (!ini_get('display_errors')) {
                            if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
                                fwrite(STDERR, $err);
                            } elseif (!headers_sent()) {
                                echo $err;
                            }
                        }
                        trigger_error(
                            $err,
                            E_USER_ERROR
                        );
                    }

                    require_once __DIR__ . '/composer/autoload_real.php';

                    $loader = ComposerAutoloaderInit2b848b33b146391fe6393b67efd7cd6f::getLoader();

                    // Ensure InstalledVersions is available
                    $installedVersionsPath = __DIR__.'/composer/InstalledVersions.php';
                    if (file_exists($installedVersionsPath)) require_once $installedVersionsPath;

                    // Restore the backup and ensure the excluded files are properly marked as loaded
                    $GLOBALS['__composer_autoload_files'] = \array_merge(
                        $existingComposerAutoloadFiles,
                        \array_fill_keys([], true)
                    );

                    return $loader;
                })();

                // Function aliases. For more information see:
                // https://github.com/humbug/php-scoper/blob/master/docs/further-reading.md#function-aliases
                if (!function_exists('bar')) { function bar() { return \Humbug\bar(...func_get_args()); } }
                if (!function_exists('foo')) { function foo() { return \Humbug\foo(...func_get_args()); } }

                return $loader;

                PHP,
        ];

        yield 'global symbols with file hashes' => [
            self::createRegistry(
                [
                    'bar' => 'Humbug\bar',
                    'foo' => 'Humbug\foo',
                ],
                [],
            ),
            ['a610a8e036135f992c6edfb10ca9f4e9', 'e252736c6babb7c097ab6692dbcb2a5a'],
            <<<'PHP'
                <?php

                // @generated by Humbug Box

                // autoload.php @generated by Composer

                if (PHP_VERSION_ID < 50600) {
                    if (!headers_sent()) {
                        header('HTTP/1.1 500 Internal Server Error');
                    }
                    $err = 'Composer 2.3.0 dropped support for autoloading on PHP <5.6 and you are running '.PHP_VERSION.', please upgrade PHP or use Composer 2.2 LTS via "composer self-update --2.2". Aborting.'.PHP_EOL;
                    if (!ini_get('display_errors')) {
                        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
                            fwrite(STDERR, $err);
                        } elseif (!headers_sent()) {
                            echo $err;
                        }
                    }
                    trigger_error(
                        $err,
                        E_USER_ERROR
                    );
                }

                require_once __DIR__ . '/composer/autoload_real.php';

                return ComposerAutoloaderInit2b848b33b146391fe6393b67efd7cd6f::getLoader();

                PHP,
            <<<'PHP'
                <?php

                // @generated by Humbug Box

                $loader = (static function () {
                    // Backup the autoloaded Composer files
                    $existingComposerAutoloadFiles = $GLOBALS['__composer_autoload_files'] ?? [];

                    // @generated by Humbug Box

                    // autoload.php @generated by Composer

                    if (PHP_VERSION_ID < 50600) {
                        if (!headers_sent()) {
                            header('HTTP/1.1 500 Internal Server Error');
                        }
                        $err = 'Composer 2.3.0 dropped support for autoloading on PHP <5.6 and you are running '.PHP_VERSION.', please upgrade PHP or use Composer 2.2 LTS via "composer self-update --2.2". Aborting.'.PHP_EOL;
                        if (!ini_get('display_errors')) {
                            if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
                                fwrite(STDERR, $err);
                            } elseif (!headers_sent()) {
                                echo $err;
                            }
                        }
                        trigger_error(
                            $err,
                            E_USER_ERROR
                        );
                    }

                    require_once __DIR__ . '/composer/autoload_real.php';

                    $loader = ComposerAutoloaderInit2b848b33b146391fe6393b67efd7cd6f::getLoader();

                    // Ensure InstalledVersions is available
                    $installedVersionsPath = __DIR__.'/composer/InstalledVersions.php';
                    if (file_exists($installedVersionsPath)) require_once $installedVersionsPath;

                    // Restore the backup and ensure the excluded files are properly marked as loaded
                    $GLOBALS['__composer_autoload_files'] = \array_merge(
                        $existingComposerAutoloadFiles,
                        \array_fill_keys(['a610a8e036135f992c6edfb10ca9f4e9', 'e252736c6babb7c097ab6692dbcb2a5a'], true)
                    );

                    return $loader;
                })();

                // Function aliases. For more information see:
                // https://github.com/humbug/php-scoper/blob/master/docs/further-reading.md#function-aliases
                if (!function_exists('bar')) { function bar() { return \Humbug\bar(...func_get_args()); } }
                if (!function_exists('foo')) { function foo() { return \Humbug\foo(...func_get_args()); } }

                return $loader;

                PHP,
        ];

        yield 'namespaced symbols' => [
            self::createRegistry(
                [
                    'Acme\bar' => 'Humbug\Acme\bar',
                    'Acme\foo' => 'Humbug\Acme\foo',
                    'Emca\baz' => 'Humbug\Emca\baz',
                ],
                [],
            ),
            [],
            <<<'PHP'
                <?php

                // @generated by Humbug Box

                // autoload.php @generated by Composer

                if (PHP_VERSION_ID < 50600) {
                    if (!headers_sent()) {
                        header('HTTP/1.1 500 Internal Server Error');
                    }
                    $err = 'Composer 2.3.0 dropped support for autoloading on PHP <5.6 and you are running '.PHP_VERSION.', please upgrade PHP or use Composer 2.2 LTS via "composer self-update --2.2". Aborting.'.PHP_EOL;
                    if (!ini_get('display_errors')) {
                        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
                            fwrite(STDERR, $err);
                        } elseif (!headers_sent()) {
                            echo $err;
                        }
                    }
                    trigger_error(
                        $err,
                        E_USER_ERROR
                    );
                }

                require_once __DIR__ . '/composer/autoload_real.php';

                return ComposerAutoloaderInit2b848b33b146391fe6393b67efd7cd6f::getLoader();

                PHP,
            <<<'PHP'
                <?php

                // @generated by Humbug Box

                namespace {
                    $loader = (static function () {
                        // Backup the autoloaded Composer files
                        $existingComposerAutoloadFiles = $GLOBALS['__composer_autoload_files'] ?? [];

                        // @generated by Humbug Box

                        // autoload.php @generated by Composer

                        if (PHP_VERSION_ID < 50600) {
                            if (!headers_sent()) {
                                header('HTTP/1.1 500 Internal Server Error');
                            }
                            $err = 'Composer 2.3.0 dropped support for autoloading on PHP <5.6 and you are running '.PHP_VERSION.', please upgrade PHP or use Composer 2.2 LTS via "composer self-update --2.2". Aborting.'.PHP_EOL;
                            if (!ini_get('display_errors')) {
                                if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
                                    fwrite(STDERR, $err);
                                } elseif (!headers_sent()) {
                                    echo $err;
                                }
                            }
                            trigger_error(
                                $err,
                                E_USER_ERROR
                            );
                        }

                        require_once __DIR__ . '/composer/autoload_real.php';

                        $loader = ComposerAutoloaderInit2b848b33b146391fe6393b67efd7cd6f::getLoader();

                        // Ensure InstalledVersions is available
                        $installedVersionsPath = __DIR__.'/composer/InstalledVersions.php';
                        if (file_exists($installedVersionsPath)) require_once $installedVersionsPath;

                        // Restore the backup and ensure the excluded files are properly marked as loaded
                        $GLOBALS['__composer_autoload_files'] = \array_merge(
                            $existingComposerAutoloadFiles,
                            \array_fill_keys([], true)
                        );

                        return $loader;
                    })();
                }

                // Function aliases. For more information see:
                // https://github.com/humbug/php-scoper/blob/master/docs/further-reading.md#function-aliases
                namespace Acme {
                    if (!function_exists('Acme\bar')) { function bar() { return \Humbug\Acme\bar(...func_get_args()); } }
                    if (!function_exists('Acme\foo')) { function foo() { return \Humbug\Acme\foo(...func_get_args()); } }
                }

                namespace Emca {
                    if (!function_exists('Emca\baz')) { function baz() { return \Humbug\Emca\baz(...func_get_args()); } }
                }

                namespace {
                    return $loader;
                }

                PHP,
        ];
    }

    /**
     * @param array<string, string> $functions
     * @param array<string, string> $classes
     */
    private static function createRegistry(
        array $functions,
        array $classes
    ): SymbolsRegistry {
        $registry = new SymbolsRegistry();

        foreach ($functions as $original => $alias) {
            $registry->recordFunction(
                new FullyQualified($original),
                new FullyQualified($alias),
            );
        }

        foreach ($classes as $original => $alias) {
            $registry->recordClass(
                new FullyQualified($original),
                new FullyQualified($alias),
            );
        }

        return $registry;
    }
}
