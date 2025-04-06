<?php

namespace App\Controller;

use App\Entity\Aulas;
use App\Repository\AulasRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AulasController extends AbstractController
{
    #[Route('/aulas', name: 'app_aulas')]
    public function index(): Response
    {
        return $this->render('aulas/index.html.twig', [
            'controller_name' => 'AulasController',
        ]);
    }

    // C3 -> Insertar varios registros (array) por código
    // OJO, hay que tener cuidado con el registro tabla principal
    #[Route(
        '/crear-aulas',
        name: 'app_aulas_insertar_aulas'
    )]
    public function crearAulas(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        // Definimos un array de aulas
        $aulas = [
            "aula1" => [
                "codigo" => 01.1,
                "capacidad" => 15,
                "adaptado" => 0
            ],
            "aula2" => [
                "codigo" => 01.2,
                "capacidad" => 10,
                "adaptado" => 1
            ],
            "aula3" => [
                "codigo" => 01.3,
                "capacidad" => 12,
                "adaptado" => 1
            ],
        ];

        // Uso un foreach para recorrer el array
        // Y meto en la tabla aula por aula
        foreach ($aulas as $aula) {
            $nuevaAula = new Aulas();
            $nuevaAula->setCodigo($aula['codigo']);
            $nuevaAula->setCapacidad($aula['capacidad']);
            $nuevaAula->setAdaptado($aula['adaptado']);

            $entityManager->persist($nuevaAula);
            $entityManager->flush();
        }
        return new Response("<h2> Aulas metidas</h2>");
    }

    #[Route('/ver-aula/{codigo}', name: 'app_aulas_ver')]
    public function verAula(AulasRepository $repo, float $codigo): Response
    {
        $aula = $repo->find($codigo);

        return $this->render('aulas/aulas.html.twig', [
            'controller_name' => 'AulasController',
            'aulas' => [$aula],
        ]);
    }
}
