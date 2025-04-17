<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\AbstractNormalizer;
use Doctrine\ORM\EntityManagerInterface;

final class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/BookController.php',
        ]);
    }

    #[Route('/api/books', name: 'app_books', methods: ['GET'])]
    /**
     * @param BookRepository $bookRepository
     * @return JsonResponse
     */
    public function getBookList(BookRepository $bookRepository, SerializerInterface $serializer): JsonResponse
    {
        $booklist = $bookRepository->findAll();
        $jsonBookList = $serializer->serialize($booklist, 'json', ['groups' => 'getBooks']);

        return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/books/{id}', name: 'detailBook', methods: ['GET'])]
    public function getDetailBook(int $id, BookRepository $bookRepository, SerializerInterface $serializer): JsonResponse
    {
        $book = $bookRepository->find($id);
        if ($book) {
            $jsonBook = $serializer->serialize($book, 'json');
            return new JsonResponse($jsonBook, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(['message' => 'Book not found'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/books', name: 'createBook', methods: ['POST'])]
    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGenerator
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function createBook(Request $request, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, EntityManagerInterface $em, AuthorRepository $authorRepository): JsonResponse
    {
        $book = $serializer->deserialize($request->getContent(), Book::class, 'json');
        
        // Récup de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();

        // Récup de l'idAuthor. S'il n'est pas défini, alors on met -1 par défaut.
        $idAuthor = $content['author'] ?? -1;

        //On cherche l'auteur qui correspond et on l'assigne au livre.
        // Si 'find' ne trouve pas d'auteur, alors on met null.
        $book->setAuthor($authorRepository->find($idAuthor));

        $em->persist($book);
        $em->flush();

        $jsonBook = $serializer->serialize($book, 'json', ['groups' => 'getBooks']);

        $location = $urlGenerator->generate('detailBook', ['id' => $book->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonBook, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    public function updateBook(Request $request, SerializerInterface $serializer, Book $currentBook, EntityManagerInterface $em, AuthorRepository $authorRepository): JsonResponse
    {
        $updateBook = $serializer->deserialize($request->getContent(), Book::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentBook]);
        $content = $request->toArray();
        $idAuthor = $content['author'] ?? -1;
        $updateBook->setAuthor($authorRepository->find($idAuthor));

        $em->persist($updateBook);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}