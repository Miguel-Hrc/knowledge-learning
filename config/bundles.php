<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ($_ENV['USE_MONGODB'] ?? 'false') !== 'true' ? ['all' => true] : [],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ($_ENV['USE_MONGODB'] ?? 'false') !== 'true' ? ['all' => true] : [],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    SymfonyCasts\Bundle\VerifyEmail\SymfonyCastsVerifyEmailBundle::class => ['all' => true],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ($_ENV['USE_MONGODB'] ?? 'false') !== 'true' ? ['all' => true] : [],
    SymfonyCasts\Bundle\ResetPassword\SymfonyCastsResetPasswordBundle::class => ['all' => true],
    Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle::class => ($_ENV['USE_MONGODB'] ?? 'false') === 'true' ? ['all' => true] : [],];
