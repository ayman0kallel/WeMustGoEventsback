<?php

namespace App\Controller;

use App\Entity\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /**
     * @Route("/article", name="app_article", methods={"GET"})
     */
    public function all(): JsonResponse
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findAll();

        return $this->json($articles);
    }

    /**
     * @Route("/article/{id}", name="app_article_show", methods={"GET"})
     */
    public function show(int $id): JsonResponse
    {
        $article = $this->getDoctrine()->getRepository(Article::class)->find($id);

        if (!$article) {
            return $this->json(['message' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($article);
    }

    /**
     * @Route("/article", name="app_article_create", methods={"POST"})
     */
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $article = new Article();
        $article->setTitle($data['title']);
        $article->setDescription($data['description']);
        $article->setImage($data['image']);
        $article->setLocation($data['location']);
        $article->setDate(new \DateTime($data['date']));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($article);
        $entityManager->flush();

        return $this->json(['message' => 'Article created successfully'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/article/{id}", name="app_article_update", methods={"PUT"})
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $entityManager = $this->getDoctrine()->getManager();
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            return $this->json(['message' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $article->setTitle($data['title']);
        $article->setDescription($data['description']);
        $article->setLocation($data['location']);
        $article->setDate(new \DateTime($data['date']));

        $entityManager->flush();

        return $this->json(['message' => 'Article updated successfully']);
    }

    /**
     * @Route("/article/{id}", name="app_article_delete", methods={"DELETE"})
     */
    public function delete(int $id): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            return $this->json(['message' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($article);
        $entityManager->flush();

        return $this->json(['message' => 'Article deleted successfully']);
    }
}
