<?php

namespace App\Controller;

use App\Controller\Input\InputDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
#[Route('/api/v1/validator-test', methods: ['POST'])]
class Controller
{
    public function __construct(
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $dto = InputDTO::fillFromRequest($request);
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return new Response((string)$errors, Response::HTTP_BAD_REQUEST);
        }

        return new Response('Success');
    }
}
