<?php


namespace App\Service;

use Symfony\Component\Finder\Finder;

class Storage
{
    private Finder $finder;

    public function __construct()
    {
        $this->finder = new Finder();
    }

    public function list(string $webPath) :Finder
    {
        $this->finder->files()->in($webPath);
        return $this->finder;
    }
}