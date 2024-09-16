<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/books')]
class BookController extends AbstractController
{
    #[Route('', name: 'app_book', methods: ['GET'])]
    public function getAllBooks(BookRepository $bookRepository, SerializerInterface $serializer): JsonResponse
    {
        $bookList = $bookRepository->findAll();
        
        $jsonBookList = $serializer->serialize($bookList, 'json', ['groups' => 'getBooks']);
        return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
    }

    #[Route('/add', name: 'app_book_create', methods: ['POST'])]
    public function createBook(Request $request, SerializerInterface $serializer, EntityManagerInterface $manager,
                                UrlGeneratorInterface $urlGenerator, AuthorRepository $authorRepository, ValidatorInterface $validator): JsonResponse
    {
        $book = $serializer->deserialize($request->getContent(), Book::class, 'json');
        
        $content = $request->toArray();
        $idAuthor = $content['idAuthor'] ?? -1;

        $book->setAuthor($authorRepository->find($idAuthor));

        $errors = $validator->validate($book);
        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $manager->persist($book);
        $manager->flush();

        $jsonBook = $serializer->serialize($book, 'json', ['groups' => 'getBooks']);

        $location = $urlGenerator->generate('app_book_detail', ['id' => $book->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonBook, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/{id}', name: 'app_book_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getDetailBook(Book $book, SerializerInterface $serializer): JsonResponse
    {
        $jsonBook = $serializer->serialize($book, 'json', ['groups' => 'getBooks']);
        return new JsonResponse($jsonBook, Response::HTTP_OK, [], true);
    }

    #[Route('/edit/{id}', name: 'app_book_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function updateBook(Request $request, SerializerInterface $serializer, EntityManagerInterface $manager,
                                Book $currentBook, UrlGeneratorInterface $urlGenerator, AuthorRepository $authorRepository): JsonResponse
    {
        $updatedBook = $serializer->deserialize($request->getContent(), Book::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentBook]);
        
        $content = $request->toArray();
        $idAuthor = $content['idAuthor'] ?? -1;

        $updatedBook->setAuthor($authorRepository->find($idAuthor));

        $manager->persist($updatedBook);
        $manager->flush();

        // $jsonBook = $serializer->serialize($updatedBook, 'json', ['groups' => 'getBooks']);

        // $location = $urlGenerator->generate('app_book_detail', ['id' => $updatedBook->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        // return new JsonResponse($jsonBook, Response::HTTP_CREATED, ["Location" => $location], true);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/delete/{id}', name: 'app_book_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteBook(Book $book, EntityManagerInterface $manager): JsonResponse
    {
        $manager->remove($book);
        $manager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
