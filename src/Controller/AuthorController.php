<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
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
    public function createAuthor(Request $request, SerializerInterface $serializer, EntityManagerInterface $manager,
                                    UrlGeneratorInterface $urlGenerator) : JsonResponse
    {
        $author = $serializer->deserialize($request->getContent(), Author::class, 'json');
        $manager->persist($author);
        $manager->flush();
        $jsonAuthor = $serializer->serialize($author, 'json', ['groups' => 'getAuthors']);
        $location = $urlGenerator->generate('app_author_detail', ['id' => $author->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonAuthor, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/{id}', name: 'app_author_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getDetailAuthor(Author $author, SerializerInterface $serializer): JsonResponse
    {
        $jsonAuthor = $serializer->serialize($author, 'json', ['groups' => 'getAuthors']);
        return new JsonResponse($jsonAuthor, Response::HTTP_OK, [], true);
    }

    #[Route('/edit/{id}', name: 'app_author_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function updateAuthor(Request $request, SerializerInterface $serializer, EntityManagerInterface $manager,
                                    Author $currentAuthor)
    {
        $updatedAuthor = $serializer->deserialize($request->getContent(), Author::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAuthor]);
        $manager->persist($updatedAuthor);
        $manager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
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
