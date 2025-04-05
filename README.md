<!-- README.md -->

# AnidiCursos


## Comandos
- symfony serve
- php bin/console doctrine:database:drop --force
- php bin/console doctrine:database:create
- php bin/console make:migration
- php bin/console doctrine:migrations:migrate
- php bin/console make:entity

## Endpoints
- http://localhost:8000/

### Endpoints de Inserción

Aulas (tabla principal):
- [C3] http://localhost:8000/crear-aulas -> array


Cursos (tabla relacionada): 
- [C4] http://localhost:8000/crea-curso/{expediente}/{denominacion}/{codAula}/{inicio}
- http://localhost:8000/crea-curso/11A/MiCurso/1.1/2024-04-04

### Endpoints de Consulta

Aulas (tabla principal):
- [R1] http://localhost:8000

Cursos (tabla relacionada):
- http://localhost:8000/consultar-cursos

### Endpoints de Actualización

- [U1] http://localhost:8000/cambiar-autor/{nif}/{nombre}/{edad}
- [U2] http://localhost:8000/cambiar-articulo/{id}/{titulo}/{nifAutor}

### Endpoints de Eliminación

- [D1] http://localhost:8000/articulo-borrar/{id}

### Endpoints de Formularios

- [F1] http://localhost:8000/articulos-form
- [F2] http://localhost:8000/autores-form
