<?php

namespace App\Tests\Service;

use App\Entity\Link;
use App\Service\UrlShortenerService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class UrlShortenerServiceTest extends TestCase
{
    private UrlShortenerService $urlShortenerService;
    private EntityManagerInterface $entityManager;
    private EntityRepository $linkRepository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->linkRepository = $this->createMock(EntityRepository::class);

        $this->entityManager->method('getRepository')
            ->willReturn($this->linkRepository);

        $this->urlShortenerService = new UrlShortenerService($this->entityManager);
    }

    public function testGenerateUniqueShortUrlReturnsUniqueUrl(): void
    {
        $shortUrl = 'abc123';
        $this->linkRepository->method('findOneBy')
            ->willReturn(null);

        $generatedShortUrl = $this->urlShortenerService->generateUniqueShortUrl();

        $this->assertIsString($generatedShortUrl);
        $this->assertNotEmpty($generatedShortUrl);
    }

    public function testCreateLinkStoresLinkInDatabase(): void
    {
        $originalUrl = 'http://example.com';
        $shortUrl = 'abc123';

        $this->linkRepository->method('findOneBy')
            ->willReturn(null);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Link::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->urlShortenerService->createLink($originalUrl, $shortUrl);
    }
}
