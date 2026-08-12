Construindo imagem:

    cp .env.example .env
    docker compose up --build
    docker exec -it gwdb composer install


Teste de conexão:

    curl -X GET http://localhost:8080/ -H "X-Token: 123"
    curl -X GET http://localhost:8080/ -H "X-Token: 123tokenErrado"

Requisições existentes:

    curl -X POST http://localhost:8080/ -H "X-Token: 123" \
        -H "Content-Type: application/json" \
        -d '{"action":"listar_databases"}'

    curl -X POST http://localhost:8080/ -H "X-Token: 123" \
        -H "Content-Type: application/json" \
        -d '{"action":"listar_usuarios"}'

Novas requisições:

    curl -X POST http://localhost:8080/ -H "X-Token: 123" \
        -H "Content-Type: application/json" \
        -d '{"action":"database_existe", "nome":"nome_database"}'

    curl -X POST http://localhost:8080/ -H "X-Token: 123" \
        -H "Content-Type: application/json" \
        -d '{"action":"usuario_existe", "nome":"nome_usuario"}'

    curl -X POST http://localhost:8080/ -H "X-Token: 123" \
        -H "Content-Type: application/json" \
        -d '{"action":"criar_database", "nome":"nome_database"}'

    curl -X POST http://localhost:8080/ -H "X-Token: 123" \
        -H "Content-Type: application/json" \
        -d '{"action":"criar_usuario", "nome":"nome_database"}'

    curl -X POST http://localhost:8080/ -H "X-Token: 123" \
        -H "Content-Type: application/json" \
        -d '{"action":"criar_database_usuario", "nome":"nome_database"}'

    curl -X POST http://localhost:8080/ -H "X-Token: 123" \
        -H "Content-Type: application/json" \
        -d '{"action":"conceder_privilegios", "nome":"nome_usuario"}'

    curl -X POST http://localhost:8080/ -H "X-Token: 123" \
        -H "Content-Type: application/json" \
        -d '{"action":"criar_database_usuario_privilegio", "nome":"nome_database"}'

    curl -X POST http://localhost:8080/ -H "X-Token: 123" \
     -H "Content-Type: application/json" \
     -d '{"action":"trocar_senha", "nome":"nome_usuario"}'