<?php

namespace App\Controller;

use App\Entity\Secret;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SecretController extends AbstractController
{
    /**
     * It creates a new secret.
     * If it cannot create it, it returns an error message to the user in response.
     *
     * @param Request $request Request
     * @param EntityManagerInterface $entityManager EntityManager
     * @param SerializerInterface $serializer Serializer
     * @param ValidatorInterface $validator Validator
     *
     * @return Response HTTP Response
     */
    #[Route('/secret', name: 'add_secret', methods: ['POST'])]
    public function addSecret(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): Response {
        $data = $request->request->all();

        $secret = new Secret();
        $secret->setHash(Uuid::v4()->toRfc4122());
        $secret->setSecretText($data['secretText']);
        $secret->setRemainingViews((int) $data['expireAfterViews']);
        $secret->setCreatedAt(new DateTime());
        $secret->setExpiresAt(
            (isset($data['expireAfter']) && $data['expireAfter'] > 0)
                ? (new DateTime())->modify("+{$data['expireAfter']} minutes")
                : null
        );

        $errors = $validator->validate($secret);

        $headerType = $request->headers->get('Accept');

        if (!in_array($headerType, Secret::getAcceptedHeaderTypes())) {
            $headerType = 'application/json';
        }

        $serializerFormat = Secret::getSerializerFormat($headerType);

        if (count($errors) > 0) {
            $error = $serializer->serialize($errors, $serializerFormat);
            return new Response($error, Response::HTTP_BAD_REQUEST, ['Content-Type' => $headerType]);
        }

        $entityManager->persist($secret);
        $entityManager->flush();

        return new Response(
            $serializer->serialize($secret, $serializerFormat, ['groups' => 'secret:read']),
            Response::HTTP_CREATED, [
                'Content-Type' => $headerType,
            ]
        );
    }

    /**
     * It creates a new secret.
     * If it cannot create it, it returns an error message to the user in response.
     *
     * @param string $hash Hash
     * @param EntityManagerInterface $entityManager EntityManager
     * @param SerializerInterface $serializer Serializer
     * @param Request $request Request
     *
     * @return Response HTTP Response
     */
    #[Route('/secret/{hash}', name: 'get_secret', methods: ['GET'])]
    public function getSecret(
        string $hash,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        Request $request
    ): Response
    {
        $secret = $entityManager->getRepository(Secret::class)->findOneBy(['hash' => $hash]);
        $headerType = $request->headers->get('Accept');

        if (!in_array($headerType, Secret::getAcceptedHeaderTypes())) {
            $headerType = 'application/json';
        }

        $serializerFormat = Secret::getSerializerFormat($headerType);

        if (!$secret) {
            return new Response($serializer->serialize(['error' => 'Secret not found'], $serializerFormat), Response::HTTP_NOT_FOUND, [
                'Content-Type' => $headerType,
            ]);
        }

        if ($secret->getExpiresAt() && $secret->getExpiresAt() < new DateTime()) {
            return new Response($serializer->serialize(['error' => 'Secret has expired'], $serializerFormat), Response::HTTP_NOT_FOUND, [
                'Content-Type' => $headerType,
            ]);
        }

        if ($secret->getRemainingViews() <= 0) {
            return new Response($serializer->serialize(['error' => 'Secret has no remaining views'], $serializerFormat), Response::HTTP_NOT_FOUND, [
                'Content-Type' => $headerType,
            ]);
        }

        $secret->setRemainingViews($secret->getRemainingViews() - 1);
        $entityManager->flush();

        return new Response($serializer->serialize($secret, $serializerFormat, ['groups' => 'secret:read']), Response::HTTP_OK, [
            'Content-Type' => $headerType,
        ]);
    }
}
