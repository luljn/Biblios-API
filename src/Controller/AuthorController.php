<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/authors')]
class AuthorController extends AbstractController
{
    #[Route('', name: 'app_author', methods: ['GET'])]
    public function getAllAuthors(AuthorRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $authorList = $repository->findAll();
        $jsonAuthorList = $serializer->serialize($authorList, 'json', ['groups' => 'getAuthors']);
        return new JsonResponse($jsonAuthorList, Response::HTTP_OK, [], true);
    }

    #[Route('/add', name: 'app_author_create', methods: ['POST'])]
    public function createAuthor()
    {

    }

    #[Route('/{id}', name: 'app_author_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getDetailAuthor(Author $author, SerializerInterface $serializer): JsonResponse
    {
        $jsonAuthor = $serializer->serialize($author, 'json', ['groups' => 'getAuthors']);
        return new JsonResponse($jsonAuthor, Response::HTTP_OK, [], true);
    }

    #[Route('/edit/{id}', name: 'app_author_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function updateAuthor()
    {

    }

    #[Route('/delete/{id}', name: 'app_author_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteAuthor(Author $author, EntityManagerInterface $manager) : JsonResponse
    {
        $manager->remove($author);
        $manager->flush();
        dd($author->getBooks());
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
