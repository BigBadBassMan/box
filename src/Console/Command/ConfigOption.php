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

use Fidry\Console\Input\IO;
use KevinGH\Box\Configuration\Configuration;
use KevinGH\Box\Console\ConfigurationLoader;
use KevinGH\Box\Json\JsonValidationException;
use KevinGH\Box\NotInstantiable;
use Symfony\Component\Console\Input\InputOption;

/**
 * Allows a configuration file path to be specified for a command.
 *
 * @private
 */
final class ConfigOption
{
    use NotInstantiable;

    private const CONFIG_PARAM = 'config';

    public static function getOptionInput(): InputOption
    {
        return new InputOption(
            self::CONFIG_PARAM,
            'c',
            InputOption::VALUE_REQUIRED,
            'The alternative configuration file path.',
        );
    }

    /**
     * Returns the configuration settings.
     *
     * @param bool $allowNoFile Load the config nonetheless if not file is found when true
     *
     * @throws JsonValidationException
     */
    public static function getConfig(IO $io, bool $allowNoFile = false): Configuration
    {
        return ConfigurationLoader::getConfig(
            $io->getInput()->getOption(self::CONFIG_PARAM),
            $io,
            $allowNoFile,
        );
    }
}
