<?php

namespace App\Service;

use App\Entity\Link;
use Doctrine\ORM\EntityManagerInterface;

class UrlShortenerService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function generateUniqueShortUrl(): string
    {
        do {
            $shortUrl = substr(md5(uniqid()), 0, 6);
            $existingLink = $this->entityManager->getRepository(Link::class)->findOneBy(['shortUrl' => $shortUrl]);
        } while ($existingLink !== null);

        return $shortUrl;
    }

    public function createLink(string $originalUrl, string $shortUrl): void
    {
        $link = new Link();
        $link->setOriginalUrl($originalUrl);
        $link->setShortUrl($shortUrl);

        $this->entityManager->persist($link);
        $this->entityManager->flush();
    }
}