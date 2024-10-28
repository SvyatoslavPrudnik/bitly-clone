<?php

namespace App\Controller;

use App\Entity\Link;
use App\Service\UrlShortenerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class UrlShortenerController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('link/index.html.twig');
    }

    #[Route('/shorten', name: 'shorten', methods: ['POST'])]
    public function shorten(Request $request, UrlShortenerService $urlShortenerService): Response
    {
        $originalUrl = $request->request->get('url');
        $shortUrl = $urlShortenerService->generateUniqueShortUrl();
        $urlShortenerService->createLink($originalUrl, $shortUrl);

        return $this->render('link/shortened.html.twig', [
            'shortUrl' => $this->generateUrl('redirect', ['shortUrl' => $shortUrl], true)
        ]);
    }

    #[Route('/go/{shortUrl}', name: 'redirect')]
    public function redirectToOriginal(string $shortUrl, EntityManagerInterface $em): Response
    {
        $link = $em->getRepository(Link::class)->findOneBy(['shortUrl' => $shortUrl]);

        if (!$link) {
            throw $this->createNotFoundException('Short URL not found');
        }

        $link->incrementClickCount();
        $this->entityManager->flush();

        return new RedirectResponse($link->getOriginalUrl());
    }

    #[Route('/links', name: 'all_links')]
    public function allLinks(): Response
    {
        $links = $this->entityManager->getRepository(Link::class)->findAll();
        return $this->render('link/all_links.html.twig', ['links' => $links]);
    }
}
