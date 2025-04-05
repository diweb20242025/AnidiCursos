<?php

namespace App\Controller;

use App\Entity\Aulas;
use App\Entity\Cursos;
use App\Repository\CursosRepository;
use DateTimeInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CursosController extends AbstractController
{
    #[Route('/cursos', name: 'app_cursos')]
    public function index(): Response
    {
        return $this->render('cursos/index.html.twig', [
            'controller_name' => 'CursosController',
        ]);
    }

    // C4 -> Insertar 1 registro por parámetros
    // Variante C2. Controlamos dato tabla principal
    #[Route(
        '/crea-curso/{expediente}/{denominacion}/{codAula}/{inicio}',
        name: 'app_cursos_insertar_curso'
    )]
    public function creaCurso(
        ManagerRegistry $doctrine,
        string $expediente,
        string $denominacion,
        float $codAula,
        DateTimeInterface $inicio
    ): Response {
        $entityManager = $doctrine->getManager();
        $nuevoCurso = new Cursos();
        $nuevoCurso->setExpediente($expediente);
        $nuevoCurso->setDenominacion($denominacion);
        $nuevoCurso->setInicio($inicio);

        $mensaje = "";
        $aula = $entityManager->getRepository(Aulas::class)->find($codAula);

        if ($aula == null) {
            $mensaje = "ERROR! No existe el aula";
        } else {
            $nuevoCurso->setCodAula($aula);
            $entityManager->persist($nuevoCurso);
            $entityManager->flush();
            $mensaje = "EXITO! Se ha introducido el curso.";
        }
        return new Response("<h2> $mensaje </h2>");
    }


    // R4 -> Consultar por parámetros. Salida Array JSON!
    #[Route(
        '/consultar-cursos',
        name: 'app_cursos_consultar_cursos'
    )]
    public function consultarCursos(CursosRepository $repo): JsonResponse
    {
        $cursos = $repo->findAll();

        // Aquí la salida NO es twig. Es JSON!!!!
        $miJSON = [];
        foreach ($cursos as $curso) {
            $miJSON[] = [
                'Expediente' => $curso->getExpediente(),
                'Denominación' => $curso->getDenominacion(),
                'Fecha' => $curso->getInicio(),
                'CodAula' => $curso->getCodAula()->getCodigo(),
            ];
        }

        return new JsonResponse($miJSON);
    }

    // F1 -> Formulario completo Ambas tablas
    #[Route('/cursos-form', name: 'app_cursos_form')]
    public function cursosForm(ManagerRegistry $doctrine, Request $envio): Response
    {
        $curso = new Cursos();

        $formulario = $this->createFormBuilder($curso)
            ->add('expediente', TextType::class, [
                'label' => 'Expediente',
            ])
            ->add('denominacion', TextType::class, [
                'label' => 'Denominación',
            ])
            ->add('inicio', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha Inicio',
            ])
            ->add('codAula', EntityType::class, [
                'label' => 'Selecciona Aula',
                'placeholder' => 'Elige una aula',
                'class' => Aulas::class,
                'choice_label' => function (Aulas $aula) {
                    return sprintf('%s (Capacidad: %d)', $aula->getCodigo(), $aula->getCapacidad());
                },
            ])
            ->getForm();

        $formulario->handleRequest($envio);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($curso);
            $entityManager->flush();

            return $this->redirectToRoute('app_cursos_consultar_cursos');
        }

        return $this->render('cursos/formulario.cursos.html.twig', [
            'controller_name' => 'CursosController',
            'formulario' => $formulario->createView(),
        ]);
    }
}
