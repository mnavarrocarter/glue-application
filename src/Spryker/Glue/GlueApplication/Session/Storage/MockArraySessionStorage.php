<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\GlueApplication\Session\Storage;

use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

/**
 * @deprecated Will be removed without replacement.
 *
 * This class is used as workaround for Clients which depend on session, this will provide in memory storage that means after request complected it's discarded.
 * When using SessionClient within GLUE application context, it will use this storage.
 */
class MockArraySessionStorage implements SessionStorageInterface
{
    /**
     * @var string
     */
    protected $id = '';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $started = false;

    /**
     * @var bool
     */
    protected $closed = false;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag
     */
    protected static $metadataBag;

    /**
     * @var array
     */
    protected static $bags;

    /**
     * @param string $name Session name
     * @param \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag|null $metaBag MetadataBag instance
     */
    public function __construct($name = 'MOCKSESSID', ?MetadataBag $metaBag = null)
    {
        $this->name = $name;
        $this->setMetadataBag($metaBag);
    }

    /**
     * Sets the session data.
     *
     * @param array $array
     *
     * @return void
     */
    public function setSessionData(array $array)
    {
        $this->data = $array;
    }

    /**
     * @inheritDoc
     */
    public function start()
    {
        if ($this->started) {
            return true;
        }

        if (!$this->id) {
            $this->id = $this->generateId();
        }

        $this->loadSession();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function regenerate($destroy = false, $lifetime = null)
    {
        if (!$this->started) {
            $this->start();
        }

        static::$metadataBag->stampNew($lifetime);
        $this->id = $this->generateId();

        return true;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @throws \LogicException
     *
     * @return void
     */
    public function setId($id)
    {
        if ($this->started) {
            throw new LogicException('Cannot set session ID after the session has started.');
        }

        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    public function save()
    {
        if (!$this->started || $this->closed) {
            throw new RuntimeException('Trying to save a session that was not started yet or was already closed');
        }
        // nothing to do since we don't persist the session data
        $this->closed = false;
        $this->started = false;
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function clear()
    {
        // clear out the bags
        foreach (static::$bags as $bag) {
            $bag->clear();
        }

        // clear out the session
        $this->data = [];

        // reconnect the bags to the session
        $this->loadSession();
    }

    /**
     * {@inheritDoc}
     *
     * @param \Symfony\Component\HttpFoundation\Session\SessionBagInterface $bag
     *
     * @return void
     */
    public function registerBag(SessionBagInterface $bag)
    {
        static::$bags[$bag->getName()] = $bag;
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return \Symfony\Component\HttpFoundation\Session\SessionBagInterface|mixed
     */
    public function getBag($name)
    {
        if (!isset(static::$bags[$name])) {
            throw new InvalidArgumentException(sprintf('The SessionBagInterface %s is not registered.', $name));
        }

        if (!$this->started) {
            $this->start();
        }

        return static::$bags[$name];
    }

    /**
     * @inheritDoc
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * Sets the MetadataBag.
     *
     * @param \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag|null $bag
     *
     * @return void
     */
    public function setMetadataBag(?MetadataBag $bag = null)
    {
        if ($bag === null) {
            $bag = new MetadataBag();
        }

        static::$metadataBag = $bag;
    }

    /**
     * Gets the MetadataBag.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag
     */
    public function getMetadataBag()
    {
        return static::$metadataBag;
    }

    /**
     * Generates a session ID.
     *
     * This doesn't need to be particularly cryptographically secure since this is just
     * a mock.
     *
     * @return string
     */
    protected function generateId()
    {
        return hash('sha256', uniqid('ss_mock_', true));
    }

    /**
     * @return void
     */
    protected function loadSession()
    {
        $bags = array_merge(static::$bags, [static::$metadataBag]);

        foreach ($bags as $bag) {
            $key = $bag->getStorageKey();
            $this->data[$key] = $this->data[$key] ?? [];
            $bag->initialize($this->data[$key]);
        }

        $this->started = true;
        $this->closed = false;
    }
}
