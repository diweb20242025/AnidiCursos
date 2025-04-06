<?php

namespace App\Controller;

use App\Entity\Aulas;
use App\Entity\Cursos;
use App\Repository\CursosRepository;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
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
        '/consultar-cursos-json',
        name: 'app_cursos_consultar_cursos_json'
    )]
    public function consultarCursosJSON(CursosRepository $repo): JsonResponse
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

    // R4 -> Consultar por parámetros. Salida por Tabla bootstrap
    #[Route(
        '/consultar-cursos',
        name: 'app_cursos_consultar_cursos'
    )]
    public function consultarCursos(CursosRepository $repo): Response
    {
        $cursos = $repo->findAll();

        return $this->render('cursos/cursos.html.twig', [
            'controller_name' => 'CursosController',
            'cursos' => $cursos,
        ]);
    }

    // U2 -> Actualizar por ID, con parámetros y cambio del FK!!
    // Si se cambia el FK, el tipo NO es string, es el objeto!
    #[Route(
        '/cambiar-curso/{expediente}/{denominacion}/{codAula}',
        name: 'app_cursos_actualizar'
    )]
    public function cambiarCurso(
        ManagerRegistry $doctrine,
        string $expediente,
        string $denominacion,
        Aulas $codAula
    ): Response {
        // Sacamos el entityManager
        $entityManager = $doctrine->getManager();
        $repoCursos = $entityManager->getRepository(Cursos::class);
        $repoAulas = $entityManager->getRepository(Aulas::class);

        $curso = $repoCursos->find($expediente);
        $aula = $repoAulas->find($codAula);

        // Controlamos el fallo
        if ($curso == null || $aula == null) {
            return new Response("<h1> Aula/Curso NO existen </h1>");
        } else {
            // Cambiamos el articulo
            $curso->setDenominacion($denominacion);
            $curso->setCodAula($codAula);
            // Actualizamos la Base de datos
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cursos_consultar_cursos', [
            'controller_name' => 'Curso Actualizado!',
        ]);
    }

    // D1 -> Eliminar por ID (Tabla relacionada!)
    #[Route(
        '/curso-borrar/{expediente}',
        name: 'app_cursos_eliminar'
    )]
    public function borrarCurso(
        EntityManagerInterface $entityManager,
        string $expediente
    ): Response {
        // Busco el curso
        $repoCursos = $entityManager->getRepository(Cursos::class);
        $curso = $repoCursos->find($expediente);
        if ($curso == null) {
            return new Response("<h1> Curso NO encontrado </h1>");
        } else {
            $entityManager->remove($curso);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_cursos_consultar_cursos', [
            'controller_name' => 'curso Eliminado!',
        ]);
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
                'choice_label' => 'codigo'
                // Si queremos presentar mas campos...
                /*
                'choice_label' => function (Aulas $aula) {
                    return sprintf('%s (Capacidad: %d)', $aula->getCodigo(), $aula->getCapacidad());
                },
                */
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
