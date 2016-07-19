<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Services\Connections\Connections;

use Closure;
use League\Flysystem\Adapter\Local;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;
use Symfony\Component\Process\Process;

/**
 * Stub of local connections to make Rocketeer work
 * locally when necessary.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class LocalConnection extends AbstractConnection
{
    /**
     * Return status of the last command.
     *
     * @var int
     */
    protected $previousStatus;

    /**
     * LocalConnection constructor.
     */
    public function __construct()
    {
        parent::__construct(new Local('/', LOCK_EX, Local::SKIP_LINKS));
    }

    /**
     * Run a set of commands against the connection.
     *
     * @param string|array $commands
     * @param Closure|null $callback
     */
    public function run($commands, Closure $callback = null)
    {
        $commands = (array) $commands;
        $commands = implode(' && ', $commands);

        $this->previousStatus = 0;
        $process = new Process($commands);
        $process->run(function ($type, $line) use ($callback) {
            $callback($line);
        });

        $this->previousStatus = $process->getExitCode();
    }

    /**
     * Get the exit status of the last command.
     *
     * @return int
     */
    public function status()
    {
        return $this->previousStatus;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return true;
    }

    /**
     * @return ConnectionKey
     */
    public function getConnectionKey()
    {
        return new ConnectionKey([
            'name' => 'local',
            'server' => 0,
        ]);
    }
}
