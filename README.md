Construindo imagem:

    cp .env.example .env
    docker compose up --build
    docker exec -it gwdb composer install

Variáveis de ambiente (.env):

    TOKEN=123

    DB_MYSQL_HOST=mariadb
    DB_MYSQL_USER=admin
    DB_MYSQL_PASS=admin
    DB_MYSQL_PORT=3306

    DB_PGSQL_HOST=postgres
    DB_PGSQL_USER=admin
    DB_PGSQL_PASS=admin
    DB_PGSQL_PORT=5432
    DB_PGSQL_ADMIN_NAME=postgres

    # opcional - se omitido, o padrao ja e "mysql"
    DB_DRIVER_PADRAO=mysql

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

Escolhendo o banco (driver):

Por padrão todas as ações acima rodam no MariaDB/MySQL, sem precisar
informar nada a mais. Para rodar no Postgres, basta adicionar o campo
"driver":"pgsql" / "postgres" / "postgresql" no corpo da requisição:

    curl -X POST http://localhost:8080/ -H "X-Token: 123" \
        -H "Content-Type: application/json" \
        -d '{"action":"listar_databases", "driver":"pgsql"}'

    curl -X POST http://localhost:8080/ -H "X-Token: 123" \
        -H "Content-Type: application/json" \
        -d '{"action":"criar_database_usuario_privilegio", "nome":"nome_database", "driver":"pgsql"}'

