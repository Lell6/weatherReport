<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;

require 'doctrine-config.php';

ConsoleRunner::run(
    new SingleManagerProvider($entityManager)
);